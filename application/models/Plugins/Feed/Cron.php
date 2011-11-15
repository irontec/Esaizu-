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
 * Clase encargada de recoger y guardar mensajes de Linkedin
 */
class Plugins_Feed_Cron extends Common_Cron
{
    /**
     * @var User_Accounts_Data
     */
    protected $_account;

    /**
     * @return void
     */
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * Método encargado de establecer conexión con Linkedin
     * @param User_Accounts_Data $account
     * @param obj $auth
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Cron#connect($idp)
     */
    public function connect($account)
    {
        $this->start = microtime();

        $this->_account = $account;
        $this->_idUP = $this->_account->getId();
    }

    /**
     * Método encargado de recoger los timelines del usuario y sus amigos
     * @return void
     *
     * (non-PHPdoc)
     * @see application/models/Common/Common_Cron#update()
     */
    public function update()
    {
        try {

            $feed = Zend_Feed::import($this->_account->getAuth());
            $items = array();
    
            foreach ($feed as $entry) {
    
                $item["title"] = $entry->title();
                $item["link"] = $entry->link();
                $item["description"] = strip_tags($entry->description());
                $item["remoteId"] = md5($entry->link());
    
                if ($entry->pubDate()) {
                    $date = $entry->pubDate();
                } elseif ($entry->published()) {
                    $date = $entry->published();
                } elseif ($entry->created()) {
                    $date = $entry->created();
                } elseif ($entry->updated()) {
                    $date = $entry->updated();
                } elseif ($entry->modified()) {
                    $date = $entry->modified();
                } elseif ($entry->date()) {
                    $date = $entry->date();
                }
                
                if (isset($date)) {
    
                    $item["publishDate"] = date("Y-m-d H:i:s",strtotime($date));
    
                } else {
    
                    $item["publishDate"] = date("Y-m-d H:i:s",time());
                }
                    
                $items[] = $item;
            }
    
            $this->save($items);
    
            if ($this->debugMode === true) {
                echo "\r\n Feed cron elapset time : ".$this->elapsedTime()."s\r\n";
            }
    
            $accountMapper = User_Accounts_Mapper::get_instance();
            $this->_account->setLastUpdate(time());
    
            $data = array(
                "lastUpdate" => $this->_account->getLastUpdate()
            );

            $accountMapper->updateFromArray($this->_account->getId(), $data);

        } catch (Exception $e) {

            //echo  $this->_account->getAuth()."<br />".$e->getMessage();
        }

    }

    /**
     * @param array $items
     * @return void
     */
    private function save(array $items)
    {
        $auth = Auth::get_instance();
        $mapper = new Message_Mapper();

        foreach ($items as $item) {

            $message = new Message_Data();
            $message->setIdU($auth->getUserId())
                    ->setIdUP($this->_account->getId())
                    ->setRemoteId($item["remoteId"]);

            $existingMessages = $mapper->find($message);
            
            if (count($existingMessages) > 0) {

                $message = $existingMessages[0];
            }
      
            $message->setOwner(0)
                    ->setPublishDate($item["publishDate"])
                    ->setTitle($item["title"])
                    ->setLink($item["link"])
                    ->setData(array("text" => $item["description"]));

            if ($message->isValid()) {

                $mapper->save($message);
            }
        }
    }
}