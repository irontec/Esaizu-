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
class Plugins_Flickr_Auth extends Common_Auth
{
    private $_data;
    private $_config;

    private $_client;

    /**
     * @return void
     */
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        $this->_ci->config->load('apikeys', TRUE);
        $this->_config = $this->_ci->config->item('flickr', 'apikeys');

        $this->_ci->load->library("phpFlickr");
        $this->_ci->phpflickr->initialize($this->_config);
        
        $this->_client = $this->_ci->phpflickr;
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
        $this->_client->auth("write", false);
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
        if (!isset($this->_data["frob"])) {

            return "Acceso denegado";
        }

        $token = $this->_client->auth_getToken($this->_data['frob']);

        if (isset($token["token"])) {
        
            $this->setData(serialize($token));
            return true;

        }

        return "Se produjo un error";
    }
}