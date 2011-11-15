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
class Columns_Data extends Common_Data
{
    protected $_id;
    protected $_idU;
    protected $_identificador;
    protected $_minimized;
    protected $_order;
    protected $_lastUserCheck;
    protected $_filters;
    protected $_type;

    /* campos relacionados */
    protected $_plugins = array();
    protected $_messages = array();

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
     * @param integer $value
     * @return Columns_Data
     */
    public function setId($value)
    {
        $rules = 'required|is_natural|max_length[20]';
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
     * @param integer $value
     * @return Columns_Data
     */
    public function setIdU($value)
    {
        $rules = 'required|is_natural|max_length[20]';
        if ($this->validate($rules, 'idU', $value)) {

            $this->_idU = $value;
        }

        return $this;
    }

    public function getIdU()
    {
        return $this->_idU;
    }

    /**
     * @param string $value
     * @return Columns_Data
     */
    public function setIdentificador($value)
    {
        $rules = 'max_length[80]|required|min_length[4]';
        if ($this->validate($rules, 'identificador', $value)) {

            $this->_identificador = $value;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentificador()
    {
        return $this->_identificador;
    }

    /**
     * @param integer $value
     * @return Columns_Data
     */
    public function setMinimized($value)
    {
        $rules = 'max_length[1]|required|is_natural';
        if ($this->validate($rules, 'minimized', $value) and $value < 2) {

            $this->_minimized = $value;

        } else if ($value > 1) {

            $this->_ci->lang->load("errormessages");
            $this->setError("minimized", $this->_ci->lang->line("invalidValue"));
        }

        return $this;
    }

    /**
     * @return integer
     */
    public function getMinimized()
    {
        return $this->_minimized;
    }

    /**
     * @param time | date $value
     * @return Columns_Data
     */
    public function setLastUserCheck($value)
    {
        if (is_numeric($value)) {

            /**
             * We assume the value is an unix time
             * Convert to a valid timestamp
             */
            $this->_lastUserCheck = date("Y-m-d H:i:s", $value);

        } else {

            $this->_lastUserCheck = $value;
        }
        
        return $this;
    }

    /**
     * @return datetime
     */
    public function getLastUserCheck()
    {
        return $this->_lastUserCheck;
    }

    
    /**
     * @param string | array $filters
     * @return Columns_Data
     */
    public function setFilters($filters)
    {
        if (is_string($filters)) {

            $filters = unserialize($filters);
        }
        $this->_filters = $filters;
        
        return $this;
    }
    
    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->_filters;
    }

    /**
     * @param string $type
     * @return Columns_Data
     */
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @param integer $order
     * @return Columns_Data
     */
    public function setOrder($order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * @return integer
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @param array $data
     * @return Columns_Data
     */
    public function setPlugins(Array $data)
    {
        $this->_plugins = array();

        foreach ($data as $item) {

            $this->addPlugin($item);
        }

        return $this;
    }

    /**
     * Enlaza una nueva identidad a una columna
     * @param Columns_PluginsData $data
     * @return Columns_Data
     */
    public function addPlugin(Columns_PluginsData $data)
    {
        $this->_plugins[] = $data;
        return $this;
    }

    /**
     * Devuelve las identidades asociadas a la columna
     * @return Columns_PluginsData array
     */
    public function getPlugins()
    {
        return $this->_plugins;
    }
    
    /**
     * Reemplaza las identidades asociadas a la columna
     * @param array $userPluginIds
     * @return Columns_Data
     */
    public function replacePlugins(Array $userPluginIds)
    {
        $columnPlugins = $this->getPlugins();

        $currentPluginIds = array();
        $pluginsToRemove = array();
        $pluginsToKeep = array();
        $pluginsToAdd = array();

        foreach ($columnPlugins as $plugin) {

            $currentPluginIds[] = $plugin->getId();
        }

        foreach($columnPlugins as $plugin) {

            if (!in_array($plugin->getId(), $userPluginIds)) {

                $pluginsToRemove[] = $plugin;

            } else {

                $pluginsToKeep[] = $plugin;
            }
        }

        foreach ($userPluginIds as $pid) {

            if(!in_array($pid, $currentPluginIds)) {

                $pluginsToAdd[] = $pid;
            }
        }

        $cPluginMapper = Columns_PluginsMapper::get_instance();
        foreach ($pluginsToRemove as $plugin) {

            $cPluginMapper->delete($plugin);
        }

        $newPlugins = $pluginsToKeep;

        foreach ($pluginsToAdd as $pid) {

            $cPlugin = new Columns_PluginsData();
            $cPlugin->setIdP($pid);
            $newPlugins[] = $cPlugin;
        }

        $this->setPlugins($newPlugins);
        return $this;
    }

    /**
     * Devuelve los mensajes de una cuenta
     * @return Message_Data array
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * Método para la carga genérica de mensajes de la columna
     * @param obj $options
     * @param integer $limit
     * @param integer $offset
     * @param integer $from
     * @param integer $since
     * @param time $timestamp
     * @return void
     */
    private function fetchMessages($options, $limit, $offset = null , $from = 0, $since = 0, $timestamp = null)
    {

        $auth = Auth::get_instance();
        $ids = array(
            "messages.idUP" => array(),
        );

        foreach ($options as $option) {

            $ids["messages.idUP"][] = $option->id;
        }
        
        $where = array();
        $where["messages.idu"] = $auth->getuserId();

        if ($from != 0) {

            $where["messages.id >"] = $from;

            if (!is_null($timestamp)) {

                $where["messages.publishDate  >"] = $timestamp;
            }
        }

        if ($since != 0) {

            $where["messages.id <"] = $since;
                
            if (!is_null($timestamp)) {

                $where["messages.publishDate  <"] = $timestamp;
            }
        }
        
        if ($from == 0 and $since == 0 and !is_null($timestamp)) {

            $where["messages.publishDate >"] = $timestamp;
        }
        
        $filters = $this->parseFilters($this->getFilters());
        
        $where = array_merge($where, $filters);
        
        $messageMapper = Message_Mapper::get_instance();
        $this->_messages = $messageMapper->find($where,$ids, $limit, $offset);
        
        //echo $this->_ci->db->last_query()."<br />";
    }

    /**
     * @param array $filters
     * @return array
     */
    private function parseFilters($filters)
    {
        $where = array();
        
        if (is_array($filters)) {

            if (isset($filters["autor"])) {

                switch($filters["autor"]) {

                    case "me":

                        $where["owner"] = 1;
                        break;

                    case "notMe":

                        $where["owner"] = 0;
                        break;
                }
            }

            if (isset($filters["content"])) {

                $where[] = " ( data like '%".$filters["content"]."%' or title like '%".$filters["content"]."%') ";
            }
        }

        return $where;
    }

    /**
     * Carga los primeros n mensajes de la columna
     * @param integer $limit
     * @param integer $from
     * @param integer $since
     * @param time $timestamp
     * @return Columns_Data
     */
    public function loadColumnMessages($limit = 10 , $from = 0, $since = 0, $timestamp = null)
    {
        $conditionals = array();

        foreach ($this->_plugins as $plugin) {

            $obj = new stdClass();
            $obj->id = $plugin->getId();
            $obj->idUP = $plugin->getIdP();

            $conditionals[] = $obj;
        }

        $this->fetchMessages($conditionals, $limit , null, $from, $since, $timestamp);
        return $this;
    }

    /**
     * Carga n mensajes de la columna a partir del mensaje m
     * @param integer $offset
     * @param integer $limit
     * @return Columns_Data
     */
    public function loadMoreColumnMessages($offset = 10, $limit = 10)
    {
        $auth = Auth::get_instance();
        $conditionals = array();

        foreach ($this->_plugins as $plugin) {

            $obj = new stdClass();
            $obj->id = $plugin->getId();
            $obj->idUP = $plugin->getIdP();
            $obj->filtros = $plugin->getFiltros();
            
            $conditionals[] = $obj;
        }

        $this->fetchMessages($conditionals, $limit);
        return $this;
    }
}