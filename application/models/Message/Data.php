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
 * Clase contenedora para todos los mensajes de la aplicación
 *
 * @author Mikel Madariaga <mikel@irontec.com>
 */
class Message_Data extends Common_Data
{
    protected $_id;
    protected $_remoteId;
    protected $_idU;
    protected $_idUP;
    protected $_title;
    protected $_data;
    protected $_publishDate;
    protected $_link;
    protected $_owner;

    public function __construct(array $options = NULL)
    {
        parent::__construct();

        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

   /**
    * Asigna el identificador único del mensaje
    * @param integer $value
    * @return Message_Data
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
     * Devuelve el identificador único del mensaje
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Asigna el id remoto (El id del mensaje original en twitter por ejemplo)
     * @param integer $value
     * @return Message_Data
     */
    public function setRemoteId($value)
    {
        $this->_remoteId = $value;
        return $this;
    }

    /**
     * Devuelve el id del emnsaje en la aplicación original [wordpress|twitter|etc]
     * @return unknown_type
     */
    public function getRemoteId()
    {
        return $this->_remoteId;
    }

    /**
     * Asigna el id del usuario al que pertenece el mensaje
     * @param integer $value
     * @return Message_Data
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
     * Devuelve el id usuario al que pertenece el mensaje
     * @return integer
     */
    public function getIdU()
    {
        return $this->_idU;
    }

   /**
    * Asigna el id de la identidad (cuenta) a la que pertenece el mensaje
    * @param integer $value
    * @return Message_Data
    */
    public function setIdUP($value)
    {
        $rules = 'required|is_natural|max_length[10]';
        if ($this->validate($rules, 'idP', $value)) {

            $this->_idUP = $value;
        }

        return $this;
    }

   /**
    * Devuelve el id de la identidad a la que pertenece el mensaje
    * @return integer
    */
    public function getIdUP()
    {
        return $this->_idUP;
    }

   /**
    * Asigna el titulo del mensaje
    * @param string $value
    * @return Message_Data
    */
    public function setTitle($value)
    {
        $rules = 'max_length[200]|required';
        if ($this->validate($rules, 'title', $value)) {

            $this->_title = $value;
        }

        return $this;
    }

    /**
     * Devuelve el titular del mensaje
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Asigna el resto de datos del mensaje (Por lo general metadatos y cualquier contenido que no tenga
     * un campo proópio en base de datos)
     *
     *  Los metadatos más comunes (Que toda aplicación han de ser capaz de manejar, o al menos
     *  saber que existen) son los siguientes:
     *      - categories
     *      - tags
     *      - text
     *
     * @param mixed $value
     * @return Message_Data
     */
    public function setData($value)
    {
        if(is_array($value)) {
        
            $this->_data = $value;
        
        } else {
        
            $this->_data = unserialize($value);
        }

        return $this;
    }

    /**
     * Devuelve todos los metadatos del mensaje o un metadato en concreto
     * si se le pasa la key
     * @param string $key
     * @return mixed
     */
    public function getData($key = null)
    {
        if (!is_null($key)) {

            if (isset($this->_data[$key])) {

                return $this->_data[$key];

            } else {
            
                return false;
            }
        }
        
        return $this->_data;
    }

   /**
    * Asigna la fecha de publicación del mensaje
    * @param datetime $value
    * @return Message_Data
    */
    public function setPublishDate($value)
    {
        $rules = 'required|time';
        if ($this->validate($rules, 'publishDate', $value)) {

            $this->_publishDate = $value;
        }

        return $this;
    }

    /**
     * Devuelve la fecha de publicación del mensaje
     * @return datetime
     */
    public function getPublishDate()
    {
        return $this->_publishDate;
    }

    /**
     * Asigna el link a el mensaje en la fuente original
     * @param string $value
     * @return Message_Data
     */
    public function setLink($value)
    {
        $this->_link = $value;
        return $this;
    }

    /**
     * Devuelve el link a el mensaje en la fuente original
     * @return string
     */
    public function getLink()
    {
        return $this->_link;
    }

    /**
     * Define si el mensaje ha sido redactado/enviado por el própio usuario
     * @param integer $value (1 or 0)
     * @return Message_Data
     */
    public function setOwner($value)
    {
        $this->_owner = $value;
        return $this;
    }

    /**
     * Devuelve si el usuario es el autor del mensaje
     * @return integer
     */
    public function getOwner()
    {
        return $this->_owner;
    }
}