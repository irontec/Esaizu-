<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
if ( ! defined('CONSOLE')) exit('Executable only from command line');
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
class Cron extends CI_Controller
{
    protected $_plugins = array();
    protected $_publishers = array();
    
    protected $_startTime;

    protected $_lockFileName;

    /**
     * Tiermpo de espera entre las iteraciones del while
     * @var integer
     */
    //private $loopDelay = 10; //seconds

    public function __construct()
    {
        parent::__construct();
        $this->load->driver('cache', $this->config->item("cache_adapter"));
        $this->load->helper("file");

        $this->_lockFileName = FCPATH.APPPATH.APPPATH.'cache/cronlock';
        $this->_startTime = time();
    }

    private function checkLock()
    {
        $info = get_file_info($this->_lockFileName);

        if($info === false) {

            write_file($this->_lockFileName, "Cron job currently running");
            return true;
            
        } else {

            if( ($info["date"] + (3*60)) < time()) {

                write_file($this->_lockFileName, "Cron job currently running");
                return true;
            }
        }

        return false;
    }
    
    private function unlock()
    {
        delete_files($this->_lockFileName);
        echo "Cron task finished at ".date("Y-m-d H:i:s").". Elapsed time: ".(time()-$this->_startTime)." seconds.\r\n";
    }

    public function index()
    {
        if (!$this->checkLock()) {

            return;
        }

        $userMapper = User_Mapper::get_instance();
        $auth = Auth::get_instance();
        $queueMapper = Message_Queue_Mapper::get_instance();

        //while (true) {

        	/*****************************************************************
        	 ********************* Enviar mensajes Programados ***************
        	 *****************************************************************/
        	$msgs = $queueMapper->find( array("publishDate <" => date("Y-m-d H:i:s",time())) );
        	$currentUser = null;

        	foreach ($msgs as $msg) {

				if (is_null($currentUser)) {

					$currentUser = array_shift($userMapper->find(array("user.id" => $msg["idU"])));

				} else if ($msg["id"] != $currentUser->getId()) {

					$currentUser = array_shift($userMapper->find(array("user.id" => $msg["idU"])));
				}

				if (! $currentUser instanceof User_Data) {

					echo "Usuario no valido!";
					continue;
				}

				$post = unserialize($msg["post"]);

                if (true === ($resp = $auth->login($currentUser))) {

        	        $myAccounts = $auth->getEnabledAccounts();

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

                } //end if $auth->login()
        	} // end foreach $msgs

        	/*****************************************************************
        	 ************************* Actualizar mensajes *******************
        	 *****************************************************************/
            //Recoger usuarios con actividad en los últimos 5 minutos
            $users = $userMapper->find(array("activated" => "1","lastVisit >" => date("Y-m-d H:i:s", time()-(60*5))));

            foreach ($users as $user) {

                $this->_plugins = array();

                if (true === ($resp = $auth->login($user))) {

                    $userAccounts = $auth->getEnabledAccounts();

                    foreach ($userAccounts as $account) {

                        $lastUpdate = strtotime($account->getLastUpdate());

                    	if ( ($lastUpdate + $account->getUpdateFrecuency()) > time() ) {
                            /*
                             * La cuenta ha sido actualizada recientemente
                             * Ignorar update
                             */
                            continue;
                        }

                        $pluginClassName = "Plugins_".$account->getClassName()."_Cron";

                        if (isset($this->_plugins[$pluginClassName])) {

                            $plugin = $this->_plugins[$pluginClassName];

                        } else {

                            if (class_exists($pluginClassName)) {

                                $plugin = $this->_plugins[$pluginClassName] = new $pluginClassName();

                            } else {

                                echo $pluginClassName." not found <br />";
                                continue;
                            }
                        }

                        if ($account->isValid()) {

                            try {

                                $plugin->connect($account);
                                $plugin->update();

                            } catch (Exception $e) {

                                /***
                                 * Aquí solo deberían llegar los connection timeouts
                                 * El resto de validacion/gestión de errores deberían ser
                                 * implementados en cada plugin
                                 */
                                echo "Error:: ".$e->getMessage();
                            }
                        }

                    } //end foreach $userAccounts
                } //end if $auth->login()
            } //end foreach $users

            /*echo "Cron loop\r\n";
            $sleep = (int) $this->loopDelay;
            sleep($sleep);*/
        //}

       $this->unlock();
    }
    
    
    
}
