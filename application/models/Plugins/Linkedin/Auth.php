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
 * Clase engarda de validar que los datos de conexión facilitados son correctos
 * además de definir si el plugin requiere de validación remota o local (oauth)
 */
class Plugins_Linkedin_Auth extends Common_Auth
{
    private $_data;
    private $_config;

    /**
     * @return unknown_type
     */
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        $this->_ci->config->load('apikeys', TRUE);
        $this->_config = $this->_ci->config->item('linkedin', 'apikeys');
    }

    /* (non-PHPdoc)
     * @see application/models/Common/Common_Auth#hasRemoteAuth()
     */
    public function hasRemoteAuth()
    {
        return true;
    }

    /* (non-PHPdoc)
     * @see application/models/Common/Common_Auth#remoteAuth()
     */
    public function remoteAuth()
    {
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

        $consumer = new Zend_Oauth_Consumer($options);

        //fetch a request token
        $token = $consumer->getRequestToken();

        //persist the token to storage
        if (session_id() == "") {
            session_start();
        }

        $_SESSION['LINKEDIN_REQUEST_TOKEN'] = serialize($token);

        //redirect the user
        $consumer->redirect();
    }

    /* (non-PHPdoc)
     * @see application/models/Common/Common_Auth#setData($data)
     */
    public function setData($data)
    {
         $this->_data = $data;
    }

    /* (non-PHPdoc)
     * @see application/models/Common/Common_Auth#getData()
     */
    public function getData()
    {
        return $this->_data;
    }

    /* (non-PHPdoc)
     * @see application/models/Common/Common_Auth#validate()
     */
    public function validate()
    {
        
        if (!isset($this->_data["oauth_token"])) {

            return "Acceso denegado";
        }

        if (session_id() == "") {
            session_start();
        }

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

        $consumer = new Zend_Oauth_Consumer($options);

        $token = $consumer->getAccessToken($this->_data, unserialize($_SESSION['LINKEDIN_REQUEST_TOKEN']));
        unset($_SESSION['LINKEDIN_REQUEST_TOKEN']);

        $this->_data = serialize($token);
        return true;
    }
}