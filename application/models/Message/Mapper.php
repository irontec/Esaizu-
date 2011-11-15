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
 * Clase encargada de mediar entre las base de datos y la aplicación en todo lo referente a mensajes
 *
 * @author Mikel Madariaga
 */
class Message_Mapper extends Common_Mapper
{
    protected static $_instance;

    /**
     * @return void
     */
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        self::$_instance =& $this;
    }

    /**
     * Devuelve una instacia del mapper
     * @return Message_Mapper
     */
    public static function get_instance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Message_Mapper();
        }

        return self::$_instance;
    }

    /**
     * Método para realizar busquedas de mensajes
     * TODO: prepararlo para querys personalizables
     *
     * @param array $where | Message_Data $where
     * @param array $whereIn
     * @param integer $limit
     * @param integer $offset
     * @return Message_Data array
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#find()
     */
    public function find($where = array(), $whereIn = array(), $limit = false, $offset = false)
    {
        if ($where instanceof Message_Data) {

            $newWhere = array();
            foreach ($where->getExistingOptions() as $key => $val) {

                if ($key == "Data" and is_array($val)) {

                    $newWhere["messages.".$key] = serialize($val);

                } else if ($key != "Errors") {

                    $newWhere["messages.".$key] = $val;
                }
            }

            $where = $newWhere;
        }

        if (isset($where["messages.idu"])) {

            $filename = md5('User_Messages_Mapper_'.implode("_",$where).implode("_",$whereIn["messages.idUP"]));
            $cache = $this->_ci->cache->get($filename);

            if($cache !== false) {

                return $cache;
            }
        }

        $this->_ci->db->select("messages.*")
                       ->from('messages');
                       
        foreach($where as $key => $value) {
        
            if(is_numeric($key)) {
            
                $this->_ci->db->where($value, false, false);
            
            } else {
            
                $this->_ci->db->where($key, $value);
            }
                
        }

        $this->_ci->db->where("activated","1")
                       ->where("enabled","1")
                       ->join('userPlugins', 'messages.idUP = userPlugins.id')
                        ->join('plugins', 'userPlugins.idP = plugins.id')
                       ->order_by("publishDate","desc")
                       ->limit($limit, $offset);

        if (count($whereIn) > 0) {

            foreach($whereIn as $key => $values) {

                if (is_array($values) and count($values) > 0) {
                
                    $this->_ci->db->where_in($key,$values);
                }
            }
        }

        $data = $this->_ci->db->get()->result_array();

        $items = array();

        foreach ($data as $item) {

            $items[] = new Message_Data($item);
        }

        if (isset($where["messages.idu"])) {

            $filename = md5('User_Messages_Mapper_'.implode("_",$where).implode("_",$whereIn["messages.idUP"]));
            $this->_ci->cache->save($filename, $items, $this->_ci->config->item("cache_lifetime"));
        }

        return $items;
    }

    /**
     * @param array $where
     * @param integer $limit
     * @param integer $offset
     * @return Message_Data array
     */
    public function customQuery($where = array(), $limit = false, $offset = false)
    {
        $this->_ci->db->select("messages.*")
                       ->from('messages');

        foreach($where as $key => $item) {

            switch ($key) {

                case "where" :

                    foreach ($item as $subkey => $value) {

                        if(is_numeric($subkey) and $value != "") {

                            $this->_ci->db->where($value, false, false);

                        } else if ($subkey != "" and $value != "") {

                            $this->_ci->db->where($subkey, $value);
                        }
                    }

                    break;

                case "where_in":

                    foreach($item as $subkey => $values) {

                        if (is_array($values) and count($values) > 0) {

                            $this->_ci->db->where_in($subkey,$values);
                        }
                    }

                    break;

                case "where_not_in":

                    foreach($item as $subkey => $values) {

                        if ($subkey != "" and is_array($values) and count($values) > 0) {
                        
                            $this->_ci->db->where_not_in($subkey,$values);
                        }
                            
                    }

                    break;

                case "like":

                    foreach($item as $subkey => $values) {

                        $this->_ci->db->like($subkey,$values);
                    }

                    break;
            }
        }

        $this->_ci->db->where("activated","1")
                       ->where("enabled","1")
                       ->join('userPlugins', 'messages.idUP = userPlugins.id')
                        ->join('plugins', 'userPlugins.idP = plugins.id')
                       ->order_by("publishDate","desc")
                       ->limit($limit, $offset);

        $data = $this->_ci->db->get()->result_array();

        //echo $this->_ci->db->last_query()."<br />";
        $items = array();

        foreach ($data as $item) {

            $items[] = new Message_Data($item);
        }

        return $items;
    }

    /**
     * Devuelve el id del último mensaje de la identidad facilitada
     * @param integer $idUP
     * @param integer $limit
     * @param integer $offset
     * @return integer | boolean
     */
    public function getLastId($idUP, $limit = 1, $offset = 0)
    {
        $data = $this->_ci->db
                        ->select("id")
                        ->from('messages')
                        ->where(array("idUP" => $idUP))
                        ->order_by("messages.id","desc")
                        ->limit($limit, $offset)->get()->result();

        if ($limit == 1) {

            $data = $data[0];
            
            if (isset($data->id)) {
    
                return $data->id;
    
            } else {
    
                return false;
            }

        } else {

            $tmp = array();

            foreach ($data as $item) {

                $tmp[] = $item->id;
            }
            
            return $tmp;
        }
                        

    }

    /**
     * Devuelve todos los mensajes en base de datos
     *
     * @return Message_Data array
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#fetchAll()
     */
    public function fetchAll()
    {
        $data = $this->_ci->db->get('messages')->result_array();
        $items = array();

        foreach ($data as $item) {
            $items[] = new Message_Data($item);
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
        return $this->_ci->db->get('messages')->row()->itemNum;
    }

    /**
     * Crea o actualiza un registro en base de datos
     *
     * @param Message_Data $messages
     * @return integer $insert_id | Exception $e
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#save()
     */
    public function save(Message_Data $messages = null)
    {
        if (!$messages instanceof Message_Data) {

            throw new Exception('Message_Data object required');
        }

        if (!$messages->isValid()) {

             throw new Exception('Incorrect data');
        }

        $data = array(
            'id' => $messages->getId(),
            'remoteId' => $messages->getRemoteId(),
            'idU' => $messages->getIdU(),
            'idUP' => $messages->getIdUP(),
            'title' => $messages->getTitle(),
            'data' => serialize($messages->getData()),
            'publishDate' => $messages->getPublishDate(),
            'link' => $messages->getLink(),
            'owner' => $messages->getOwner(),
        );

        if (null === ($id = $messages->getId())) {

            unset($data['id']);
            $this->_ci->db->set($data);
            $this->_ci->db->insert('messages');

        } else {

            $id = $data['id'];
            $this->_ci->db->where('id', $data['id']);
            unset($data['id']);
            $this->_ci->db->set($data);
            $this->_ci->db->update('messages');

            return $id;
        }

        return $this->_ci->db->insert_id();
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
        if ($where instanceof Message_Data) {

            if ($where->getId() != "") {

                $where = array(

                    "id" => $where->getId()
                );

            } else {

                $where = $where->getOptions();
                $where["data"] = serialize($where["data"]);
                unset($where["Errors"]);
            }

        }

        $this->_ci->db->where($where);
        $this->_ci->db->delete('messages');
        return true;
    }
}