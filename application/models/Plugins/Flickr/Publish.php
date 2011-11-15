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
 * Clase encargada de publicar en flickr
 */
class Plugins_Flickr_Publish extends Common_Publish
{
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
     * Método para conectarse a una identidad flickr
     * @param User_Accounts_Data $token
     * @return void
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Publish#connect($token)
     */
    public function connect($token)
    {
        if (! $token instanceof User_Accounts_Data) {

            throw new Exception("Invalid data");
        }

        $this->_account = $token;
        $this->_idUP = $this->_account->getId();
        $auth = unserialize($this->_account->getAuth());

        $this->_ci->config->load('apikeys', TRUE);
        $this->_config = $this->_ci->config->item('flickr', 'apikeys');

        $this->_ci->load->library("phpFlickr");
        $this->_ci->phpflickr->initialize($this->_config);

        $this->_client = $this->_ci->phpflickr;

        $credentials = unserialize($this->_account->getAuth());
        $this->_client->setToken($credentials["token"]);
    }
    
    /**
     * Método que valida si el contenido a publicar es correcto.
     *
     * @param array $post
     * @param User_Accounts_Data $targetAccount
     * @return Message_Data
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Publish#validatePost($post, $targetAccount)
     */
    public function validatePost($post = null, User_Accounts_Data $targetAccount)
    {
        $this->_ci->lang->load("errormessages");

        $msg = new Message_Data();

        if (is_null($post)) {

            $msg->setError("form", $this->_ci->lang->line("unknownError"));
        }

        if (isset($post["flickr_title"]) and $post["flickr_title"] != "") {

            $msg->setTitle($post["flickr_title"]);

        } else {

            $msg->setError("flickr_title", $this->_ci->lang->line("requiredField"));
        }

        return $msg;
    }

    /**
     * Método para publicar contenido en cuentas flickr
     * @param array $post
     * @param User_Accounts_Data $targetAccount
     * @return Message_Data
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Publish#publish($post, $targetAccount)
     */
    public function publish($post = null, User_Accounts_Data $targetAccount, $files = null)
    {
        $this->_ci->lang->load("errormessages");
    	$this->_account = $targetAccount;

        $auth = Auth::get_instance();
        $msg = $this->validatePost($post, $targetAccount, $files);

        if (!is_array($files) or count($files) == 0) {

            $msg->setError("flickr_img", $this->_ci->lang->line("noImage"));
        }

        $msg->setIdU($auth->getUserId());
        $msg->setOwner("1");

        if ($msg->isValid()) {

            $account = $auth->getEnabledAccount($post["publishIn"]);

            $this->connect($account);

            $newPath = FCPATH."tmp/".$auth->getUserId().$files["flickr_img"]["name"];
            if (! move_uploaded_file($files["flickr_img"]["tmp_name"], $newPath)) {

                $msg->setError("form", sprintf($this->_ci->lang->line("couldNotWriteIn"), FCPATH));
                return $msg;
            }

            $files["flickr_img"]["tmp_name"] = $newPath;

            $photo = $files["flickr_img"]["tmp_name"];
            $title = $post["flickr_title"] == "" ? null : $post["flickr_title"];
            $description = $post["flickr_description"] == "" ? null : $post["flickr_description"];
            $tags = $post["flickr_description"] == "" ? null : $post["flickr_description"];
            $isPublic = null;
            $isFriend = null;
            $isFamily = null;

            if(isset($post["flickr_private"]) and $post["flickr_private"] == 1) {

                $isPublic = 0;

                if (isset($post["flickr_friends"]) and $post["flickr_friends"] == 1) {

                    $isFriend = 1;

                } else {

                    $isFriend = 0;
                }

                if (isset($post["flickr_relatives"]) and $post["flickr_relatives"] == 1) {

                    $isFamily = 1;

                } else {

                    $isFamily = 0;
                }

            } else {
            
                $isPublic = 1;
            }

            try {

                $response = $this->_client->sync_upload($photo, $title, $description, $tags, $isPublic, $isFriend, $isFamily);

                if(is_numeric($response)) {

                    $msg = $this->fetch($response);

                } else {

                    $msg->setError("form", $this->_ci->lang->line("unknownError"));
                }
                
            } catch(Exception $e) {
            
                $msg->setError("form", $e->getMessage());
            }
        }

        if (isset($newPath) and file_exists($newPath)) {

            unlink($newPath);
        }

        return $msg;
    }

    /**
     * @param integer $photoId
     * @return Message_Data
     */
    public function fetch($photoId)
    {
        $data = $this->_client->photos_getInfo ($photoId);
        $photo = $data["photo"];

        $uploadedImage = array(
            "id" => $photo["id"],
            "owner" => $photo["owner"]["nsid"],
            "ownername" => $photo["owner"]["username"],
            "secret" => $photo["secret"],
            "server" => $photo["server"],
            "farm" => $photo["farm"],
            "title" => $photo["title"],
            "activities" => array(array(
                "photos_uploaded" => 1,
                "type" => "app"
            )),
        );

        return Plugins_Flickr_DB::save(array($uploadedImage), $this->_account);
    }

    /* (non-PHPdoc)
     * @see application/models/Common/Common_Publish#reference($message, $account)
     */
    public function reference (Message_Data $message, User_Accounts_Data $account)
    {
        return false;
    }
}