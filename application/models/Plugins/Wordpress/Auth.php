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
class Plugins_Wordpress_Auth extends Common_Auth
{
    private $_data;

    /**
     * @return void
     */
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * Responde si el plugin requiere validación remota (Oauth etc)
     * @return boolean
     */
    /* (non-PHPdoc)
     * @see application/models/Common/Common_Auth#hasRemoteAuth()
     */
    public function hasRemoteAuth()
    {
        return false;
    }
    
    /* (non-PHPdoc)
     * @see application/models/Common/Common_Auth#remoteAuth()
     */
    public function remoteAuth() {}

    /**
     * Asigna los datos de conexión
     * @param Array $data
     * @return Plugins_Wordpress_Auth
     */
    /* (non-PHPdoc)
     * @see application/models/Common/Common_Auth#setData($data)
     */
    public function setData($data)
    {
         $this->_data = $data;
         return $this;
    }

    /**
     * Devuelve los datos de conexión
     * @return Array
     */
    /* (non-PHPdoc)
     * @see application/models/Common/Common_Auth#getData()
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Método encargado de dar el visto bueno a los datos facilitados
     * @param User_Account $userAccount
     * @return User_Account | boolean true en caso de que todo sea correcto, el objeto en caso de error
     */
    /* (non-PHPdoc)
     * @see application/models/Common/Common_Auth#validate()
     */
    public function validate(User_Accounts_Data $userAccount = null)
    {
        $validator = Common_Validator::get_instance();
        $validator->validate('required', 'wp_url', $this->_data["url"]);
        $validator->validate('required', 'wp_username', $this->_data["username"], false);
        $validator->validate('required', 'wp_password', $this->_data["password"], false);

        foreach ($validator->getErrorArray() as $key => $val) {

            
            $userAccount->setError($key,$val);
        }

        if (!$userAccount->isValid()) {

            return $userAccount;
        }

        $client = new IXR_Client(prep_url($this->_data["url"]).'/xmlrpc.php');

        if (!$client->query('wp.getCategories', '', $this->_data["username"], $this->_data["password"])) {

            return "Error de wordpress : ".$client->getErrorMessage();
        }

        $user = Auth::get_instance();

        $this->_data["username"] = $this->_ci->encrypt->encode($this->_data["username"], sha1($user->getEmail()));
        $this->_data["password"] = $this->_ci->encrypt->encode($this->_data["password"], sha1($user->getEmail()));
        $this->_data = json_encode($this->_data);
        return true;
    }
}