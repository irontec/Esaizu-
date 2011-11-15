<?php
/**
 * Esaizu!
 * @version 1.0
 * Copyright (C) ESLE & Irontec 2011
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * @author Mikel Madariaga <mikel@irontec.com>
 *
 * Clase encargada de recoger y guardar posts de wordpress
 */
class Plugins_Wordpress_Cron extends Common_Cron
{
    protected $_account;
    protected $_client;

    /***
     * Frecuencia con la que se revisa un post en busca de modificaciones,
     * número de comentarios etc
     */
    protected $_updateFrecuency;

    /**
     * @return unknown_type
     */
    function __construct()
    {
        parent::__construct();
        $this->_updateFrecuency = 60*30; //seconds
    }

    /**
     * Método encargado de establecer conexión con un portal wordpress
     * @param User_Accounts_Data $account
     * @param obj $auth
     */
    /* (non-PHPdoc)
     * @see application/models/Common/Common_Cron#connect($idp)
     */
    public function connect($account)
    {
        $this->start = microtime();

        $this->_account = $account;
        $this->_idUP = $account->getId();
        $this->_credentials = json_decode($account->getAuth());

        $user = Auth::get_instance();

        $this->_ci->load->library('encrypt');
        $this->_credentials->password = $this->_ci->encrypt->decode($this->_credentials->password, sha1($user->getEmail()));
        $this->_credentials->username = $this->_ci->encrypt->decode($this->_credentials->username, sha1($user->getEmail()));

        /***
         * Refs::
         * http://life.mysiteonline.org/archives/161-Automatic-Post-Creation-with-Wordpress,-PHP,-and-XML-RPC.html
         * http://en.forums.wordpress.com/topic/upload-image-using-xmlrpcphp-script
         * http://codex.wordpress.org/XML-RPC_wp#wp.getComments
         */
        $this->_client = new IXR_Client(prep_url($this->_credentials->url).'/xmlrpc.php');
    }

    /**
     * Método encargado de recoger y guardar/actualizar los posts recientes del blog en cuestión
     * @return void
     */
    /* (non-PHPdoc)
     * @see application/models/Common/Common_Cron#update()
     */
    public function update()
    {
        $auth = Auth::get_instance();

        /***
         * Actualizar mensajes
         */
        if (!$this->_client->query(
            'mt.getRecentPostTitles',
            '', $this->_credentials->username,
            $this->_credentials->password, 10
        )) {

            return;
        }

        $resp = $this->_client->getResponse();
        $messageMapper = Message_Mapper::get_instance();

        foreach ($resp as $entry) {

            $message  = New Message_Data();
            $message->setRemoteId($entry["postid"]);

            $dbMessage = $messageMapper->find($message);
            if (count($dbMessage) > 0) {

                $message = array_shift($dbMessage);

                if (
                    $message->getData("fetched") !== false and
                    ( $message->getData("fetched") + $this->_updateFrecuency ) < time()
                ) {

                    //Ignored item
                    continue;
                }
            }

            if ($this->_client->query(
                'metaWeblog.getPost',
                $entry["postid"],
                $this->_credentials->username,
                $this->_credentials->password,
                array("post_id" => $entry["postid"])
            )) {

                $post = $this->_client->getResponse();
            }

            if ($this->_client->query(
                'wp.getComments',
                '',
                $this->_credentials->username,
                $this->_credentials->password,
                array("post_id" => $entry["postid"])
            )) {

                $post["comments"] = count($this->_client->getResponse());
            }

            Plugins_Wordpress_DB::save($post, $this->_idUP);
        }

        /***
         * Actualizar categorias
         */
        if (!$this->_client->query(
            'wp.getCategories', '',
            $this->_credentials->username, $this->_credentials->password
        )) {

            return;
        }

        $resp = $this->_client->getResponse();
        $categorias = array();

        foreach ($resp as $cat) {

            $id = $cat["categoryId"];
            $categorias[$id] = $cat;
        }

        $this->_account->setMetadata("categories", $categorias);

        if ($this->_account->isValid()) {

            $accountMapper = User_Accounts_Mapper::get_instance();
            $this->_account->setLastUpdate(time());

            $data = array(
                "metadata" =>  $this->_account->getMetadata(),
                "lastUpdate" => $this->_account->getLastUpdate()
            );

            $accountMapper->updateFromArray($this->_account->getId(), $data);
        }

        if ($this->debugMode === true) {
            echo "\r\n Wordpress cron elapset time : ".$this->elapsedTime()."s\r\n";
        }
    }
}