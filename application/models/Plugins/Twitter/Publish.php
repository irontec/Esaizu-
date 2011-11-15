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
 * Clase encargada de publicar en twitter
 */
class Plugins_Twitter_Publish extends Common_Publish
{
    protected $_client;
    protected $_account;

    /**
     * @return void
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Método para conectarse a una identidad twitter
     * @param obj $auth
     * @return void
     */
    /* (non-PHPdoc)
     * @see application/models/Common/Common_Publish#connect($token)
     */
    public function connect($token)
    {
        $this->_ci->config->load('apikeys', TRUE);
        $config = $this->_ci->config->item('twitter', 'apikeys');

        $options = array(
            'accessToken'    => $token,
            'consumerKey'    => $config["consumerKey"],
            'consumerSecret' => $config["consumerSecret"]
        );

        $this->_client = new Zend_Service_Twitter($options);
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
        $msg = new Message_Data();
        $this->_ci->lang->load("errormessages");

        if (is_null($post)) {

            $msg->setError("form", $this->_ci->lang->line("unknownError"));
            return $msg;
        }

        if (isset($post["tw_content"]) and $post["tw_content"] != "") {

            if (strlen($post["tw_content"]) > 140) {
            
                $msg->setError("tw_content", sprintf($this->_ci->lang->line("maxStrLength"), 140));
            
            } else {
            
                $msg->setTitle($post["tw_content"]);
            }
                

        } else {

            $msg->setError("tw_content", $this->_ci->lang->line("requiredField"));
        }

        return $msg;
    }

    /**
     * Método para publicar contenido en cuentas twitter
     * @param array $post
     * @param User_Accounts_Data $targetAccount
     * @return Message_Data
     */
    /* (non-PHPdoc)
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

            $response = $this->_client->status->update($msg->getTitle());

            if ($response instanceof Zend_Rest_Client_Result) {

                return $this->fetch($response);
            }
        }

        return $msg;
    }

    /**
     * @param Zend_Rest_Client_Result $response
     * @param integer $owner
     * @return Message_Data
     */
    private function fetch(Zend_Rest_Client_Result $response, $owner = 1)
    {
        $auth = Auth::get_instance();
        $messageMapper = Message_Mapper::get_instance();
        $message  = New Message_Data();

        $status = array(
            'created_at' => date("Y-m-d H:i:s", strtotime((string) $response->created_at)),
            'id' => (string) $response->id,
            'text' => (string) $response->text,
            'source' => (string) $response->source,
            'favorited' => (string) $response->favorited,
            'user_name' => (string) $response->user->name,
            'user_screen_name' => (string) $response->user->screen_name,
            'retweet_count' => (string) $response->retweet_count,
            'profile_image_url' => (string) $response->user->profile_image_url,
        );

        $data = array(
            'source' => $status['source'],
            'favorited' => $status['favorited'],
            'user_name' => $status['user_name'],
            'retweet_count' => $status['retweet_count'],
            'profile_image_url' => $status['profile_image_url'],
        );

        $message->setRemoteId($status["id"])
                ->setIdU($auth->getUserId())
                ->setIdUP($this->_account->getId());

        $existingMessages = $messageMapper->find($message);
        if (count($existingMessages) > 0) {

            $message = $existingMessages[0];
        }

        $message->setTitle($status["text"])
                ->setPublishDate($status["created_at"])
                ->setLink("http://www.twitter.com/".$status["user_screen_name"])
                ->setData(serialize($data))
                ->setOwner($owner);

        if ($message->isValid()) {

            $messageMapper->save($message);
        }

        return $message;
    }

    /* (non-PHPdoc)
     * @see application/models/Common/Common_Publish#reference($message, $account)
     */
    public function reference (Message_Data $message, User_Accounts_Data $account)
    {
        $auth = Auth::get_instance();
        $this->_account = $account;

        if ($message->isValid()) {

            $this->connect(unserialize($this->_account->getAuth()));
            $msg = $message->getTitle();

            if (is_array($message->getData("tags"))) {

                $tags = $message->getData("tags");

            } else {

                $tags = explode(",", $message->getData("tags"));
            }

            $shortUrl = Shorter::short($message->getLink());

            if( (strlen($msg) + strlen($shortUrl) + 1) < 140 ) {

                $msg.= " ".$shortUrl;
            }

            if(is_array($tags)) {

                foreach($tags as $tag) {

                    if ($tag == "") {

                        continue;
                    }

                    $tag = trim($tag);

                    if( (strlen($msg) + strlen($tag) + 2) < 140 ) {

                        $msg .= " #".$tag;

                    } else {

                        break;
                    }
                }
            }

            $response = $this->_client->status->update($msg);
            $this->fetch($response);
        }

        return true;
    }
}