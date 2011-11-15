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
 * Clase encargada de recoger y guardar mensajes de twitter
 */
class Plugins_Twitter_Cron extends Common_Cron
{
    protected $_account;
    protected $_client;
    protected $_conf;

    /**
     * @return void
     */
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * Método encargado de establecer conexión con twitter
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
        $this->_idUP = $this->_account->getId();
        $this->_credentials = unserialize($account->getAuth());

        $config = array(
            'version' => '1.0',
            'requestScheme' => Zend_Oauth::REQUEST_SCHEME_HEADER,
            'signatureMethod' => 'HMAC-SHA1',
            'callbackUrl' => site_url('accounts/auth'),
        );

        $this->_ci->config->load('apikeys', TRUE);
        $config = array_merge($config, $this->_ci->config->item('twitter', 'apikeys'));
        $this->_config = $config;

        $this->_client = new Zend_Service_Twitter();
        $this->_client->setLocalHttpClient($this->_credentials->getHttpClient($this->_config));
    }

    /**
     * Método encargado de recoger los timelines del usuario y sus amigos
     * @return void
     */
    /* (non-PHPdoc)
     * @see application/models/Common/Common_Cron#update()
     */
    public function update()
    {
        if ($this->_client->isAuthorised()) {

            $messageMapper = Message_Mapper::get_instance();
            $lastId = $messageMapper->getLastId($this->_account->getId());

            if (is_numeric($lastId)) {

                $options = array("since_id" => $lastId);

            } else {

                $options = array();
            }

            $resp = $this->_client->statusFriendsTimeline($options);

            if (!$resp->isError()) {

                Plugins_Twitter_DB::save($resp, "0", $this->_account->getId());

            } else {

                //echo "connection error";
            }

            $resp = $this->_client->statusUserTimeline(array("include_rts" => 1));

            if (!$resp->isError()) {

                Plugins_Twitter_DB::save($resp, "1", $this->_account->getId());

            } else {

                //echo "connection error";
            }

        } else {

            //echo "client not authorized";
        }

        if ($this->debugMode === true) {
            echo "\r\n Twitter cron elapset time : ".$this->elapsedTime()."s\r\n";
        }

        $accountMapper = User_Accounts_Mapper::get_instance();
        $this->_account->setLastUpdate(time());

        $data = array(
            "lastUpdate" => $this->_account->getLastUpdate()
        );

        $accountMapper->updateFromArray($this->_account->getId(), $data);
    }
}