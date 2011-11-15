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
 * Clase encargada de publicar en facebook
 */
class Plugins_Facebook_Publish extends Common_Publish
{
    protected $_token;
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
     * Método para conectarse a una identidad facebook
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

        $mytoken = unserialize($token->getAuth());
        $this->_token = $mytoken["token"];

        $url = 'https://graph.facebook.com/me/feed';

        $this->_client = new Zend_Http_Client($url, array(
            'maxredirects' => 0,
            'timeout'      => 30)
        );
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
        $msg = new Message_Data();
        $this->_ci->lang->load("errormessages");

        if (is_null($post)) {

            $msg->setError("form", $this->_ci->lang->line("unknownError"));
            return $msg;
        }

        if (isset($post["fb_content"]) and $post["fb_content"] != "") {

            $msg->setTitle($post["fb_content"]);

        } else {

            $msg->setError("fb_content", $this->_ci->lang->line("requiredField"));
        }

        return $msg;
    }

    /**
     * Método para publicar contenido en cuentas facebook
     * @param array $post
     * @param User_Accounts_Data $targetAccount
     * @return Message_Data
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Publish#publish($post, $targetAccount)
     */
    public function publish($post = null, User_Accounts_Data $targetAccount)
    {
    	$this->_account = $targetAccount;

        $auth = Auth::get_instance();
        $msg = $this->validatePost($post, $targetAccount);

        $msg->setIdU($auth->getUserId());
        $msg->setOwner("1");

        if ($msg->isValid()) {

            $account = $auth->getEnabledAccount($post["publishIn"]);

            $this->connect($account);

            $data = array(
                'access_token'  => $this->_token,
                'message'   => $msg->getTitle()
            );

			$this->_client->setParameterPost($data);
			$response = $this->_client->request('POST');
			$this->fetch($response);
        }

        return $msg;
    }

    /**
     * @param mixed $response
     * @return boolean
     */
    public function fetch($response)
    {
       $body = json_decode($response->getBody());

       if (isset($body->id))
       {
            /********* Wall *********/
            $url = "https://graph.facebook.com/me/feed?access_token=".$this->_token;
            $resp = json_decode(file_get_contents($url));

            if (is_array($resp->data)) {

                foreach ($resp->data as $item) {

                    if($item->id == $body->id) {

                        //this->save($item);
                        Plugins_Facebook_DB::save($resp->data, $this->_account);
                    }
                }
            }

            return true;
       }

       return false;
    }

    /* (non-PHPdoc)
     * @see application/models/Common/Common_Publish#reference($message, $account)
     */
    public function reference (Message_Data $message, User_Accounts_Data $account)
    {
        $auth = Auth::get_instance();
        $this->_account = $account;

        if ($message->isValid()) {

            $this->connect($this->_account);
            $msg = $message->getTitle();

            if (is_array($message->getData("tags"))) {

                $tags = $message->getData("tags");

            } else {

                $tags = explode(",", $message->getData("tags"));
            }

            $shortUrl = Shorter::short($message->getLink());

            if( (strlen($msg) + strlen($shortUrl) + 1) < 200 ) {

                $msg.= " ".$shortUrl;
            }

            if(is_array($tags)) {

                foreach($tags as $tag) {

                    if ($tag == "") {

                        continue;
                    }

                    $tag = trim($tag);

                    if( (strlen($msg) + strlen($tag) + 2) < 200 ) {

                        $msg .= " #".$tag;

                    } else {

                        break;
                    }
                }
            }

            $data = array(
                'access_token'  => $this->_token,
                'message'   => $msg
            );

            $this->_client->setParameterPost($data);
            $response = $this->_client->request('POST');
        }

        return true;
    }
}