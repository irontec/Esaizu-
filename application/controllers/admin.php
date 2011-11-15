<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
class Admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('pagination');
    }

    public function index($currentPage = 0)
    {
        $userMapper = User_Mapper::get_instance();

        $itemsPerPage = 20;
        $where = array("id !=" => "1");

        $config = array(
            'base_url' => site_url("admin/index/"),
            'total_rows' => $userMapper->totalRows($where, false),
            'per_page' => $itemsPerPage,
        );

        $this->pagination->initialize($config);

        $items = $userMapper->find($where, $itemsPerPage, ( $itemsPerPage*$currentPage ), false);
        $this->load->masterview("admin", array("items" => $items));
    }

    public function accept($idu)
    {
        $userMapper = User_Mapper::get_instance();

        $where = array("id =" => $idu);
        $items = $userMapper->find($where);
    
        if (count($items) == 1) {

            $user = $items[0];
            $user->setActivated("1");

            if ($user->isValid()) {
            
                $userMapper->save($user);
            }
        }
        
        redirect("admin", "refresh");
    }

    public function deny($idu)
    {

        $userMapper = User_Mapper::get_instance();

        $where = array("id =" => $idu);
        $items = $userMapper->find($where);

        if (count($items) == 1) {

            $user = $items[0];
            $user->setActivated("0");

            if ($user->isValid()) {
            
                $userMapper->save($user);
            }
        }

        redirect("admin", "refresh");
    }

    public function plugins() {

        $pluginMapper = Plugins_Mapper::get_instance();
        $plugins = $pluginMapper->fetchAll();

        $this->load->masterview("admin", array("plugins" => $plugins));
    }

    public function edit($id = null, $errors = array()) {

        if (is_null($id)) {

            Throw new Exception("Plugin not found");
        }

        $pluginMapper = Plugins_Mapper::get_instance();
        $plugin = new Plugins_Data();
        $plugin->setId($id);

        $currentPlugin = array_shift($pluginMapper->find($plugin));

        if (! $currentPlugin instanceof Plugins_Data) {

            Throw new Exception("Plugin not found");
        }

        $data = array(
            "plugin" => $currentPlugin,
            "errors" => $errors
        );

        $this->load->masterview("admin", $data);
    }

    public function update ($id = null) {

        if (is_null($id)) {

            Throw new Exception("Plugin not found");
        }

        $pluginMapper = Plugins_Mapper::get_instance();
        $plugin = new Plugins_Data();
        $plugin->setId($id);

        $currentPlugin = array_shift($pluginMapper->find($plugin));

        if (! $currentPlugin instanceof Plugins_Data) {

            Throw new Exception("Plugin not found");
        }

        $currentPlugin->setActivated($_POST["activated"]);
        $currentPlugin->setUpdateFrecuency($_POST["updateFrecuency"]);

        if ($currentPlugin->isValid()) {

            $pluginMapper->save($currentPlugin);
            redirect("admin/plugins/", "refresh");
        
        } else {

            $this->edit($id, $currentPlugin->getErrors());
        }
    }
}
