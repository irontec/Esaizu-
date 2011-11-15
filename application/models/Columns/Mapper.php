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
 * Clase encargada de recoger y guardar los nombres y propiedades de las columnas del usuario
 */
class Columns_Mapper extends Common_Mapper
{
    public static $instance;

    /**
     * @return void
     */
    function __construct()
    {
        parent::__construct();
        self::$instance =& $this;
    }

    /**
     * Devuelve una instacia del mapper
     * @return Columns_Mapper
     */
    public static function get_instance()
    {
        if (self::$instance == null) {
            self::$instance = new Columns_Mapper();
        }

        return self::$instance;
    }

    /**
     * Método para realizar busquedas de columnas
     *
     * @param array $where
     * @param integer $limit
     * @param integer $offset
     * @return Columns_Data array
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#find()
     */
    public function find($where = array(), $limit = false, $offset = false)
    {
        $auth = Auth::get_instance();

        if ($where instanceof Columns_Data) {

            $newWhere = array();
            foreach ($where->getExistingOptions() as $key => $value) {

                if (!in_array($key, array("Errors","Plugins","Messages"))) {
                    $newWhere["userColumns.".$key] = $value;
                }
            }

            $where = $where->getExistingOptions();
            $where = $newWhere;
        }

        $data = $this->_ci->db->select("userColumns.id, userColumnPlugins.idP as ucpid, identificador, `order`,
                minimized,lastUserCheck,filters, userPlugins.idP, type, enabled")
              ->from('userColumns')
              ->where($where)
              ->join('userColumnPlugins', 'userColumns.id = userColumnPlugins.idc', 'left')
              ->join('userPlugins', 'userColumnPlugins.idP = userPlugins.id', 'left')
              ->order_by("`order` asc ,id asc, idP asc")
              ->limit($limit, $offset)
              ->get()->result_array();

        $items = array();
        
        foreach ($data as $item) {

            $cid = $item["id"];
            if (isset($items[$cid])) {

                $column = $items[$cid];

            } else {

                $column = new Columns_Data($item);
                $column->setId($cid)
                       ->setIdU($auth->getUserId())
                       ->setIdentificador($item["identificador"])
                       ->setminimized($item["minimized"])
                       ->setLastUserCheck($item["lastUserCheck"]);
            }

            if (isset($item["idP"]) and $item["idP"] != "") {

                $columnPluginData = new Columns_PluginsData();
                $columnPluginData->setId($item["ucpid"])
                                 ->setIdC($cid)
                                 ->setIdP($item["idP"]);
    
                $column->addPlugin($columnPluginData);
            }

            $items[$cid] = $column;
        }

        return $items;
    }

    /**
     * Devuelve todos las columnas en base de datos
     *
     * @return Columns_Data array
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#fetchAll()
     */
    public function fetchAll()
    {
        $data = $this->_ci->db->get('userColumns')->result_array();
        $items = array();

        foreach ($data as $item) {
            $items[] = new Columns_Data($item);
        }

        return $items;
    }

    /**
     * Devuelve el número de registros en base de datos
     *
     * @param array $where
     * @return integer
     */
    public function totalRows($where = array())
    {
        $this->_ci->db->select('count(*) as itemNum');
        return $this->_ci->db->where($where)->get('userColumns')->row()->itemNum;
    }

    /**
     * Crea o actualiza un registro en base de datos
     *
     * @param Columns_Data $column
     * @return bool | Columns_Data $column
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#save()
     */
    public function save(Columns_Data $column = null)
    {
        if (!$column instanceof Columns_Data) {

            throw new Exception('Not a valid Columns_Data object');
        }

        if (!$column->isValid()) {

             throw new Exception('Incorrect data');
        }

        $data = array(
            'id' => $column->getId(),
            'idU' => $column->getIdU(),
            'identificador' => $column->getIdentificador(),
            'minimized' => $column->getMinimized(),
            'lastUserCheck' => $column->getLastUserCheck(),
            'filters' => serialize($column->getFilters()),
            'order' => $column->getOrder(),
            'type' => is_null($column->getType()) ? "standard" : $column->getType()
        );

        $this->_ci->db->trans_start();

        if (null === ($id = $column->getId())) {

            unset($data['id']);
            $this->_ci->db->set($data);
            $this->_ci->db->insert('userColumns');

            $columnId =  $this->_ci->db->insert_id();;

        } else {

            $columnId =  $data['id'];
            $this->_ci->db->where('id', $data['id']);
            unset($data['id']);
            $this->_ci->db->set($data);
            $this->_ci->db->update('userColumns');
        }

        $columnPlugins = $column->getPlugins();
        $columnPluginMapper = Columns_PluginsMapper::get_instance();
        
        try {

             for ($i=0; $i < count($columnPlugins); $i++) {

                if ($columnPlugins[$i]->getIdC() === null or $columnPlugins[$i]->getIdC() == "") {

                    $columnPlugins[$i]->setIdC($columnId);

                    if (!$columnPlugins[$i]->isValid()) {

                        throw new Exception('Incorrect ColumnPlugin data');
                    }
                }

                $columnPluginMapper->save($columnPlugins[$i]);
             }

        } catch (Exception $e) {

            $column->setError("form", $e->getMessage());
            $this->_ci->db->trans_rollback();
            return $column;
        }

        if ($this->_ci->db->trans_status() === FALSE) {

            $this->_ci->lang->load("errormessages");
            $column->setError("form", $this->_ci->lang->line("sqlTransactionError"));
            $this->_ci->db->trans_rollback();
            return $column;
        }

        $this->_ci->db->trans_complete();
        return $columnId;
    }

    /**
     * Elimina registros en base de datos
     *
     * @param Columns_Data $column
     * @return bool
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#delete()
     */
    public function delete(Columns_Data $column = null)
    {
        if($column == null) {

            throw new Exception("Invalid Columns_Data object");
        }

        $where = array(
            "id" => $column->getId()
        );

        $cPluginMapper = Columns_PluginsMapper::get_instance();

        foreach ($column->getPlugins() as $plugin) {

            $cPluginMapper->delete($plugin);
        }

        $this->_ci->db->where($where);
        $this->_ci->db->delete('userColumns');
        return true;
    }
}