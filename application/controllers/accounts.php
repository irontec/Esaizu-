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
class Accounts extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $auth = Auth::get_instance();
        $myAccounts = $auth->getAccounts();

        $data = array(
            "accounts" => $myAccounts,
            "+js" => array("application.myaccounts.js")
        );

        $this->load->masterview("accounts/myaccounts", $data);
    }

    public function newaccount(User_Accounts_Data $userAccount = null, $plugin = '')
    {
        $auth = Auth::get_instance();
        $pluginMapper = Plugins_Mapper::get_instance();
        $plugins = $pluginMapper->find(array("activated" => "1"), false, false, array("name" => "asc"));

        $availablePlugins = array();
        foreach ($plugins as $plugin) {

            $className =  "Plugins_".$plugin->getClassName()."_Render";

            $render = new $className();

            $availablePlugins[] = array(
                "id"   => $plugin->getId(),
                "name" => $plugin->getName(),
                "template" => $render->getNewAccountFormView(),
            );
        }

        $data = array("plugins" => $availablePlugins);

        if ($userAccount instanceof User_Accounts_Data) {

            $data["obj"] = $userAccount;

        } else {

            $data["obj"] = new User_Accounts_Data();
        }

        $columnMapper = new Columns_Mapper();
        $userColumns = $columnMapper->find(array("userColumns.idu" => $auth->getUserId()));
        $data["userColumns"] = $userColumns;

        $data["+js"] = array("application.accounts.js");
        $this->load->masterview("accounts/new", $data);
    }

    public function add()
    {
        if(count($_POST) == 0) {

            redirect("accounts/newaccount","refresh");
            return;
        }

        $this->lang->load("errormessages");
        
        $auth = Auth::get_instance();
        $userAccount = new User_Accounts_Data();
        $userAccountMapper = new User_Accounts_Mapper();

        $userAccount->setAlias($_POST["alias"]);
        $userAccount->setIdU($auth->getUserId());

        $tmp = $userAccountMapper->find($userAccount);

        if (count($tmp) > 0) {

            $userAccount->setError("alias", $this->lang->line("duplicatedAlias"));
        }

        $userAccount->setIdP($_POST["plugin"])
        ->setIdU($auth->getUserId());

        if (!$userAccount->isValid()) {

            $this->newaccount($userAccount);
            return;
        }

        $plugin = new Plugins_Data();
        $plugin->setId($userAccount->getIdP());
        $plugin->setActivated(1);

        if (!$plugin->isValid()) {

            $userAccount->setError("form", $this->lang->line("invalidPlugin"));
            $this->newaccount($userAccount);
            return;
        }

        $pluginMapper = Plugins_Mapper::get_instance();
        $plugins = $pluginMapper->find($plugin);

        if (count($plugins) !== 1) {

            $userAccount->setError("form", $this->lang->line("pluginNotFound"));
            $this->newaccount($userAccount);
            return;
        }

        $plugin = $plugins[0];

        $pluginClassName = "Plugins_".$plugin->getClassName()."_Auth";
        $pluginAuth = new $pluginClassName();

        if ($pluginAuth->hasRemoteAuth() === true) {

        	if (session_id() == "") {

        		session_start();
        	}

            $_SESSION["newAccount"] = array(
                "pluginClassname" => $pluginClassName,
                "postData" => $_POST,
            );

            $pluginAuth->remoteAuth();

        } else {

            $pluginAuth->setData($_POST);

            if (true === ( $resp = $pluginAuth->validate($userAccount) )) {

            	if ($pluginAuth->getData() != "") {

            		$userAccount->setAuth($pluginAuth->getData());
            	}

                $userAccount->setEnabled("1");

                $userAccountMapper = User_Accounts_Mapper::get_instance();
                $id = $userAccountMapper->save($userAccount);

                if(is_numeric($id)) {

                    $userAccount->setId($id);
                }

                if (is_numeric($_POST["column"])) {

                    $columnPlugin = new Columns_PluginsData();
                    $columnPluginMapper = new Columns_PluginsMapper();

                    $columnPlugin->setIdC($_POST["column"]);
                    $columnPlugin->setIdP($userAccount->getId());

                    $id = $columnPluginMapper->save($columnPlugin);

                    if(is_numeric($id)) {

                        $columnPlugin->setId($id);
                    }

                    $this->addToColumn($_POST["column"], $columnPlugin);

                } else if ($_POST["column"] == "new") {

                    $column = new Columns_Data();
                    $columnMapper = new Columns_Mapper();

                    $column->setIdU($auth->getUserId());
                    $column->setIdentificador($userAccount->getAlias());
                    
                    /***
                     * Check if the column name is taken
                     */
                    $i = 1;
                    while (true) {

                        $tmp = $columnMapper->find($column);

                        if (count($tmp) == 0) {

                            break;

                        } else {

                            $column->setIdentificador($userAccount->getAlias()."_".$i);
                        }
                    }

                    $column->setMinimized(0);
                    $column->setType("standard");

                    $id = $columnMapper->save($column);

                    if(is_numeric($id)) {

                        $column->setId($id);
                    }

                    $columnPlugin = new Columns_PluginsData();
                    $columnPluginMapper = new Columns_PluginsMapper();
                    $columnPlugin->setIdC($column->getId());
                    $columnPlugin->setIdP($userAccount->getId());

                    $id = $columnPluginMapper->save($columnPlugin);

                    if (is_numeric($id)) {

                        $columnPlugin->setId($id);
                    }

                    $column->addPlugin($columnPlugin);

                    /***
                     * Hacer un fetch del contenido sin esperar a cron
                     */
                    $cronClassName = "Plugins_".$plugin->getClassName()."_Cron";
                    $cron = new $cronClassName();

                    $cron->connect($userAccount);
                    $cron->update();
                }

                redirect("accounts", "refresh");

            } else {

                $userAccount->setError("form",$resp);
                $this->newaccount($userAccount);
                return;
            }
        }
    }

    private function addToColumn($columnId, Columns_PluginsData $account)
    {
        $auth = Auth::get_instance();
        $columnMapper = Columns_Mapper::get_instance();

        $column = array_shift($columnMapper->find(
            array(
                "userColumns.idU" => $auth->getUserId(),
                "userColumns.id" => $columnId
            )
        ));

        if ($column instanceof Columns_Data) {
        
            $column->addPlugin($account);
            $columnMapper->save($column);
        }
    }

    /**
     * This method fetch remote server auth token
     */
    public function auth ()
    {
        if (session_id() == "") {
            session_start();
        }

        if (!isset($_SESSION["newAccount"])) {

            show_error("Se produjo un error");
            return;
        }

        $auth = Auth::get_instance();

        $userAccount = new User_Accounts_Data();

        $userAccount->setIdP($_SESSION["newAccount"]["postData"]["plugin"])
        ->setIdU($auth->getUserId())
        ->setAlias($_SESSION["newAccount"]["postData"]["alias"]);

        $pluginClassName = $_SESSION["newAccount"]["pluginClassname"];
        $pluginAuth = new $pluginClassName();

        $pluginAuth->setData($_REQUEST);

        if (true === ( $resp = $pluginAuth->validate() )) {

            $userAccount->setAuth($pluginAuth->getData());
            $userAccount->setEnabled("1");

            $userAccountMapper = User_Accounts_Mapper::get_instance();
            $id = $userAccountMapper->save($userAccount);

            if(is_numeric($id)) {

                $userAccount->setId($id);
            }

            if (is_numeric($_SESSION["newAccount"]["postData"]["column"])) {

                $columnPlugin = new Columns_PluginsData();
                $columnPluginMapper = new Columns_PluginsMapper();

                $columnPlugin->setIdC($_SESSION["newAccount"]["postData"]["column"]);
                $columnPlugin->setIdP($userAccount->getId());

                $id = $columnPluginMapper->save($columnPlugin);

                if(is_numeric($id)) {

                    $columnPlugin->setId($id);
                }

                $this->addToColumn($_SESSION["newAccount"]["postData"]["column"], $columnPlugin);

            } else if ($_SESSION["newAccount"]["postData"]["column"] == "new") {

                $column = new Columns_Data();
                $columnMapper = new Columns_Mapper();

                $column->setIdU($auth->getUserId());
                $column->setIdentificador($userAccount->getAlias());
                $column->setMinimized(0);

                $id = $columnMapper->save($column);

                if(is_numeric($id)) {

                    $column->setId($id);
                }

                $columnPlugin = new Columns_PluginsData();
                $columnPluginMapper = new Columns_PluginsMapper();

                $columnPlugin->setIdC($column->getId());
                $columnPlugin->setIdP($userAccount->getId());

                $id = $columnPluginMapper->save($columnPlugin);

                if(is_numeric($id)) {

                    $columnPlugin->setId($id);
                }

                $column->addPlugin($columnPlugin);
            }

            $plugin = new Plugins_Data();
            $pluginMapper = new Plugins_Mapper();

            $plugin->setId($userAccount->getIdP());
            $plugin->setActivated(1);

            $tmp = array_shift($pluginMapper->find($plugin));

            if ($tmp instanceof Plugins_Data) {

                $plugin = $tmp;

            } else {

                Throw new Exception("Sometyhing happend");
            }

           /***
             * Hacer un fetch del contenido sin esperar a cron
             */
            $cronClassName = "Plugins_".$plugin->getClassName()."_Cron";
            $cron = new $cronClassName();

            $cron->connect($userAccount);
            $cron->update();

            unset($_SESSION["newAccount"]);
            redirect("accounts", "refresh");

        } else {

            $userAccount->setError("form", $resp);
            $this->newaccount($userAccount);
            return;
        }
    }

    public function enable($id)
    {

        $auth = Auth::get_instance();

        $account = new User_Accounts_Data();
        $account->setId($id);
        $account->setIdU($auth->getUserId());

        if ($account->isValid()) {

            $accountMapper = User_Accounts_Mapper::get_instance();
            $accounts = $accountMapper->find($account);

            if (count($accounts) == 1) {

                $account = $accounts[0];
            }

            $account->setEnabled("1");
            $accountMapper->save($account);

            redirect("accounts", "refresh");
        }
    }

    public function disable ($id)
    {
    
        $auth = Auth::get_instance();
        
        $account = new User_Accounts_Data();
        $account->setId($id);
        $account->setIdU($auth->getUserId());

        if ($account->isValid()) {

            $accountMapper = User_Accounts_Mapper::get_instance();
            $accounts = $accountMapper->find($account);

            if (count($accounts) == 1) {

                $account = $accounts[0];
            }

            $account->setEnabled("0");
            $accountMapper->save($account);

            redirect("accounts", "refresh");
        }
    }

    public function delete($id)
    {
        $auth = Auth::get_instance();

        $account = new User_Accounts_Data();
        $account->setId($id);
        $account->setIdU($auth->getUserId());

        if ($account->isValid()) {

            $accountMapper = User_Accounts_Mapper::get_instance();
            $accounts = $accountMapper->find($account);
            
            if (count($accounts) == 1) {

                $account = $accounts[0];
            }

            $account->setEnabled("0");
            $accountMapper->delete($account);
            $this->deleteOrphans();
            redirect("accounts", "refresh");
        }
    }

    private function deleteOrphans()
    {
        $this->load->database();
        $sql = "Delete from userColumnPlugins where idP not in (
            select distinct id from userPlugins
        )";
        $this->db->query($sql);

        $sql = "DELETE FROM `userColumns` WHERE id NOT IN (
            SELECT DISTINCT idc
            FROM `userColumnPlugins`
        )";
        
        $this->db->query($sql);
    }

    public function me()
    {
        $this->lang->load("accounts");
        $auth = Auth::get_instance();

        $userMapper = User_Mapper::get_instance();
        $user = new User_Data();
        $user->setId($auth->getUserId());

        $user = array_shift($userMapper->find($user));

        if ($user instanceof User_Data) {

            if (count($_POST) > 0) {

                $user = $this->updateMe($user);

                if ($user->isValid()) {

                    redirect("");
                }
            }

            $this->load->masterview("accounts/me", array("user" => $user));
        }
    }

    private function updateMe (User_Data $user)
    {
        if ($_POST["passwd"] != $_POST["passwd2"]) {

            $user->setError("form", $this->lang->line("passwdMisMatch"));
            return $user;
        }

        $user->setPassword($_POST["passwd"], true);

        if ($user->isValid()) {

            $userMapper = User_Mapper::get_instance();
            $userMapper->save($user);
        }

        return $user;
    }
}
