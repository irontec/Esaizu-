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
 * Métodos públicos accesibles desde la propia interface de la applicación
 */
class Plugins_Twitter_Public extends Common_Public
{
    /**
     * @return void
     */
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * @param User_Account_Data $account
     * @return obj
     */
    private function _fetchClient($account)
    {
        $config = array(
            'version' => '1.0',
            'requestScheme' => Zend_Oauth::REQUEST_SCHEME_HEADER,
            'signatureMethod' => 'HMAC-SHA1',
            'callbackUrl' => site_url('accounts/auth'),
        );

        $this->_ci->config->load('apikeys', TRUE);
        $config = array_merge($config, $this->_ci->config->item('twitter', 'apikeys'));
        $oauth = unserialize($account->getAuth());
        
        $client = new ExtendedTwitter();
        $client->setLocalHttpClient($oauth->getHttpClient($config));
        return $client;
    }

    /**
     * @param integer $msgId
     * @return void
     */
    public function retweet ($msgId)
    {
        $msgMapper = Message_Mapper::get_instance();

        $msg = new Message_Data();
        $msg->setId($msgId);
        $msg = array_shift($msgMapper->find($msg));

        $idUP = $msg->getIdUP();

        if (! is_numeric($idUP)) {

            return;
        }

        $account = new User_Accounts_Data();
        $account->setId($idUP);
        $account->setIdU($this->_auth->getUserId());

        $accountMapper = User_Accounts_Mapper::get_instance();
        $account = array_shift($accountMapper->find($account));

        if (! $account instanceof User_Accounts_Data) {

            return;
        }

        $client = $this->_fetchClient($account);
        $retweet = $client->statusRetweet($msg->getRemoteId());

        $cron = new Plugins_Twitter_Cron();
        $cron->connect($account);
        $cron->update();
        
        redirect("", "refresh");
    }

    /**
     * @param integer $msgId
     * @return void
     */
    public function favorite ($msgId)
    {
        $msgMapper = Message_Mapper::get_instance();

        $msg = new Message_Data();
        $msg->setId($msgId);
        $msg = array_shift($msgMapper->find($msg));

        $idUP = $msg->getIdUP();

        if (! is_numeric($idUP)) {

            return;
        }

        $account = new User_Accounts_Data();
        $account->setId($idUP);
        $account->setIdU($this->_auth->getUserId());

        $accountMapper = User_Accounts_Mapper::get_instance();
        $account = array_shift($accountMapper->find($account));

        if (! $account instanceof User_Accounts_Data) {

            return;
        }

        $client = $this->_fetchClient($account);
        $resp = $client->favoriteCreate($msg->getRemoteId());
        
        $msgData = $msg->getData();
        $msgData["favorited"] = "true";
        $msg->setData($msgData);
        $msgMapper->save($msg);

        redirect("", "refresh");
    }

    /**
     * @param integer $msgId
     * @return void
     */
    public function revertfavorite ($msgId)
    {
        $msgMapper = Message_Mapper::get_instance();

        $msg = new Message_Data();
        $msg->setId($msgId);
        $msg = array_shift($msgMapper->find($msg));

        $idUP = $msg->getIdUP();

        if (! is_numeric($idUP)) {

            return;
        }

        $account = new User_Accounts_Data();
        $account->setId($idUP);
        $account->setIdU($this->_auth->getUserId());

        $accountMapper = User_Accounts_Mapper::get_instance();
        $account = array_shift($accountMapper->find($account));

        if (! $account instanceof User_Accounts_Data) {

            return;
        }

        $client = $this->_fetchClient($account);
        $resp = $client->favoriteDestroy($msg->getRemoteId());

        
        $msgData = $msg->getData();
        $msgData["favorited"] = "false";
        $msg->setData($msgData);
        $msgMapper->save($msg);

        redirect("", "refresh");
    }
}