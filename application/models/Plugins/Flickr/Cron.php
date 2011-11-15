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
class Plugins_Flickr_Cron extends Common_Cron
{
    protected $_account;
    /**
     * @var Zend_Oauth_Client
     */
    protected $_client;
    protected $_conf;

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
        $auth = unserialize($account->getAuth());

        $this->_ci->config->load('apikeys', TRUE);
        $this->_config = $this->_ci->config->item('flickr', 'apikeys');

        $this->_ci->load->library("phpFlickr");
        $this->_ci->phpflickr->initialize($this->_config);

        $this->_credentials = unserialize($this->_account->getAuth());
        $this->_client = $this->_ci->phpflickr;

        $this->_client->setToken($this->_credentials["token"]);
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
        /*** User comments ***/
        $data = $this->_client->activity_userComments();
        $images = $this->parser($data);
        //$this->save($images);
        Plugins_Flickr_DB::save($images, $this->_account);

        /*** User Photos ***/
        $data = $this->_client->activity_userPhotos();
        $images = $this->parser($data);
        //$this->save($images);
        Plugins_Flickr_DB::save($images, $this->_account);

        /*** Recently uploaded photos ***/
        $photos = $this->_client->photos_getContactsPhotos(null, null, true);
        $data = $this->_client->contacts_getListRecentlyUploaded(time()-60*60*12);

        $images = array();
        if (isset($data["contacts"]["contact"])) {

            foreach ($data["contacts"]["contact"] as $key => $contact) {

                foreach ($photos as $photo) {

                    if ($contact["nsid"] == $photo["owner"]) {

                        $image = array(
			                "id" => $photo["id"],
			                "owner" => $photo["owner"],
			                "ownername" => $photo["username"],
			                "secret" => $photo["secret"],
			                "server" => $photo["server"],
			                "farm" => $photo["farm"],
			                "title" => $photo["title"],
			                "activities" => array(
                                array(
                                    "type" => "uploaded",
                                    "photos_uploaded" => $contact["photos_uploaded"],
                                    "user" => $photo["owner"]
                                )
                            ),
            		    );
            
            		    $images[] = $image;
                        break;
                    }
                }
            }
        }

        Plugins_Flickr_DB::save($images, $this->_account);

        if ($this->debugMode === true) {
            echo "\r\n Flickr cron elapset time : ".$this->elapsedTime()."s\r\n";
        }

        $accountMapper = User_Accounts_Mapper::get_instance();
        $this->_account->setLastUpdate(time());

        $data = array(
            "lastUpdate" => $this->_account->getLastUpdate()
        );

        $accountMapper->updateFromArray($this->_account->getId(), $data);
    }

    /**
     * @param mixed $data
     * @return array
     */
    private function parser($data)
    {
        $images = array();

        if (isset($data[0]["type"])) {

            foreach ($data as $item) {

                $message = new Message_Data();

                $image = array(
                    "type" => $item["type"],
                    "id" => $item["id"],
                    "owner" => $item["owner"],
                    "ownername" => $item["ownername"],
                    "secret" => $item["secret"],
                    "server" => $item["server"],
                    "farm" => $item["farm"],
                    "title" => $item["title"],
                    "comments" => $item["comments"],
                    "notes" => $item["notes"],
                    "views" => $item["views"],
                    "faves" => $item["faves"],
                    "activities" => array(),
                );

                foreach($item["activity"]["event"] as $activity) {

                    $image["activities"][] = $activity;
                }

                $images[] = $image;
            }
        }
        
        return $images;
    }
}