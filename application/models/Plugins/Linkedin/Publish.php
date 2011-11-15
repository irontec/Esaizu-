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
 * Clase encargada de publicar en Linkedin
 */
class Plugins_Linkedin_Publish extends Common_Publish
{
    protected $_token;
    protected $_account;
    /**
     * @var Zend_Oauth_Client
     */
    protected $_client;

    /**
     * @return void
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Método para conectarse a una identidad Linkedin
     * @param obj $auth
     * @return void
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Publish#connect($token)
     */
    public function connect($token)
    {
        $this->_idUP = $this->_account->getId();
        $client = $token;

        $this->_ci->config->load('apikeys', TRUE);
        $this->_config = $this->_ci->config->item('linkedin', 'apikeys');

        $options = array(
            'version' => '1.0',
            'localUrl' => site_url("app/testing"),
            'callbackUrl' => site_url('accounts/auth'),
            'requestTokenUrl' => 'https://api.linkedin.com/uas/oauth/requestToken',
            'userAuthorizationUrl' => 'https://api.linkedin.com/uas/oauth/authorize',
            'accessTokenUrl' => 'https://api.linkedin.com/uas/oauth/accessToken',
            'consumerKey' => $this->_config["key"],
            'consumerSecret' => $this->_config["secret"],
        );

        $this->_client = $client->getHttpClient($options);
        $this->_client->setMethod(Zend_Http_Client::POST);
        $this->_client->setUri('http://api.linkedin.com/v1/people/~/shares');
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

        if (is_null($post) or !isset($post["comment"]) or $post["comment"] == "") {

            $msg->setError("form", $this->_ci->lang->line("unknownError"));
        }

        if ( isset($post["title"]) and $post["title"] != "" ) {

            if(!isset($post["url"]) or $post["url"] == "") {

                
                $msg->setError("fieldset", sprintf($this->_ci->lang->line("requiredCertainField"), "url"));
            }
            
            if(!isset($post["description"]) or $post["description"] == "") {

                if ($msg->getErrors("fieldset") == "") {

                    $msg->setError("fieldset", sprintf($this->_ci->lang->line("requiredCertainField"), "descripción"));

                } else {

                    $msg->setError("fieldset",$msg->getErrors("fieldset")."<br />".sprintf($this->_ci->lang->line("requiredCertainField"), "descripción"));
                }
            }
        }

        return $msg;
    }

    /**
     * Método para publicar contenido en cuentas linkedin
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
            $this->connect(unserialize($account->getAuth()));

            $private = (isset($post["private"]) and $post['private'] == 1) ? 'connections-only' : 'anyone';

            $xml = $this->_ci->load->view("plugins/linkedin/postTemplate", array(
                "private" => $private,
                "comment" => $post["comment"],
                "title" => $post["title"],
                "url" => $post["url"],
                "img" => $post["img"],
                "description" => $post["description"],
            ),  true);

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n".$xml;

            $this->_client->setRawData($xml,'text/xml');
            $this->_client->setHeaders('Content-Type', 'text/xml');

            $response = $this->_client->request();
            $code = $response->extractCode($response->asString());
            $content =  $response->getBody();

            if ($code >= 200 and $code <= 300) {

                $msg->setTitle($post["comment"]);
                return $this->fetch($msg);

            } else {

                $this->_ci->lang->load("errormessages");
                $msg->setError("form", $this->_ci->lang->line("unknownError").". Error code ".$code." : ".$content);
            }
        }

        return $msg;
    }

    /**
     * @param Message_Data $myMessage
     * @return Message_Data
     */
    public function fetch(Message_Data $myMessage)
    {
        $auth = Auth::get_instance();
        $messageMapper = Message_Mapper::get_instance();
        $message  = New Message_Data();

        $this->_client->setUri('http://api.linkedin.com/v1/people/~/network/updates');
        $this->_client->setParameterGet("type","SHAR");
        $this->_client->setParameterGet("scope","self");
        $this->_client->setParameterGet("from", (time() -5) *1000);
        $this->_client->setMethod(Zend_Http_Client::GET);

        $response = $this->_client->request();
        $content =  $response->getBody();

        $data = new SimpleXMLElement($content);

        $message = Plugins_Linkedin_DB::save($data, 1, $this->_account);
        return $message;
    }

    /* (non-PHPdoc)
     * @see application/models/Common/Common_Publish#reference($message, $account)
     */
    public function reference(Message_Data $message, User_Accounts_Data $account)
    {
        $this->_account = $account;
        $auth = Auth::get_instance();

        if ($message->isValid()) {

            $this->connect(unserialize($account->getAuth()));

            $title = $message->getTitle();
            $shortUrl = Shorter::short($message->getLink());

            $xml = $this->_ci->load->view("plugins/linkedin/postTemplate", array(
                "private" => 'anyone',
                "comment" => $title." ".$shortUrl,
            ),  true);

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n".$xml;

            $this->_client->setRawData($xml,'text/xml');
            $this->_client->setHeaders('Content-Type', 'text/xml');

            $response = $this->_client->request();
            $code = $response->extractCode($response->asString());
            $content =  $response->getBody();

            if ($code >= 200 and $code <= 300) {

                $message->setTitle($title." ".$shortUrl);
                return $this->fetch($message);

            } else {

                $this->_ci->lang->load("errormessages");
                $message->setError("form", $this->_ci->lang->line("unknownError").". Error code ".$code." : ".$content);
            }
        }

        return $message;
    }
}