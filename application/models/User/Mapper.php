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
 * Clase encargada de mediar entre las base de datos y la aplicación en todo lo referente a usuarios
 */
class User_Mapper extends Common_Mapper
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
        $this->_ci->load->database();
    }

    /**
     * Devuelve una instacia del mapper
     * @return User_Mapper
     */
    public static function get_instance()
    {
        if (self::$instance == null) {
            self::$instance = new User_Mapper();
        }

        return self::$instance;
    }

    /**
     * Devuelve todos los registros en base de datos
     *
     * @return User_Data array
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#fetchAll()
     */
    public function fetchAll()
    {
        
        $data = $this->_ci->db->get('user')->result_array();
        $items = array();

        foreach ($data as $item) {
            $items[] = new User_Data($item);
        }

        return $items;
    }

    /**
     * Método para realizar busquedas en la tabla user
     *
     * @param array $where | User_Data $where
     * @param integer $limit
     * @param integer $offset
     * @param boolean $escape
     * @return User_Data array
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#find()
     */
    public function find($where = array(), $limit = false, $offset = false, $escape = true)
    {
        if ($where instanceof User_Data) {

            $where = $where->getExistingOptions();
            unset($where["Errors"]);
        }

        $this->_ci->db->select("*");
        $this->_ci->db->from("user");

        if (count($where) > 0) {

            if ($escape) {

                $this->_ci->db->where($where);

            } else {

                foreach ($where as $key => $val) {

                    $this->_ci->db->where($key, $val, false);
                }
            }
        }
 
        if ($limit and $offset) {

            $this->_ci->db->limit($limit, $offset);

        } else if ($limit) {

            $this->_ci->db->limit($limit);
        }

        $data = $this->_ci->db->get()->result_array();
        $items = array();

        foreach ($data as $item) {
            $items[] = new User_Data($item);
        }

        return $items;
    }

    /**
     * Devuelve el número de registros en base de datos
     *
     * @param array $where
     * @param boolean $escape
     * @return integer
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#totalRows()
     */
    public function totalRows($where = array(), $escape = true)
    {

        $this->_ci->db->from('user');
        if (count($where) > 0) {

            if ($escape) {

                $this->_ci->db->where($where);

            } else {

                foreach ($where as $key => $val) {

                    $this->_ci->db->where($key, $val, false);
                }
            }
        }

        //$results =
        return $this->_ci->db->get()->num_rows();
    }

    /**
     * Crea o actualiza un registro en base de datos
     *
     * @param User_Data $messages
     * @return bool | User_Data $column
     * (non-PHPdoc)
     * @see application/models/Common/Common_Mapper#save()
     */
    public function save(User_Data $user = null)
    {
        if (!$user instanceof User_Data) {

            throw new Exception('User_Data object required');
        }

        if (!$user->isValid()) {

             throw new Exception('User object is not valid');
        }

        $data = array(
            'id' => $user->getId(),
            'userName' => $user->getUserName(),
            'password' => $user->getPassword(),
            'email' => $user->getEmail(),
            'activated' => $user->getActivated(),
            'activationCode' => $user->getActivationCode(),
            'forgottenPasswordCode' => $user->getForgottenPasswordCode(),
            'deleteAccountCode' => $user->getDeleteAccountCode(),
            'lastVisit' => $user->getLastVisit(),
            'created' => $user->getCreated(),
        );

        if (null === ($id = $user->getId())) {

            unset($data['id']);
            $this->_ci->db->set($data);
            $this->_ci->db->insert('user');

        } else {

            $this->_ci->db->where('id', $data['id']);
            unset($data['id']);
            $this->_ci->db->set($data);
            $this->_ci->db->update('user');
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
    public function delete($where = array())
    {
        if ($where instanceof User_Data) {

            $where = array("id" => $where->getId());
        }

        $this->_ci->db->where($where);
        $this->_ci->db->delete('user');
        return true;
    }
}