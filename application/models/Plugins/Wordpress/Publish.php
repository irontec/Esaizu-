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
 */
class Plugins_Wordpress_Publish extends Common_Publish
{
    protected $_auth;
    protected $_account;

    protected $_client;

    /**
     * @return void
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Metodo para conectarse a una identidad wordpress
     * @param obj $auth
     * @return void
     */
    /* (non-PHPdoc)
     * @see application/models/Common/Common_Publish#connect($token)
     */
    public function connect($auth)
    {
        $this->_auth = json_decode($auth);

        $user = Auth::get_instance();

        $this->_ci->load->library('encrypt');
        $this->_auth->password = $this->_ci->encrypt->decode($this->_auth->password, sha1($user->getEmail()));
        $this->_auth->username = $this->_ci->encrypt->decode($this->_auth->username, sha1($user->getEmail()));

        /***
         * +Info::
         * http://life.mysiteonline.org/archives/161-Automatic-Post-Creation-with-Wordpress,-PHP,-and-XML-RPC.html
         */
        $this->_client = new IXR_Client(prep_url($this->_auth->url).'/xmlrpc.php');
    }
    
    /**
     * Método que valida si el contenido a publicar es correcto.
     *
     * @param array $post
     * @param User_Accounts_Data $targetAccount
     * @return Message_Data
     */
    /* (non-PHPdoc)
     * @see application/models/Common/Common_Publish#validatePost($post, $targetAccount)
     */
    public function validatePost($post = null, User_Accounts_Data $targetAccount)
    {
        $this->_ci->lang->load("errormessages");
        $msg = new Message_Data();
        $data = array();

        if (is_null($post)) {

            $msg->setError("form", $this->_ci->lang->line("unknownError"));
            return $msg;
        }

        if (isset($post["wp_content"]) and $post["wp_content"] != "") {

            $post["wp_content"] = preg_replace('/src="([^"]+)"/i', ' src="'.base_url()."\\1".'" ', $post["wp_content"]);
            $data["text"] = $post["wp_content"];

        } else {

            $msg->setError("wp_content", $this->_ci->lang->line("requiredField"));        }

        $data["category"] = $post["wp_categories"];
        $data["tags"] = $post["tags"];
        $data["text"] = $post["wp_content"];

        $msg->setTitle($post["wp_titular"]);
        $msg->setData($data);

        return $msg;
    }

    /**
     * Método para publicar contenido en una cuenta wordpress
     * @param Array $post
     * @return Message_Data
     */
    /* (non-PHPdoc)
     * @see application/models/Common/Common_Publish#publish($post, $targetAccount)
     */
    function publish($post = null, User_Accounts_Data $targetAccount)
    {
        $this->_account = $targetAccount;

        $auth = Auth::get_instance();
        $msg = $this->validatePost($post, $targetAccount);

        $msg->setIdU($auth->getUserId());
        $msg->setOwner("1");

        if($msg->isValid()) {
            
            $this->connect($this->_account->getAuth());

            $content = $msg->getData("text");
            preg_match_all('/src="([^"]+)"/i',$content, $imgMatches);
            $imgMatches = $imgMatches[1];

            $media = array();

            foreach ($imgMatches as $img) {

                $img = str_replace(base_url()."tmp/", FCPATH.'tmp/' ,$img);

                if( file_exists($img) ) {

                    $name = substr($img, strrpos($img, DIRECTORY_SEPARATOR)+1 );
                    $mime = mime_content_type($img);

                    $media[] = array(
                        "name" => $name,
                        "type" => $mime,
                        "bits" => new IXR_Base64(file_get_contents($img))
                    );

                    unlink($img);
                }
            }

            foreach($media as $item) {

                if ( $this->_client->query('metaWeblog.newMediaObject',
                    '', $this->_auth->username, $this->_auth->password, $item) ) {

                    $newUrl = $this->_auth->url."wp-content/uploads/".date("Y/m/");
                    $content = str_replace(base_url()."tmp/", $newUrl , $content);
                }
            }

            $data = $msg->getData();
            $data["text"] = $content;
            $msg->setData($data);

            $content = array();
            $content['title'] = $msg->getTitle();
            $content['categories'] = $msg->getData("category");
            $content['description'] = $msg->getData("text");
            $content['mt_keywords'] = $msg->getData("tags");

            if (!$this->_client->query('metaWeblog.newPost','', $this->_auth->username,$this->_auth->password, $content, true)) {

                Throw new Exception('An error occurred on wordpress publishing - '.$this->_client->getErrorMessage());
            }

            $msg->setRemoteId($this->_client->getResponse()); //Will report the ID of the new post

            if ($msg->isValid()) {

                return $this->fetch($msg->getRemoteId());
            }
        }

        return $msg;
    }
    
    /**
     * @param integer $id
     * @return Message_Data
     */
    private function fetch($id)
    {
        if ($this->_client->query(
            'metaWeblog.getPost',
            $id,
            $this->_auth->username,
            $this->_auth->password,
            array("post_id" => $id)
        )) {

            $post = $this->_client->getResponse();
        }

        if ($this->_client->query(
            'wp.getComments',
            '',
            $this->_auth->username,
            $this->_auth->password,
            array("post_id" => $id)
        )) {

            $post["comments"] = count($this->_client->getResponse());
        }

        return Plugins_Wordpress_DB::save($post, $this->_account->getIdU());
    }

    /* (non-PHPdoc)
     * @see application/models/Common/Common_Publish#reference($message, $account)
     */
    public function reference(Message_Data $message, User_Accounts_Data $account)
    {
        return false;
    }
}