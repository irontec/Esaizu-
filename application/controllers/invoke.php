<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
class Invoke extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function method ($class = null, $method = null, $msgId = null)
    {
        if ( in_array(null, array($class, $method, $msgId)) ) {
        
            return false;
        }
        
        $className = "Plugins_".ucfirst($class)."_Public";
        if (! class_exists($className) ) {

            return false;
        }

        $class = new $className();

        if (! method_exists($class, $method) ) {

            return false;
        }

        return $class->$method($msgId);
    }
}