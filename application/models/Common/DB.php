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
 * Clase encargada de recoger los mensajes en bruto, recogidos de cada uno de los portales de terceros,
 * tratarlos y guardarlos en base de datos de manera legible para la aplicación
 * @author Mikel Madariaga <mikel@irontec.com>
 */
abstract class Common_DB
{
    protected $_ci; // framework instance

    function __construct()
    {
        $this->_ci =& get_instance();
        $this->_ci->load->database();
    }
}