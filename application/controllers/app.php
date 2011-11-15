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
class App extends CI_Controller
{
	private $messagesPerColumn = 20;

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Parametro que corresponde al id del primer mensaje mostrado en una columna dada.
	 * Se usa para actualizar por ajax el contenido de las columnas.
	 * @param integer $from
	 *
     * Parametro que corresponde al id del último mensaje mostrado en una columna dada.
     * Se usa para actualizar por ajax el contenido de las columnas al pulsar en ver más.
	 * @param integer $since
	 */
	public function index($column = null, $from = 0, $since = 0)
	{
	    $auth = Auth::get_instance();
	    $accounts = $auth->getEnabledAccounts();

	    $i=0;

	    $myAccounts = array();
	    $renders = array();
	    $pluginClassNames = array();

	    foreach ($accounts as $account) {

	        $accountId = $account->getId();
	        $myAccounts[$accountId] = $account;

	        $pluginClassNames[$accountId] = $account->getClassName();
            $renderClassName = "Plugins_".$account->getClassName()."_Render";
            $renders[$accountId] = new $renderClassName();
	    }

	    $columns = array();
        $columnMapper = new Columns_Mapper();

        if (! is_null($column)) {

            $where = "userColumns.idu = ".$auth->getUserId()." and enabled = 1 and userColumns.id = ".$column;

        } else {

            $where = "userColumns.idu = ".$auth->getUserId()." and (enabled = 1 or type='search')";
        }

        $userColumns = $columnMapper->find($where);

        foreach ($userColumns as $userColumn) {

            $timestamp = isset($_GET["timestamp"]) ? $_GET["timestamp"] : null;
            $userColumn->loadColumnMessages($this->messagesPerColumn, $from, $since, $timestamp);
            $sql = $this->db->last_query();

            if ($userColumn->getMinimized() == 0) {

                $userColumn->setLastUserCheck(time());
                $columnMapper->save($userColumn);
            }
        }

	    $data = array(
	       "renders" => $renders,
	       "pluginClassNames" => $pluginClassNames,
	       "columns" => $userColumns,
	       "accountNum" => count($userColumns) == 0 ? count($auth->getEnabledAccounts()) : null,
	       "messagesPerColumn" => $this->messagesPerColumn,
	       "+js" => array("application.main.js")
	    );

	    if (! is_null($column)) { //ajax request

	        $targetColumn = array_shift($data["columns"]);
	        $messages = $targetColumn->getMessages();

	        $html = "";
	        $kont = 0;
            foreach ($messages as $msg) {

                $pluginId =  $msg->getIdUP();
                $html .= $renders[$pluginId]->messageBox($msg, true);
                $kont++;
            }

            $data = array(
                "column" => $column,
                "type" => isset($_REQUEST["type"]) ? $_REQUEST["type"] : "",
                "identifier" => isset($_REQUEST["identifier"]) ? $_REQUEST["identifier"] : "",
                "messagesPerColumn" => $this->messagesPerColumn,
                "messageNum" => $kont,
                "html" => $html,
            );

            echo json_encode($data);

	    } else {

	        $this->load->masterview("app", $data);
	    }
	}

	public function checkUnread () {

        $response = array();

	    if (count($_POST["cIds"]) > 0) {

	        $auth = Auth::get_instance();
 	        $columnMapper = new Columns_Mapper();

	        foreach($_POST["cIds"] as $id) {

                $where = array(
                    "userColumns.idu" => $auth->getUserId(),
                    "enabled" => 1,
                    "userColumns.id" => $id
                );

                $userColumn = array_shift($columnMapper->find($where));
	        }

	        if (! $userColumn instanceof Columns_Data) {

	           continue;
	        }

            $lastCheck = $userColumn->getLastUserCheck();

            $userColumn->loadColumnMessages(20 , 0, 0, $lastCheck);
            $response[$id] = count($userColumn->getMessages()) ;
	    }

        echo json_encode($response);
	}

	/***
	 * Minimiza columnas
	 */
	public function minimize($idC)
	{
        $auth = Auth::get_instance();
        $where = array("userColumns.idu" => $auth->getUserId(), "userColumns.id" => $idC);

        $columnMapper = new Columns_Mapper();
        $targetColumn = $columnMapper->find($where);

        if (count($targetColumn) == 1) {

            $column = array_shift($targetColumn);
            $column->setMinimized(1);
            $columnMapper->save($column);
        }

        redirect("","refresh");
	}

	/***
	 * Restaura columnas minimizadas
	 */
	public function restore($idC)
	{
        $auth = Auth::get_instance();
        $where = array("userColumns.idu" => $auth->getUserId(), "userColumns.id" => $idC);

        $columnMapper = new Columns_Mapper();
        $targetColumn = $columnMapper->find($where);

        if(count($targetColumn) == 1) {

            $column = array_shift($targetColumn);
            $column->setMinimized(0);
            $columnMapper->save($column);
        }

        redirect("","refresh");
	}

	private function testing() {

	   $accountId = 10;
	   $auth = Auth::get_instance();
	   $account = $auth->getAccount($accountId);

	   $cron = new Plugins_Facebook_Cron();
	   $cron->connect($account);
	   $cron->update();
	}
}
