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
 * Clase encargada de recoger y guardar mensajes de Linkedin
 */
class Plugins_Linkedin_Cron extends Common_Cron
{
    protected $_account;
    /**
     * @var Zend_Oauth_Client
     */
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
     * Método encargado de establecer conexión con Linkedin
     * @param User_Accounts_Data $account
     * @param obj $auth
     * (non-PHPdoc)
     * @see application/models/Common/Common_Cron#connect($idp)
     */
    public function connect($account)
    {
        $this->start = microtime();

        $this->_account = $account;
        $this->_idUP = $this->_account->getId();
        $client = unserialize($account->getAuth());

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
        $this->_client->setMethod(Zend_Http_Client::GET);
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
        $this->_client->setUri('http://api.linkedin.com/v1/people/~/network/updates');
        $this->_client->setParameterGet("type","SHAR");

        $response = $this->_client->request();
        $content =  $response->getBody();

        $xml = new SimpleXMLElement($content);

        //$this->save($xml);
        Plugins_Linkedin_DB::save($xml, 0, $this->_account);

        $this->_client->setParameterGet("scope","self");
        $response = $this->_client->request();
        $content =  $response->getBody();

        $xml = new SimpleXMLElement($content);

        Plugins_Linkedin_DB::save($xml, 1, $this->_account);

        if ($this->debugMode === true) {
            echo "\r\n Linkedin cron elapset time : ".$this->elapsedTime()."s\r\n";
        }

        $accountMapper = User_Accounts_Mapper::get_instance();
        $this->_account->setLastUpdate(time());

        $data = array(
            "lastUpdate" => $this->_account->getLastUpdate()
        );

        $accountMapper->updateFromArray($this->_account->getId(), $data);
    }
}