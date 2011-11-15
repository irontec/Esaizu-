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
 */
class User_Data extends Common_Data
{
    protected $_id;
    protected $_userName;
    protected $_password;
    protected $_email;
    protected $_activated;
    protected $_activationCode;
    protected $_forgottenPasswordCode;
    protected $_deleteAccountCode;
    protected $_lastVisit;
    protected $_created;

    /**
     * @param mixed Array
     * @return void
     */
    public function __construct(array $options = NULL)
    {
        parent::__construct();

        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
    * @param integer $value
    * @return User_Data
    */
    public function setId($value)
    {
        $rules = 'required|integer|max_length[11]';
        if ($this->validate($rules, 'id', $value)) {

            $this->_id = $value;
        }

        return $this;
    }

    /**
     *
     * @return integer
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
    * @param string $value
    * @return User_Data
    */
    public function setUserName($value)
    {
        $rules = 'required|max_length[45]|min_length[4]';
        if ($this->validate($rules, 'userName', $value)) {

            $this->_userName = $value;
        }

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->_userName;
    }

   /**
    * @param string $value
    * @param boolean $encrypt
    * @return User_Data
    */
    public function setPassword($value, $crypt = false)
    {
        $rules = 'required|max_length[50]|min_length[5]';
        if ($this->validate($rules, 'password', $value)) {

            if ($crypt === true) {

                $value = $this->cryptPassword($value);
            }

            $this->_password = $value;
        }

        return $this;
    }
    
    
    /**
     * @param string $pass
     * @param string $salt
     * @return string
     */
    private function cryptPassword($pass, $salt = '')
    {
        $salt = ($salt=='') ? random_string('alnum', 8) : $salt;
        return (crypt($pass, '$1$' . $salt . '$'));
    }

    /**
     * @param string $pass
     * @return boolean
     */
    public function checkPassword($pass)
    {
        if (!isset($this->_password)) return false;
    
        list(,, $salt, $unsalted) = explode('$', $this->_password);
    
        return ($this->_password == $this->cryptPassword($pass, $salt));
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->_password;
    }

   /**
    * @param string $value
    * @return User_Data
    */
    public function setEmail($value)
    {
        $rules = 'required|max_length[120]|valid_email';
        if ( $this->validate($rules, 'email', $value) ) {

            $this->_email = $value;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->_email;
    }
    
   /*
    * @param integer $value (0 or 1)
    * @return User_Data
    */
    public function setActivated($value)
    {
        $rules = 'required|integer|max_length[1]';
        if ($this->validate($rules, 'activated', $value)) {

            $this->_activated = $value;
        }

        return $this;
    }

    /**
     * @return integer (1 or 0)
     */
    public function getActivated()
    {
        return $this->_activated;
    }
    
   /**
    * @param string $value
    * @return User_Data
    */
    public function setActivationCode($value)
    {
        $rules = 'max_length[50]';
        if ($this->validate($rules, 'activationCode', $value)) {

            $this->_activationCode = $value;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getActivationCode()
    {
        return $this->_activationCode;
    }
    
   /**
    * @param string $value
    * @return User_Data
    */
    public function setForgottenPasswordCode($value)
    {
        $rules = 'max_length[50]';
        if ($this->validate($rules, 'forgottenPasswordCode', $value)) {

            $this->_forgottenPasswordCode = $value;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getForgottenPasswordCode()
    {
        return $this->_forgottenPasswordCode;
    }
    
    /**
     * @param string $value
     * @return User_Data
     */
    public function setDeleteAccountCode($value)
    {
        $rules = 'max_length[50]';
        if ($this->validate($rules, 'deleteAccountCode', $value)) {

            $this->_deleteAccountCode = $value;
        }

        return $this;
    }
    
    /**
     * @return string
     */
    public function getDeleteAccountCode()
    {
        return $this->_deleteAccountCode;
    }
    
   /**
    * @param datetime $value
    * @return User_Data
    */
    public function setLastVisit($value)
    {
        $rules = 'date';
        if ($this->validate($rules, 'lastVisit', $value)) {

            $this->_lastVisit = $value;
        }

        return $this;
    }

    /**
     * @return datetime
     */
    public function getLastVisit()
    {
        return $this->_lastVisit;
    }

   /**
    * @param datetime $value
    * @return User_Data
    */
    public function setCreated($value)
    {
        $rules = 'required|time';
        if ($this->validate($rules, 'created', $value)) {

            $this->_created = $value;
        }

        return $this;
    }

    /**
     * @return datetime
     */
    public function getCreated()
    {
        return $this->_created;
    }
}