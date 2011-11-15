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
 *
 * Clase encargada de mediar entre las base de datos y la aplicación en
 * todo lo referente a cuentas de usuario
 */
class User_Accounts_Mapper extends Common_mapper
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
     * Devuelve una instancia del mapper
     * @return User_Accounts_Mapper
     */
    public static function get_instance()
    {
        if (self::$instance == null) {
            self::$instance = new User_Accounts_Mapper();
        }

        return self::$instance;
    }

    /**
     * Método para realizar busquedas en la tabla userPlugins
     *
     * @param array $where | User_accounts_Data $where
     * @param integer $limit
     * @param integer $offset
     * @param array $orderBy
     * @return User_accounts_Data array
     */
    /* (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#find()
     */
    public function find($where = array(), $limit = false, $offset = false, $orderBy = array())
    {
        if ($where instanceof User_accounts_Data) {

            $where = $where->getExistingOptions();
            unset($where["Errors"]);
            
            if (isset($where["Metadata"]) and is_array($where["Metadata"])) {

                $where["Metadata"] = serialize($where["Metadata"]);
            }
        }

        $where["activated"] = 1;

        /**
         * Avoid ambiguous fields in sql where
         */
        if (isset($where["Id"])) {

            $where["userPlugins.Id"] = $where["Id"];
            unset($where["Id"]);
        }

        if (isset($where["idu"])) {

            $cache = $this->_ci->cache->get(md5('User_Accounts_Mapper_'.$where["idu"]));

            if($cache !== false) {

                return $cache;
            }
        }

        $this->_ci->db->select("userPlugins.id, idU, idP, alias, enabled,
        name, className, auth, metadata, lastUpdate, updateFrecuency")
        ->from('userPlugins')
        ->join('plugins', 'plugins.id = userPlugins.idP')
        ->where($where);

        foreach ($orderBy as $key => $val) {

            $this->_ci->db->order_by($key, $val);
        }

        $data = $this->_ci->db->get()->result_array();
        $items = array();

        foreach ($data as $item) {

            $items[] = new User_Accounts_Data($item);
        }

        if (isset($where["idu"])) {

            $cache = $this->_ci->cache->save(md5('User_Accounts_Mapper_'.$where["idu"]), $items, $this->_ci->config->item("cache_lifetime"));
        }

        return $items;
    }

    /**
     * Devuelve todos los registros en base de datos
     *
     * @return User_Accounts_Data array
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#fetchAll()
     */
    public function fetchAll()
    {
        $data = $this->_ci->db->select("userPlugins.id, idU, idP, alias, enabled, name, className, auth, lastUpdate")
        ->from('userPlugins')
        ->join('plugins', 'plugins.id = userPlugins.idP')->get()
        ->result_array();

        $items = array();

        foreach ($data as $item) {
            $items[] = new User_Accounts_Data($item);
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
        return $this->_ci->db->get('userPlugins')->row()->itemNum;
    }

    /**
     * Crea o actualiza un registro en base de datos
     *
     * @param User_Accounts_Data $messages
     * @return bool | User_Accounts_Data $column
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#save()
     */
    public function save(User_Accounts_Data $userAccounts = null)
    {
        if (!$userAccounts instanceof User_Accounts_Data) {

            throw new Exception('User_Accounts_Data object required');
        }

        if (!$userAccounts->isValid()) {

             throw new Exception('Incorrect data');
        }

        if($userAccounts->getIdU()) {
        
            $this->_ci->cache->delete(md5('User_Accounts_Mapper_'.$userAccounts->getIdU()));
        }
        
        $data = array(
            'id' => $userAccounts->getId(),
            'idU' => $userAccounts->getIdU(),
            'idP' => $userAccounts->getIdP(),
            'alias' => $userAccounts->getAlias(),
            'enabled' => $userAccounts->getEnabled(),
            'auth' => $userAccounts->getAuth(),
            'metadata' => serialize($userAccounts->getMetadata()),
            'lastUpdate' => $userAccounts->getlastUpdate(),
        );

        if (null === ($id = $userAccounts->getId())) {

            unset($data['id']);
            $this->_ci->db->set($data);
            $this->_ci->db->insert('userPlugins');

        } else {

            $this->_ci->db->where('id', $data['id']);
            unset($data['id']);
            $this->_ci->db->set($data);
            $this->_ci->db->update('userPlugins');
        }

        return $this->_ci->db->insert_id();
    }
    
    /**
     * Actualiza un registro en base de datos con los datos recibidos en un array
     *
     * @param integer $id
     * @param Array $data
     * @return boolean
     *
     * @param $id
     * @param array $data
     * @return unknown_type
     */
    public function updateFromArray($id, array $data)
    {
        if (isset($data["metadata"]) and is_array($data["metadata"])) {
        
            $data["metadata"] = serialize($data["metadata"]);
        }

        $this->_ci->db->where('id', $id);
        $this->_ci->db->set($data);

        return $this->_ci->db->update('userPlugins');
    }

    /**
     * Elimina registros en base de datos
     *
     * @param array $where | User_Accounts_Data $where
     * @return bool
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#delete()
     */
    public function delete( $where = array() )
    {
        if ($where instanceof User_Accounts_Data) {

            $where = $where->getOptions();
            unset($where["Errors"]);
            unset($where["Name"]);
            unset($where["ClassName"]);
            unset($where["Auth"]);
            unset($where["UpdateFrecuency"]);

            if (isset($where["Metadata"]) and is_array($where["Metadata"])) {

                $where["Metadata"] = serialize($where["Metadata"]);
            }
        }

        if(isset($where["idu"])) {

            $this->_ci->cache->delete(md5('User_Accounts_Mapper_'.$where["idu"]));
        }

        $this->_ci->db->where($where);
        $this->_ci->db->delete('userPlugins');
        return true;
    }
}