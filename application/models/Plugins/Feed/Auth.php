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
 */
class Plugins_Feed_Auth extends Common_Auth
{
    private $_data;
    private $_config;

    /**
     * @return void
     */
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /* (non-PHPdoc)
     * @see application/models/Common/Common_Auth#hasRemoteAuth()
     */
    public function hasRemoteAuth()
    {
        return false;
    }

    /* (non-PHPdoc)
     * @see application/models/Common/Common_Auth#remoteAuth()
     */
    public function remoteAuth(){}

    /* (non-PHPdoc)
     * @see application/models/Common/Common_Auth#setData($data)
     */
    public function setData($data)
    {
         $this->_data = $data;
    }

    /* (non-PHPdoc)
     * @see application/models/Common/Common_Auth#getData()
     */
    public function getData()
    {
        return $this->_data;
    }

    /* (non-PHPdoc)
     * @see application/models/Common/Common_Auth#validate()
     */
    public function validate()
    {
        $post = $this->getData();

        if (isset($post["feed_url"]) and $post["feed_url"] != "") {

        	try {

        		$feed= Zend_Feed::import($post["feed_url"]);

        	} catch (Exception $e) {

        	   $this->_ci->lang->load("feed");
        	   return $this->_ci->lang->line("error");
        	}

            if ($feed instanceof Zend_Feed_RSS or $feed instanceof Zend_Feed_Atom) {

                $this->_data = $post["feed_url"];
                return true;
            }
        }

        return "Error";
    }
}