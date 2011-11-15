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
 * Clase abstracta que define los metodos que han de implementar los mappers de la aplicación.
 * @author Mikel Madariaga <mikel@irontec.com>
 */
abstract class Common_Mapper
{
    protected $_ci; // framework instance
    public static $instance;

    function __construct()
    {
        $this->_ci =& get_instance();
        $this->_ci->load->database();
    }

    abstract public static function get_instance();

    abstract public function fetchAll();

    abstract public function find();

    abstract public function totalRows();

    abstract public function save();

    abstract public function delete();
}