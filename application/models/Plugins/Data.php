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
 */
class Plugins_Data extends Common_Data
{
    protected $_id;
    protected $_name;
    protected $_activated;
    protected $_className;
    protected $_updateFrecuency;

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
     * Asigna el id
     * @param integer $value
     * @return Plugins_Data
     */
    public function setId($value)
    {
        $rules = 'required|is_natural_no_zero|max_length[3]';
        if ($this->validate($rules, 'id', $value)) {

            $this->_id = $value;
        }

        return $this;
    }

    /**
     * Devuelve el id
     * @return integer
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Asinga el nombre
     * @param string $value
     * @return Plugins_Data
     */
    public function setName($value)
    {
        $rules = 'required|max_length[60]|min_length[2]';
        if ($this->validate($rules, 'name', $value)) {

            $this->_name = $value;
        }

        return $this;
    }

    /**
     * Devuelve el nombre
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Define si el plugin esta o no activo
     * @param integer $value (1 or 0)
     * @return Plugins_Data
     */
    public function setActivated($value)
    {
        $rules = 'required|is_natural|max_length[1]';
        if ($this->validate($rules, 'activated', $value)) {

            $this->_activated = $value;
        }

        return $this;
    }

    /**
     * @return integer
     */
    public function getActivated()
    {
        return $this->_activated;
    }

    /**
     * Asigna el nombre de la class del plugin
     * @param string $value
     * @return Plugins_Data
     */
    public function setClassName($value)
    {
        $rules = 'max_length[45]|required|min_length[3]';
        if ($this->validate($rules, 'className', $value)) {

            $this->_className = $value;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->_className;
    }

    /**
     * @param integer $value
     * @return Plugins_Data
     */
    public function setUpdateFrecuency($value)
    {
        $rules = 'required|max_length[20]|min_length[2]|is_natural_no_zero';
        if ($this->validate($rules, 'updateFrecuency', $value)) {

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