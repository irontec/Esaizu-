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
class Plugins_Facebook_Auth extends Common_Auth
{
    private $_data;
    private $_config;

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        $this->_ci->config->load('apikeys', TRUE);
        $this->_config = $this->_ci->config->item('facebook', 'apikeys');
    }

    /* (non-PHPdoc)
     * @see application/models/Common/Common_Auth#hasRemoteAuth()
     *
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
        $targetUrl = site_url('accounts/auth');
        redirect("https://www.facebook.com/dialog/oauth?client_id="
        .$this->_config["appId"]
        ."&redirect_uri=$targetUrl&scope=offline_access,read_stream,publish_stream");
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
        if (!isset($this->_data["code"])) {

            return "Acceso denegado";
        }

        $this->_data = serialize($this->_data);
        return true;
    }
}