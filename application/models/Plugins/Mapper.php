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
 * Clase encargada de mediar entre las base de datos y la aplicación en todo lo referente a plugins
 * @author Mikel Madariaga <mikel@irontec.com>
 */
class Plugins_Mapper extends Common_Mapper
{
    public static $instance;

    /**
     * @return void
     */
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        self::$instance =& $this;
    }

    /**
     * Devuelve una instacia del mapper
     * @return Plugins_Mapper
     */
    public static function get_instance()
    {
        if (self::$instance == null) {
            self::$instance = new Plugins_Mapper();
        }

        return self::$instance;
    }

    /**
     * Método para realizar busquedas en la tabla plugins
     *
     * @param array $where | Plugins_Data $where
     * @param integer $limit
     * @param integer $offset
     * @param array $orderBy
     * @return Plugins_Data array
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#find()
     */
    public function find($where = array(), $limit = false, $offset = false, $orderBy = array())
    {
        if ($where instanceof Plugins_Data) {

            $where = $where->getExistingOptions();
            unset($where["Errors"]);
        }
        
        $data = $this->_ci->db->from('plugins')
                              ->where($where)
                              ->limit($limit, $offset);
                              
        foreach ($orderBy as $key => $val) {
        
            $this->_ci->db->order_by($key, $val);
        }
        
        $data = $this->_ci->db->get()->result_array();
                              
        $items = array();

        foreach ($data as $item) {
            $items[] = new Plugins_Data($item);
        }

        return $items;
    }

    /**
     * Devuelve todos los registros en base de datos
     *
     * @return Plugins_Data array
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#fetchAll()
     */
    public function fetchAll()
    {
        $data = $this->_ci->db->get('plugins')->result_array();
        $items = array();

        foreach ($data as $item) {
            $items[] = new Plugins_Data($item);
        }

        return $items;
    }

    /**
     * Devuelve el número de registros en base de datos
     *
     * @param array $where
     * @return integer
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#totalRows()
     */
    public function totalRows($where = array())
    {
        $this->_ci->db->select('count(*) as itemNum');
        return $this->_ci->db->get('plugins')->row()->itemNum;
    }

    /**
     * Crea o actualiza un registro en base de datos
     *
     * @param Plugins_Data $plugins
     * @return bool | int insert_id
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#save()
     */
    public function save(Plugins_Data $plugins = null)
    {
        if (!$plugins instanceof Plugins_Data) {

            throw new Exception('Plugins_Data object required');
        }

        if (!$plugins->isValid()) {

             throw new Exception('Incorrect data');
        }

        $data = array(
            'id' => $plugins->getId(),
            'name' => $plugins->getName(),
            'activated' => $plugins->getActivated(),
            'className' => $plugins->getClassName(),
            'updatefrecuency' => $plugins->getUpdateFrecuency()
        );

        if (null === ($id = $plugins->getId())) {

            unset($data['id']);
            $this->_ci->db->set($data);
            $this->_ci->db->insert('plugins');
            return $this->_ci->db->insert_id();

        } else {

            $this->_ci->db->where('id', $data['id']);
            unset($data['id']);
            $this->_ci->db->set($data);
            $this->_ci->db->update('plugins');
            return true;
        }
    }

    /**
     * Elimina registros en base de datos
     *
     * @param array $where
     * @return bool
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#delete()
     */
    public function delete( $where = array() )
    {
        if ($where instanceof Plugins_Data) {

            $where = $where->getOptions();
            unset($where["Errors"]);
        }

        $this->_ci->db->where($where);
        $this->_ci->db->delete('plugins');
        return true;
    }
}