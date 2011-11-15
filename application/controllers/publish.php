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
class Publish extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('pagination');
    }

    public function index(Message_Data $msg = null)
    {
        $auth = Auth::get_instance();
        $myAccounts = $auth->getEnabledAccounts();

        /***
         * Descartar los plugins que no tienen publicador
         */
        foreach ($myAccounts as $key => $account) {

            if (!class_exists("Plugins_".$account->getClassName()."_Publish")) {

                unset($myAccounts[$key]);
            }
        }

        $myPluginIds = array();
        $myOrderedAccounts = array();

        foreach ($myAccounts as $account) {

            $myPluginIds[] = $account->getIdP();
        }

        $pluginMapper = Plugins_Mapper::get_instance();
        $plugins = $pluginMapper->fetchAll();

        $availablePlugins = array();
        foreach ($plugins as $plugin) {

            if (in_array($plugin->getId(), $myPluginIds)) {

                $className =  "Plugins_".$plugin->getClassName()."_Render";
                $render = new $className();

                $availablePlugins[] = array(
                    "id"   => $plugin->getId(),
                    "name" => $plugin->getName(),
                    "publishView" => $render->getPublishFormView(),
                    "showReferenceButton" => $render->showReferenceButton()
                );
            }
        }

        $data = array(
            "accounts" => $myAccounts,
            "plugins" => $availablePlugins,
            "post" => $_POST,
            "js" => array("jquery.js","jquery-ui.js","application.publish.js", "jquery.tipsy.js"),
            "msg" => is_null($msg) ? new Message_Data() : $msg,
        );

        $this->load->masterview("publish/publish", $data);
    }

    public function send()
    {
        /***
         * Gestionar envios programados
         */
        if (isset($_POST["envio_programado"]) and $_POST["envio_programado"] == 1) {

            $this->_addToQueue();
            return;
        }
        
        $this->lang->load("errormessages");

        /***
         * Envio estandard
         */
        $message = new Message_Data();

        if(count($_POST) === 0 or !isset($_POST["publishIn"])) {

            $message->setError("form", $this->lang->line("unknownError"));
            $this->index($message);
            return;
        }

        $auth = Auth::get_instance();
        $myAccounts = $auth->getEnabledAccounts();

        $targetAccount = null;
        $referenceAccounts = array();

        foreach ($myAccounts as $account) {

            if ($account->getId() == $_POST["publishIn"]) {

                $targetAccount = $account;

            } else if (isset($_POST["referenceIn"]) and in_array($account->getId(), $_POST["referenceIn"])) {

                $referenceAccounts[] = $account;
            }
        }

        if(! $targetAccount instanceof User_Accounts_Data) {

            $message->setError("form", $this->lang->line("unknownError"));
            $this->index($message);
            return;
        }

        $publisherClassname = "Plugins_".$targetAccount->getClassName()."_Publish";
        $publisher = new $publisherClassname();

        $files =  count($_FILES) > 0 ? $_FILES : null;
        $message = $publisher->publish($_POST,$targetAccount, $files);

        if (! $message->isValid()) {

            $this->index($message);
            return;

        } else {

             foreach ($referenceAccounts as $account) {

                $publisherClassname = "Plugins_".$account->getClassName()."_Publish";
                $publisher = new $publisherClassname();

                $publisher->reference($message, $account);
            }
        }

        redirect("","refresh");
    }

    public function programmed()
    {
        $auth = Auth::get_instance();
        $queueMapper = Message_Queue_Mapper::get_instance();

        $MyMsgs = $queueMapper->find( array("message_queue.idU" => $auth->getUserId()) );

        $myAccounts = $auth->getEnabledAccounts();

        $data = array(
            "accounts" => $myAccounts,
            "msgs" => $MyMsgs,
        );

        $this->load->masterview("publish/programmed", $data);
    }

    public function edit ($id, Message_Data $msg = null)
    {
        if (!is_numeric($id)) {

            return;
        }

        $auth = Auth::get_instance();
        $queueMapper = Message_Queue_Mapper::get_instance();

        $tmpMsg = array_shift($queueMapper->find( array("message_queue.id" => $id, "message_queue.idu" => $auth->getUserId()) ));

        if (!isset($tmpMsg["post"])) {

            return;
        }

        $post = unserialize($tmpMsg["post"]);
        $context = array(
            "id" => $tmpMsg["id"],
            "idU" => $tmpMsg["idU"],
            "idUP" => $tmpMsg["idUP"],
        );

        unset($tmpMsg);

        $myAccounts = $auth->getEnabledAccounts();

        /***
         * Descartar los plugins que no tienen publicador
         */
        foreach ($myAccounts as $key => $account) {

            if (!class_exists("Plugins_".$account->getClassName()."_Publish")) {

                unset($myAccounts[$key]);
            }
        }

        $myPluginIds = array();
        $myOrderedAccounts = array();

        foreach ($myAccounts as $account) {

            $myPluginIds[] = $account->getIdP();
        }

        $pluginMapper = Plugins_Mapper::get_instance();
        $plugins = $pluginMapper->fetchAll();

        $availablePlugins = array();
        foreach ($plugins as $plugin) {

            if (in_array($plugin->getId(), $myPluginIds)) {

                $className =  "Plugins_".$plugin->getClassName()."_Render";
                $render = new $className();

                $availablePlugins[] = array(
                    "id"   => $plugin->getId(),
                    "name" => $plugin->getName(),
                    "publishView" => $render->getPublishFormView(),
                    "showReferenceButton" => $render->showReferenceButton()
                );
            }
        }

        $data = array(
            "accounts" => $myAccounts,
            "post" => $post,
            "context" => $context,
            "plugins" => $availablePlugins,
            "js" => array("jquery.js","jquery-ui.js","application.publish.js"),
            "msg" => !isset($msg) ? new Message_Data() : $msg,
        );

        $this->load->masterview("publish/edit", $data);
    }

    public function update($id)
    {
        if (! is_numeric($id)) {
        
            return;
        }

        $this->_addToQueue($id);
    }

    public function delete($id)
    {
        $auth = Auth::get_instance();
        $queueMapper = Message_Queue_Mapper::get_instance();
        
        $where = array(
            "message_queue.idU" => $auth->getUserId(),
            "message_queue.id" => $id
        );
        
        $msg = $queueMapper->delete($where);

        redirect("publish/programmed","refresh");
    }

    private function _addToQueue($id = null)
    {
        $this->lang->load("errormessages");
        
        $fechaProgramada = strtotime($_POST["dia_programado_dbformat"]." ".$_POST["hora_programada"].":00");
        $message = new Message_Data();

        if ($fechaProgramada+60 < time()) {

            $message->setError("envio_programado", $this->lang->line("dateError"));

            if (is_null($id)) {

                $this->index($message);

            } else {

                $this->edit($id, $message);
            }

            return;
        }

        $auth = Auth::get_instance();
        $myAccounts = $auth->getEnabledAccounts();

        $targetAccount = null;
        $referenceAccounts = array();

        foreach ($myAccounts as $account) {

            if ($account->getId() == $_POST["publishIn"]) {

                $targetAccount = $account;
            }
        }

        if(! $targetAccount instanceof User_Accounts_Data) {

            $message->setError("form", $this->lang->line("unknownError"));
            
            if (is_null($id)) {

                $this->index($message);

            } else {

                $this->edit($id, $message);
            }
            return;
        }

        $publisherClassname = "Plugins_".$targetAccount->getClassName()."_Publish";
        $publisher = new $publisherClassname();

        $message = $publisher->validatePost($_POST,$targetAccount);

        if (! $message->isValid()) {

            if (is_null($id)) {

                $this->index($message);

            } else {

                $this->edit($id, $message);
            }
            return;
        }

        $mapper = Message_Queue_Mapper::get_instance();

        $data = array(
            'idU' => $auth->getUserId(),
            'idUP' => $targetAccount->getId(),
            'title' => $message->getTitle(),
            'publishDate' => date("Y-m-d H:i:s",$fechaProgramada),
            'post' => serialize($_POST)
        );

        if (!is_null($id)) {

            $data["id"] = $id;
        }

        $mapper->save($data);
        redirect("publish/programmed","refresh");
    }

    /**
     * Enviar ahora mensajes programados a futuro
     * @param integer $id
     * @return void
     */
    public function now($id)
    {
        if (!is_numeric($id)) {

            die;
        }

        $auth = Auth::get_instance();
        $queueMapper = Message_Queue_Mapper::get_instance();

        $msgs = $queueMapper->find( array("message_queue.id" => $id, "idu" => $auth->getUserId()) );
        $myAccounts = $auth->getEnabledAccounts();

        foreach ($msgs as $msg) {

            $post = unserialize($msg["post"]);

            $targetAccount = null;
            $referenceAccounts = array();

            foreach ($myAccounts as $account) {

                if ($account->getId() == $post["publishIn"]) {

                    $targetAccount = $account;

                } else if (isset($post["referenceIn"]) and in_array($account->getId(), $post["referenceIn"])) {

                    $referenceAccounts[] = $account;
                }
            }

            if (! $targetAccount instanceof User_Accounts_Data) {
                continue;
            }

            $publisherClassname = "Plugins_".$targetAccount->getClassName()."_Publish";

            if (isset($this->_publishers[$publisherClassname])) {

                $publisher = $this->_publishers[$publisherClassname];

            } else {

                $publisher = $this->_publishers[$publisherClassname] = new $publisherClassname();
            }

            //publicar
            $message = $publisher->publish($post,$targetAccount);

            if (! $message->isValid()) {

                continue;
            }

            //referenciar
            foreach ($referenceAccounts as $account) {

                $publisherClassname = "Plugins_".$account->getClassName()."_Publish";
                //echo "refrenciando con ".$publisherClassname." \r\n";

                if (isset($this->_publishers[$publisherClassname])) {

                    $publisher = $this->_publishers[$publisherClassname];

                } else {

                    $publisher = $this->_publishers[$publisherClassname] = new $publisherClassname();
                }

                $publisher->reference($message, $account);
            }

            //eliminar mensaje de base de datos
            $queueMapper->delete(array("id" => $msg["id"]));
        }

        redirect("publish/programmed");
    }
}