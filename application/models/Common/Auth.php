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
 * Clase abstracta que han de implementar todos
 * de los plugins engarda de validar que los datos de conexión facilitados son correctos
 * además de definir si el plugin requiere de validación remota o local (oauth)
 *
 * @author Mikel Madariaga <mikel@irontec.com>
 */
abstract class Common_Auth
{
    protected $_ci; // framework instance

    function __construct()
    {
        $this->_ci =& get_instance();
        $this->_ci->load->database();
    }

    /**
     * @return boolean
     */
    abstract public function hasRemoteAuth();
    
    /**
     * @return void
     */
    abstract public function remoteAuth();
    
    /**
     * @param mixed $data
     * @return void
     */
    abstract public function setData($data);

    /**
     * @return mixed
     */
    abstract public function getData();

    /**
     * @return boolean
     */
    abstract public function validate();
}