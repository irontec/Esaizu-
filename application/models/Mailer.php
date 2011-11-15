<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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

class Mailer extends CI_Model
{
    private $_ci;
    protected $_conf;

    /**
     * Constructor
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->_ci =& get_instance();

        // Set the character encoding in MB.
        if (function_exists('mb_internal_encoding')) {

            mb_internal_encoding($this->_ci->config->item('charset'));
        }

        $this->_ci->config->load('email', TRUE);
        $this->_conf = $this->_ci->config->item('email');

        log_message('debug', "Email Class Initialized");
    }

    /**
     * Método para el envio de emails con la configuración definida en
     * config/email.php . No envia lotes de emails
     *
     * @param string $to
     * @param string $subject
     * @param string $msg
     * @return boolean
     */
    public function send ($to, $subject, $msg)
    {
        $this->_ci->load->library('email');
        $this->_ci->email->initialize($this->_conf);
        $this->_ci->email->from($this->_conf["from"], $this->_conf['from_name']);

        $this->_ci->email->to($to);
        $this->_ci->email->subject($subject);
        $this->_ci->email->message($msg);

        if (!$this->_ci->email->send()) {

            log_message('error', $this->_ci->email->print_debugger());
            $this->email->clear(TRUE);
            return false;
        }

        $this->email->clear(TRUE);
        return true;
    }
}