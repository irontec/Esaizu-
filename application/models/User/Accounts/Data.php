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
 */
class User_Accounts_Data extends Common_Data
{
    protected $_id;
    protected $_idU;
    protected $_idP;
    protected $_alias;
    protected $_enabled;
    protected $_auth;
    protected $_metadata;
    protected $_lastUpdate;
    protected $_updateFrecuency;

    protected $_name;
    protected $_className;

    /**
     * @param array $options
     * @return void
     */
    public function __construct(array $options = NULL)
    {
        parent::__construct();

        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Metodo que setea todos los valores recogidos en un array
     * @param mixed $options
     * @return Common_Data
     */
    public function setOptions(array $options)
    {
        if (isset($options["metadata"])) {

            $options["metadata"] = unserialize($options["metadata"]);
        }

        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {

                $this->$method($value);
            }
        }
        return $this;
    }

   /**
    * @param integer $value
    * @return User_accounts
    */
    public function setId($value)
    {
        $rules = 'required|is_natural|max_length[10]';
        if ($this->validate($rules, 'id', $value)) {

            $this->_id = $value;
        }

        return $this;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->_id;
    }

   /**
    * Asigna el id usuario
    *
    * @param integer $value
    * @return User_accounts
    */
    public function setIdU($value)
    {
        $rules = 'required|is_natural|max_length[10]';
        if ($this->validate($rules, 'idU', $value)) {

            $this->_idU = $value;
        }

        return $this;
    }

    /**
     * Devuelve el id usuario
     * @return integer
     */
    public function getIdU()
    {
        return $this->_idU;
    }

   /**
    * Asigna el idPlugin
    * @param integer $value
    * @return User_accounts
    */
    public function setIdP($value)
    {
        $rules = 'required|integer|max_length[11]';
        if ($this->validate($rules, 'idP', $value)) {

            $this->_idP = $value;
        }

        return $this;
    }

    /**
     * Devuelve el idPlugin
     * @return integer
     */
    public function getIdP()
    {
        return $this->_idP;
    }

   /**
    * TODO : Comprobar alias único por usuario
    * Asigna el alias de la identidad de usuario
    *
    * @param integer $value
    * @return User_accounts
    */
    public function setAlias($value)
    {
        $rules = 'required|max_length[25]|min_length[3]';
        if ($this->validate($rules, 'alias', $value)) {
            $this->_alias = $value;
        }

        return $this;
    }

    /**
     * Devuelve el alias de la identidad
     * @return string
     */
    public function getAlias()
    {
        return $this->_alias;
    }

   /**
    * Activa o desactiva una identidad de usuario
    * @param integer $value (1 or 0)
    * @return User_accounts
    */
    public function setEnabled($value)
    {
        $rules = 'required|is_natural|max_length[1]';
        if ($this->validate($rules, 'enabled', $value)) {

            $this->_enabled = $value;
        }

        return $this;
    }

    /**
     * @return integer
     */
    public function getEnabled()
    {
        return $this->_enabled;
    }

   /**
    * Asigna los datos de acceso a la identidad de usuario,
    * por lo general objetos serialziado o en json
    *
    * @param obj $value
    * @return User_accounts
    */
    public function setAuth($value)
    {
        $this->_auth = $value;
    }

    /**
     * Devuelve los datos de acceso a la identidad de usuario
     * @return obj
     */
    public function getAuth()
    {
        return $this->_auth;
    }

    /**
     * Asigna la última fecha en la que se comprobo la existencia de nuevos mensajes
     * correspondientes a esta identidad
     *
     * @param datetime | time $value
     * @return User_accounts
     */
    public function setLastUpdate($value)
    {
        if (is_numeric($value)) {

            /**
             * We assume the value is an unix time
             * Convert to a valid timestamp
             */
            $this->_lastUpdate = date("Y-m-d H:i:s", $value);

        } else {

            $this->_lastUpdate = $value;
        }

        return $this;
    }

    /**
     * Devuelve la última fecha en la que se comprobo la existencia de nuevos mensajes
     * @return datetime
     */
    public function getLastUpdate()
    {
        return $this->_lastUpdate;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Asigna el nombre el plugin
     * @param string $name
     * @return User_accounts
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * Devuelve el nombre del plugin
     * @return string
     */
    public function getClassName()
    {
        return $this->_className;
    }

    /**
     * Asigna el nombre de la clase del plugin
     * @param string $name
     * @return User_accounts
     */
    public function setClassName($name)
    {
        $this->_className = $name;
        return $this;
    }

    /**
     * Recoge los metadatos de la identidad (Categorías por ejem.)
     * @param integer $key
     * @return obj
     */
    public function getMetadata($key = null) {

        if (is_null($key)) {

            return $this->_metadata;

        } else if(is_array($this->_metadata) and isset($this->_metadata[$key])) {

            return $this->_metadata[$key];

        } else {

            return false;
        }
    }
    
    /**
     * Existen dos modos de uso para este método:
     *
     *  - Pasarle un solo valor ($key) que sea un array. El atributo
     *  metadata del objeto será reemplazado con este array.
     *
     *  - Pasarle un $key y su $value modificando solo el valor del array en $key.
     *
     *  @param string | array $key
     *  @param string obj $value
     *  @return User_Accounts
     */
    public function setMetadata($key, $value = null) {

        if(is_null($value) and is_array($key)) {

            $this->_metadata = $key;

        } else {

            if (is_null($this->_metadata)) {

                $this->_metadata = array();
            }

            $this->_metadata[$key] = $value;
        }

        return $this;
    }

    /**
     * Define la fracuencia de actualización del plugin
     * @param integer $value
     * @return User_Accounts_Data
     */
    public function setUpdateFrecuency($value)
    {
        $rules = 'required|max_length[20]|min_length[2]|is_natural_no_zero';
        if ($this->validate($rules, 'className', $value)) {

            $this->_updateFrecuency = $value;
        }

        return $this;
    }

    /**
     * @return integer
     */
    public function getUpdateFrecuency()
    {
        return $this->_updateFrecuency;
    }
}