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
 * Clase abstracta que define los métodos que han de implementar los publishers de la aplicación.
 * @author Mikel Madariaga
 */
abstract class Common_Publish
{
    protected $_ci;
    protected $_account;

    function __construct()
    {
        // Call the Model constructor
        $this->_ci =& get_instance();
    }

    /**
     * @param User_Accounts_Data $token
     * @return void
     */
    abstract public function connect($token);
    
    /**
     * @param array $post
     * @param User_Accounts_Data $targetAccount
     * @return Message_Data
     */
    abstract public function validatePost($post = null, User_Accounts_Data $targetAccount);

    /**
     * @param array $post
     * @param User_Accounts_Data $targetAccount
     * @return Message_Data
     */
    abstract public function publish($post = null, User_Accounts_Data $targetAccount);
    
    /**
     * @param Message_Data $message
     * @param User_Accounts_Data $account
     * @return boolean
     */
    abstract public function reference(Message_Data $message, User_Accounts_Data $account);
}