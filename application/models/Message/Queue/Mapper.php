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
 * Clase encargada de mediar entre las base de datos y la aplicación en todo lo referente a mensajes programados
 */
class Message_Queue_Mapper extends Common_Mapper
{
    protected static $_instance;

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        self::$_instance =& $this;
    }

    /**
     * Devuelve una instacia del mapper
     * @return Message_Queue_Mapper
     */
    public static function get_instance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Message_Queue_Mapper();
        }

        return self::$_instance;
    }

    /**
     * Método para realizar busquedas de mensajes
     * TODO: prepararlo para querys personalizables
     *
     * @param array $where
     * @param array $whereIn
     * @param integer $limit
     * @param integer $offset
     * @return array
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#find()
     */
    public function find($where = array(), $limit = false, $offset = false)
    {

        $this->_ci->db->select('message_queue.*')
        			   ->from('message_queue')
                       ->where($where)
                       ->where("enabled","1")
                       ->join('userPlugins', 'message_queue.idUP = userPlugins.id')
                       ->order_by("message_queue.publishDate","asc")
                       ->order_by("message_queue.idU","asc")
                       ->limit($limit, $offset);

        return $this->_ci->db->get()->result_array();
    }

    /**
     * Devuelve el id del último mensaje de la identidad facilitada
     * @param integer $idUP
     * @return integer
     */
    public function getLastId($idUP)
    {
        $data = $this->_ci->db
                        ->select("id")
                        ->from('message_queue')
                        ->where(array("idUP" => $idUP))
                        ->order_by("message_queue.id","desc")
                        ->limit(1)->get()->row();

        if (isset($data->id)) {

            return $data->id;

        } else {

            return false;
        }
    }

    /**
     * Devuelve todos los mensajes en base de datos
     *
     * @return array
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#fetchAll()
     */
    public function fetchAll()
    {
        return $this->_ci->db->get('message_queue')->result_array();
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
        return $this->_ci->db->get('message_queue')->row()->itemNum;
    }

    /**
     * Crea un registro en base de datos
     *
     * @param array $data
     * @return integer $insert_id | Exception $e
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#save()
     */
    public function save(array $data = null)
    {
    	if (is_null($data)) {

    		throw new Exception('Incorrect data');
    	}

        if(isset($data["id"])) {

            $this->_ci->db->where("id",$data["id"]);
            $this->_ci->db->set($data);
            $this->_ci->db->update('message_queue');
            return $data["id"];

        } else {

            $this->_ci->db->set($data);
            $this->_ci->db->insert('message_queue');
            return $this->_ci->db->insert_id();
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
        $this->_ci->db->where($where);
        $this->_ci->db->delete('message_queue');

        return true;
    }
}