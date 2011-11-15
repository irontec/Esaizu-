<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
 * Clase abstracta que simplifica la gestión de datos cargados desde base de datos.
 * Incluye metodos para la validación de los datos "seteados", así como variables
 * para la gestión de errores que se pudieran producir
 * @author Mikel Madariaga <mikel@irontec.com>
 */
abstract class Common_Data
{
    protected $_ci;
    protected $_errorArray = array();
    protected $_errorMessages = array();

    protected $_validator;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_ci =& get_instance();

        $this->_validator = Common_Validator::get_instance();

        // Set the character encoding in MB.
        if (function_exists('mb_internal_encoding')) {

            mb_internal_encoding($this->_ci->config->item('charset'));
        }

        $this->_ci->lang->load('form_validation');
        log_message('debug', "DataObject Class Initialized");
    }

    /**
     * Setter genérico
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid property '.$name);
        }

        $this->$method($value);
    }

    /**
     * Getter genérico
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid property '.$name);
        }

        return $this->$method();
    }

    /**
     * Metodo que setea todos los valores recogidos en un array
     * @param mixed $options
     * @return Common_Data
     */
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {

            if (is_null($value)) {

                continue;
            }

            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {

                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Devuelve los valores de todos los los attributos del objeto
     * @return array
     */
    public function getOptions()
    {
        $methods = get_class_methods($this);
        $data = array();

        for ($i =0; $i < count($methods); $i++) {

            $method = $methods[$i];
  
            if (substr($method, 0, 3) == "get" and !in_array($method, array( "getOptions", "getExistingOptions"))) {

                $varName = substr($method, 3);
                $data[$varName] = $this->$method();
            }
        }
        return $data;
    }

    /**
     * Devuelve los valores de todos los los attributos del objeto que tengan un valor asignado
     * @return array
     */
    public function getExistingOptions()
    {

        $items = $this->getOptions();
        $data = array();

        foreach ($items as $key=>$value) {
        
            if ($value !== null) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * Atajo a la función print_r implementado en todos los objetos del tipo Data
     */
    public function print_r() {

        echo "<pre>";
            print_r($this->getOptions());
        echo "</pre>";
    }
    
    /**
     * Atajo a la función var_dump implementado en todos los objetos del tipo Data
     */
    public function var_dump($fullObject = false) {

        echo "<pre>";
            if ($fullObject) {
                var_dump($this);
            } else {
                var_dump($this->getOptions());
            }
                
        echo "</pre>";
    }
    
    /**
     * Asigna a todos los attributos del objeto el valor null ignorando las validaciones própias de cada campo
     *
     * Optionalmente también hace un reset del errorlog
     *
     * @param boolean
     */
    public function resetAll($eraseErrors = false)
    {

        $options = $this->getExistingOptions();

        foreach ($options as $key => $value) {
        
            $altKey = "_".lcfirst($key);

            if (isset($this->$key)) {
                $this->$key = "";
            
            } else if (isset($this->$altKey)) {

                $this->$altKey = "";
            }
        }

        if ($eraseErrors) {
            $this->_errorArray = array();
        }
    }

    /**
     * Asigna el valor null a el attributo selecionado ignorando cualquier validacion definida
     *
     * @param string
     */
    public function reset($key)
    {
        
        if (!$key) {
            return false;
        }

        $altKey = "_".lcfirst($key);

        if (isset($this->$key)) {
            $this->$key = "";
            return $this;

        } else if (isset($this->$altKey)) {

            $this->$altKey = "";
            return $this;
        }
        
        return false;
    }

    /**
     * Comprueba si el objeto cumple con todas las validaciones predefinidas
     *
     * @return boolean
     */
    public function isValid()
    {

        if (count($this->_errorArray) > 0) {

            return false;
        }
        
        return true;
    }
    
    /**
     * Establece un error
     *
     * @param string
     * @param string
     */
    public function setError($key, $msg)
    {

        $this->_errorArray[$key] = $msg;
    }

    public function getErrors($key = null)
    {
        if ($key == null) {

            return $this->_errorArray;

        } else {

            if (isset($this->_errorArray[$key])) {

                return $this->_errorArray[$key];
            }
        }
    }

    /**
     * Valida un valor contra las reglas de validación establecidas
     *
     * @param string
     * @param string
     */
    protected function validate($rules, $fieldname, $postdata = NULL)
    {
        $this->_validator->validate($rules, $fieldname, $postdata);

        $errorArray = $this->_validator->getErrorArray();
        $errorMessages = $this->_validator->getErrorMessages();

        if (count($errorArray) > 0) {

            $this->_errorArray = array_merge($this->_errorArray, $errorArray);
            $this->_errorMessages = array_merge($this->_errorMessages, $errorMessages);
            return false;
        }

        return true;
    }
}