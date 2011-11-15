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
class Columns_PluginsData extends Common_Data
{
    protected $_id;
    protected $_idC;
    protected $_idP;

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

    /* (non-PHPdoc)
     * @see application/models/Common/Common_Data#__set($name, $value)
     */
    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid column plugin property');
        }

        $this->$method($value);
    }

    /* (non-PHPdoc)
     * @see application/models/Common/Common_Data#__get($name)
     */
    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid column plugin property');
        }

        return $this->$method();
    }
    
    /**
     * @param integer $value
     * @return Columns_PluginsData
     */
    public function setId($value)
    {
        $rules = 'required|is_natural|max_length[20]';
        if ($this->validate($rules, 'idC', $value)) {

            $this->_id = $value;
        }

        return $this;
    }

    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param integer $value
     * @return Columns_PluginsData
     */
    public function setIdC($value)
    {
        $rules = 'required|is_natural|max_length[20]';
        if ($this->validate($rules, 'idC', $value)) {

            $this->_idC = $value;
        }

        return $this;
    }

    /**
     * @return integer
     */
    public function getIdC()
    {
        return $this->_idC;
    }

    /**
     * @param integer $value
     * @return Columns_PluginsData
     */
    public function setIdP($value)
    {
        $rules = 'required|is_natural|max_length[20]';
        if ($this->validate($rules, 'idP', $value)) {

            $this->_idP = $value;
        }

        return $this;
    }

    /**
     * @return integer
     */
    public function getIdP()
    {
        return $this->_idP;
    }
}
