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
 * Clase encargada de recoger y guardar mensajes de facebook
 */
class Plugins_Facebook_Cron extends Common_Cron
{
    protected $_account;
    protected $_client;
    protected $_conf;

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * Método encargado de establecer conexión con facebook
     * @param User_Accounts_Data $account
     * @param obj $auth
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Cron#connect($idp)
     */
    public function connect($account)
    {
        $this->start = microtime();

        $this->_account = $account;
        $this->_idUP = $this->_account->getId();
        $this->_credentials = unserialize($account->getAuth());

        if ( ! isset($this->_credentials["token"]) )
        {
            /***
             * Esta la primera vez que se conecta a la cuenta del usuario.
             * Recoger el token y resto de datos necesarios
             */
            $this->_ci->config->load('apikeys', TRUE);
            $config = $this->_ci->config->item('facebook', 'apikeys');
            $this->_config = $config;

            $my_url = site_url('accounts/auth/');
            $app_id = $this->_config["appId"];
            $app_secret = $this->_config["secret"];
            $code = $this->_credentials["code"];

            $url = "https://graph.facebook.com/oauth/access_token?client_id="
            .$app_id . "&client_secret="
            .$app_secret . "&code=" . $code . "&redirect_uri=" . $my_url;

            $token = file_get_contents($url);
            $this->_credentials["token"] = str_replace("access_token=","",$token);

            $accountMapper = User_Accounts_Mapper::get_instance();
            $accountMapper->updateFromArray($account->getId(), array("auth" => serialize($this->_credentials)));
        }

        if (! $this->_account->getMetadata("id"))
        {
            /****
             * Recoger id de usuario para comprobar que mensajes le pertenecen
             */
            $url = "https://graph.facebook.com/me?access_token=".$this->_credentials["token"];
            $resp = json_decode(file_get_contents($url));

            $account->setMetadata("id", $resp->id);
            $account->setMetadata("name", $resp->name);

            $accountMapper = User_Accounts_Mapper::get_instance();
            $accountMapper->updateFromArray($account->getId(), array("metadata" => serialize($account->getMetadata() )));
        }
    }

    /**
     * Método encargado de recoger los timelines del usuario y sus amigos
     * @return void
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Cron#update()
     */
    public function update()
    {
        /********* Wall *********/
        $url = "https://graph.facebook.com/me/feed?access_token=".$this->_credentials["token"];
        $resp = json_decode(file_get_contents($url));

        if (isset($resp->data) and is_array($resp->data)) {

            Plugins_Facebook_DB::save($resp->data, $this->_account,"wall");
        }

        /********* home *********/
        $url = "https://graph.facebook.com/me/home?access_token=".$this->_credentials["token"];
        $resp = json_decode(file_get_contents($url));

        if (isset($resp->data) and is_array($resp->data)) {

            Plugins_Facebook_DB::save($resp->data, $this->_account, "home");
        }

        if ($this->debugMode === true) {
            echo "\r\n Facebook cron elapset time : ".$this->elapsedTime()."s\r\n";
        }

        $accountMapper = User_Accounts_Mapper::get_instance();
        $this->_account->setLastUpdate(time());

        $data = array(
            "lastUpdate" => $this->_account->getLastUpdate()
        );

        $accountMapper->updateFromArray($this->_account->getId(), $data);
    }
}