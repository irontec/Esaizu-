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
 * Clase encargada de recoger y guardar los plugins activos en cada columna
 *
 * @author Mikel Madariaga <mikel@irontec.com>
 */
class Columns_PluginsMapper extends Common_Mapper
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
     * @return Columns_PluginsMapper
     */
    public static function get_instance()
    {
        if (self::$instance == null) {
            self::$instance = new Columns_PluginsMapper();
        }

        return self::$instance;
    }

    /**
     * Método para realizar busquedas en la tabla userColumnPlugins
     *
     * @param array $where | Columns_PluginsData $where
     * @param integer $limit
     * @param integer $offset
     * @return Columns_Data array
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#find()
     */
    public function find($where = array(), $limit = false, $offset = false)
    {
        if ($where instanceof Columns_PluginsData) {

            $where = $where->getExistingOptions();
            unset($where["Errors"]);
        }

        $data = $this->_ci->db->get_where('userColumnPlugins', $where, $limit, $offset)->result_array();
        $items = array();

        foreach ($data as $item) {
            $items[] = new Columns_PluginsData($item);
        }

        return $items;
    }

    /**
     * Devuelve todos los registros en base de datos
     *
     * @return Columns_PluginsData array
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#fetchAll()
     */
    public function fetchAll()
    {
        $data = $this->_ci->db->get('userColumnPlugins')->result_array();
        $items = array();

        foreach ($data as $item) {
            $items[] = new Columns_PluginsData($item);
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
        return $this->_ci->db->get('userColumnPlugins')->row()->itemNum;
    }

    /**
     * Crea o actualiza un registro en base de datos
     *
     * @param Columns_PluginsData $messages
     * @return boolean | Columns_PluginsData $column
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#save()
     */
    public function save(Columns_PluginsData $columnsPlugins = null)
    {
        if (!$columnsPlugins instanceof Columns_PluginsData) {

            throw new Exception('Incorrect Columns_PluginsData object');
        }

        if (!$columnsPlugins->isValid()) {

             throw new Exception('Incorrect data');
        }

        $data = array(
            'id' => $columnsPlugins->getId(),
            'idC' => $columnsPlugins->getIdC(),
            'idP' => $columnsPlugins->getIdP(),
        );

        if (null === ($id = $columnsPlugins->getId())) {

            unset($data['id']);
            $this->_ci->db->set($data);
            $this->_ci->db->insert('userColumnPlugins');

        } else {

            $this->_ci->db->where('id', $data['id']);
            unset($data['id']);
            $this->_ci->db->set($data);
            $this->_ci->db->update('userColumnPlugins');
        }

        return $this->_ci->db->insert_id();
    }

    /**
     * Elimina registros en base de datos
     *
     * @param array $where
     * @return boolean
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#delete()
     */
    public function delete( $where = array() )
    {
        if ($where instanceof Columns_PluginsData) {

            $where = array(
                "idC" => $where->getIdC(),
                "idP" => $where->getId()
            );
        }

        $this->_ci->db->where($where);
        $this->_ci->db->delete('userColumnPlugins');
        return true;
    }
}