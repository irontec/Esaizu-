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
class Columns extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('pagination');
    }

    public function index(Columns_Data $column = null)
    {
        $auth = Auth::get_instance();
        $myAccounts = $auth->getEnabledAccounts();

        if ($column !== null) {

            $data = array(
                "accounts" => $myAccounts,
                "obj" => $column,
                "+js" => array("application.columns.js")
            );

        } else {

            $data = array(
                "accounts" => $myAccounts,
                "obj" => new Columns_Data(),
                "+js" => array("application.columns.js")
            );
        }

        $this->load->masterview("columns/columns", $data);
    }

    public function listar()
    {
        $auth = Auth::get_instance();
        $columnMapper = new Columns_Mapper();
        $userColumns = $columnMapper->find(array("userColumns.idu" => $auth->getUserId()));

        $data = array("columns" => $userColumns);

        $this->load->masterview("columns/listar", $data);
    }

    public function add()
    {
        if (count($_POST) == 0) {

            $this->index();
            return;
        }

        $this->lang->load("errormessages");

        $column = new Columns_Data();

        if ($_POST["type"] == "search") {

            if ($_POST["text"] == "" and $_POST["hashtags"] == "") {

                
                $column->setError("search", $this->lang->line("searchString"));
            }

        } else {

            if (!isset($_POST["plugins"]) or count($_POST["plugins"]) == 0) {

                $column->setError("plugins", $this->lang->line("selectPlugin"));
            }
        }

        $columnMapper = Columns_Mapper::get_instance();
        $auth = Auth::get_instance();

        $column->setIdentificador($_POST["identificador"]);
        $column->setIdU($auth->getUserId());

        $tmp = $columnMapper->find($column);

        if (count($tmp) > 0) {

            $column->setError("identificador", $this->lang->line("duplicatedIdentitier"));
            $this->index($column);
            return;
        }

        $column->setMinimized("0");

        /****
         * Asignar filtros y plugins
         */
        if ($_POST["type"] == "standard") {

            $column->setType("standard");
            $filters = array();

            if (isset($_POST["filterByAuthor"]) and $_POST["filterByAuthor"] != "") {

                $filters["autor"] = $_POST["filterByAuthor"];
            }

            if (isset($_POST["filterByWord"]) and $_POST["filterByWord"] != "") {

                $filters["content"] = $_POST["filterByWord"];
            }
            
            $plugins = array();
            foreach ($_POST["plugins"] as $plugin) {

                $item = new Columns_PluginsData();
                $item->setIdP($plugin);
                $plugins[] = $item;
            }

            $column->setPlugins($plugins);

        } else if ($_POST["type"] == "search") {

            $column->setType("search");
            $filters = array(
                "text" => $_POST["text"],
                "hashtags" => $_POST["hashtags"],
                "language" => $_POST["language"],
                "user" => $_POST["username"]
            );
            
            if( empty($_POST["text"]) and empty($_POST["hashtags"]) and empty($_POST["language"]) and empty($_POST["username"])) {

                $this->index($column);
                return;
            }
        }

        $column->setFilters($filters);

        /****
         * Guardar
         */
        if (! is_numeric($columnMapper->save($column)) ) {

            $column->setError("form", $this->lang->line("unknownError"));
            $this->index($resp);
            return;
        }

        redirect("", "refresh");
    }

    public function edit($id, $error = false)
    {
        $auth = Auth::get_instance();
        $myAccounts = $auth->getEnabledAccounts();

        //fetch column to edit
        $column = new Columns_Data();
        $column->setId($id);
        $column->setIdU($auth->getUserId());

        $columnMapper = new Columns_Mapper();

        $myColumn = array_shift($columnMapper->find($column));

        if (!$myColumn instanceof Columns_Data) {

            throw new Exception("Column not found");
        }

        $activeUserPlugins = array();
        foreach ($myColumn->getPlugins() as $plugin) {

            $activeUserPlugins[] = $plugin->getId();
        }

        if ($error !== false) {

            $myColumn->setError("form", $error);
        }

        $data = array(
            "obj" => $myColumn,
            "accounts" => $myAccounts,
            "activeUserPlugins" => $activeUserPlugins,
            "+js" => array("application.columns.js")
        );

        $this->load->masterview("columns/editar", $data);
    }

    public function update($id)
    {
        if ($_POST["type"] == "search") {

            if ($_POST["text"] == "" and $_POST["hashtags"] == "" and $_POST["username"] == "") {

                 $this->lang->load("errormessages");
                 $this->edit($id, $this->lang->line("searchString"));
                 return;
            }

        } else {

            if (!isset($_POST["plugins"]) or count($_POST["plugins"]) == 0) {

                $this->lang->load("errormessages");
                $this->edit($id, $this->lang->line("selectPlugin"));
                return;
            }
        }

        if (!isset($_POST["identificador"]) or strlen($_POST["identificador"]) < 5) {

            $this->edit($id, "El identificador requiere una longitud mínima de 5 caracteres");
            return;
        }

        $auth = Auth::get_instance();
        $columnMapper = Columns_Mapper::get_instance();

        //fetch column to edit
        $column = new Columns_Data();
        $column->setId($id);
        $column->setIdU($auth->getUserId());

        $myColumn = array_shift($columnMapper->find($column));

        if (!$myColumn instanceof Columns_Data) {

            throw new Exception("Column not found");
        }

        /**************** Comprobar conflictos en el nombre de la columna ***********************/
        if ($myColumn->getIdentificador() != $_POST["identificador"] ) {

            $matchNum = $columnMapper->totalRows(
                array(
                    "idU" => $auth->getUserId(),
                    "identificador" => $_POST["identificador"],
                    "id !=" => $id
                )
            );

            if ($matchNum > 0) {

                $this->edit($id, "Ya existe una columna con ese nombre");
                return;
            }

        }
        /***************************************/

        $myColumn->setIdentificador($_POST["identificador"]);

        if ($_POST["type"] == "standard") {

            $myColumn->replacePlugins($_POST["plugins"]);

            $filters = array();

            if (isset($_POST["filterByAuthor"]) and $_POST["filterByAuthor"] != "") {

                $filters["autor"] = $_POST["filterByAuthor"];
            }

            if (isset($_POST["filterByWord"]) and $_POST["filterByWord"] != "") {

                $filters["content"] = $_POST["filterByWord"];
            }

            $myColumn->setFilters($filters);

        } else {
        
            $myColumn->replacePlugins(array());
            $filters = array(
                "text" => $_POST["text"],
                "hashtags" => str_replace("#","",$_POST["hashtags"]),
                "language" => $_POST["language"],
                "user" => $_POST["username"]
            );
            
            $myColumn->setFilters($filters);
        }

        $myColumn->setType($_POST["type"]);
        $columnMapper->save($myColumn);

        redirect("", "redirect");
    }

    public function delete($id)
    {
        $auth = Auth::get_instance();
        $myAccounts = $auth->getEnabledAccounts();

        //fetch column to edit
        $column = new Columns_Data();
        $column->setId($id);
        $column->setIdU($auth->getUserId());

        $columnMapper = new Columns_Mapper();
        $myColumn = array_shift($columnMapper->find($column));

        if (!$myColumn instanceof Columns_Data) {

            throw new Exception("Column not found");
        }

        $cMapper = Columns_Mapper::get_instance();
        $cMapper->delete($myColumn);
        redirect("columns/listar", "redirect");
    }

    public function order()
    {
        $auth = Auth::get_instance();

        $column = new Columns_Data();
        $column->setIdU($auth->getUserId());

        $columnMapper = new Columns_Mapper();
        $myColumns = $columnMapper->find($column);

        $data = array(
            "columns" => $myColumns,
            "+js" => array("application.ordercolumns.js"),
            "+css" => array("ui.css")
        );

        $this->load->masterview("columns/ordenar", $data);
    }
    
    public function reorder()
    {

        if (isset($_POST)) {
        
            $columnMapper = Columns_Mapper::get_instance();

            foreach ($_POST["identifier"] as $key => $val) {

                $column = new Columns_Data();
                $column->setId($val);

                $column = array_shift($columnMapper->find($column));

                if($column instanceof Columns_Data) {

                    $column->setOrder($key+1);
                    $columnMapper->save($column);
                }
            }
        }

        redirect("app");
    }
}