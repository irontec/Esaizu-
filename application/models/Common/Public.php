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
 * de los plugins que requieran invocar acciones directamente
 * desde la interface de la aplicación
 * @author Mikel Madariaga
 */
abstract class Common_Public
{
    protected $_ci; // framework instance
    protected $_auth;

    function __construct()
    {
        $this->_ci =& get_instance();
        $this->_ci->load->database();

        $this->_auth = Auth::get_instance();
    }
}