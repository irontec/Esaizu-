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
class Plugins_Twitter_Auth extends Common_Auth
{
    private $_data;
    private $_config;

    /**
     * @return void
     */
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        $config = array(
            'version' => '1.0',
            'requestScheme' => Zend_Oauth::REQUEST_SCHEME_HEADER,
            'signatureMethod' => 'HMAC-SHA1',
            'callbackUrl' => site_url('accounts/auth'),
        );

        $this->_ci->config->load('apikeys', TRUE);
        $config = array_merge($config, $this->_ci->config->item('twitter', 'apikeys'));

        $this->_config = $config;
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
        $consumer = new Zend_Oauth_Consumer($this->_config);

        //fetch a request token
        $token = $consumer->getRequestToken();

        //persist the token to storage
        if (session_id() == "") {
            session_start();
        }

        $_SESSION['TWITTER_REQUEST_TOKEN'] = serialize($token);

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
        if (isset($this->_data["denied"])) {

            return "Acceso denegado";
        }

        if (session_id() == "") {
            session_start();
        }

        $consumer = new Zend_Oauth_Consumer($this->_config);

        $token = $consumer->getAccessToken($this->_data, unserialize($_SESSION['TWITTER_REQUEST_TOKEN']));
        unset($_SESSION['TWITTER_REQUEST_TOKEN']);

        $this->_data = serialize($token);

        return true;
    }
}