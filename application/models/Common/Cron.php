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
 * Clase abstracta que han de implementar todos
 * de los plugins que necesiten realizar trabajos en background
 * con cron
 * @author Mikel Madariaga <mikel@irontec.com>
 */
abstract class Common_Cron
{
    protected $_ci; // framework instance
    protected $_credentials;
    protected $_idP; //plugin id
    
    protected $debugMode = false;
    
   /***
    * bechmarking
    */
    protected $start;
    protected $end;

    function __construct()
    {
        $this->_ci =& get_instance();
        $this->_ci->load->database();
    }

    /**
     * @param User_Accounts_Data $account
     * @param obj $auth
     * @return void
     */
    abstract public function connect($idp);

    /**
     * @return void
     */
    abstract public function update();

    /**
     * @return number
     */
    protected function elapsedTime() {
    
        $this->end = microtime();
        $decimals = 4;

        list($sm, $ss) = explode(' ', $this->start);
        list($em, $es) = explode(' ', $this->end);

        return number_format(($em + $es) - ($sm + $ss), $decimals);
    }
}