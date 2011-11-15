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
 * Clase para la gestión de usuarios y credenciales
 *
 * Además del login/logout se encarga de:
 *  - Registro de nuevas cuentas de usuario
 *  - Activar cuentas de usuario
 *  - Eliminar cuentas de usuario
 *  - Generar nuevos passwprd para aquellos usuarios que no recuerden el mismo
 *
 *  @author Mikel Madariaga <mikel@irontec.com>
 */

class Auth extends CI_Model
{
    private $_ci; // framework instance
    public static $instance;

    protected $_user;
    protected $_accounts = null;
    //protected $_plugins;

    /**
     * @return void
     */
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        self::$instance =& $this;
        $this->_ci =& get_instance();
        $this->_ci->load->database();

        $this->_ci->load->driver('cache', $this->_ci->config->item("cache_adapter"));

        if ( ! defined('CONSOLE')) {
            $this->init();
        }
    }

    /**
     * Devuelve una instancia de la clase
     * @return Auth
     */
    public static function &get_instance()
    {
        if (self::$instance == null) {
            self::$instance = Auth::get_instance();
        }

        return self::$instance;
    }

    /**
     * Inicializa la clase y comprueba que el usuario tiene
     * permisos de acceso a la url solicitada
     *
     * @return void
     */
    private function init()
    {
        $userId = $this->session->userdata('id');

        if ($userId) {

            $user = new User_Data();
            $user->setId($userId);

            if ( true === $this->login($user) ) {

                return;
            }
        }

        if ($this->_ci->uri->segment(1) != "login") {

            redirect("login");
            return;
        }
    }

    /**
     * Carga de las cuentas (Identidades) de un usuario
     * @param integer $idu
     * @return void
     */
    private function loadAccounts($idu)
    {
        //fetch user accounts
        $userAccounts = User_Accounts_Mapper::get_instance();

        $where = array(
            "idu" => $idu
        );

        $orderBy = array(
            "idP" => "asc",
            "name" => "asc",
        );

        $this->_accounts = $userAccounts->find($where, false, false, $orderBy);
    }

    /**
     * Devuelve todas las cuentas de usuario, activadas o no
     * @return User_Data array
     */
    public function getAccounts()
    {
        if(is_null($this->_accounts)) {
            $this->loadAccounts($this->_user->getId());
        }

        return $this->_accounts;
    }

    /**
     * Devuelve la cuenta de usuario que pertenece a el id facilitado, activa o no
     * @param integer $accountId
     * @return User_Data
     */
    public function getAccount($accountId)
    {
        $enabledAccounts = $this->getAccounts();

        foreach($enabledAccounts as $account) {

            if($account->getId() == $accountId) {

                return $account;
            }
        }

        return false;
    }

    /**
     * Devuelve las cuentas de usuario activadas
     * @return User_Accounts_Data array
     */
    public function getEnabledAccounts()
    {
        $tmp = array();
        foreach ($this->getAccounts() as $account) {

            if ($account->getEnabled() == 1) {
                $tmp[] = $account;
            }
        }

        return $tmp;
    }

    /**
     * Devuelve la cuenta de usuario que corresponde a el id facilitado, solo cuentas activadas
     * @param integer $accountId
     * @return User_Accounts_Data
     */
    public function getEnabledAccount($accountId)
    {
        $enabledAccounts = $this->getEnabledAccounts();

        foreach($enabledAccounts as $account) {

            if($account->getId() == $accountId) {

                return $account;
            }
        }

        return false;
    }

    /**
     * Devuelve el id del usuario actual
     * @return integer
     */
    public function getUserId()
    {
        if($this->_user != null)
        return $this->_user->getId();
    }

    /**
     * Devuelve el nombre de usuario
     * @return string
     */
    public function getUserName()
    {
        if($this->_user != null)
        return $this->_user->getUserName();
    }

    /**
     * Devuelve el correo electrónico del usuario actual
     * @return string
     */
    public function getEmail()
    {
        if($this->_user != null)
        return $this->_user->getEmail();
    }

    /**
     * Activa una cuenta de usuario
     * @param User_Data $user
     * @return User_Data
     */
    public function activation(User_Data $user)
    {

        if ($user->isValid()) {

            $userMapper = User_Mapper::get_instance();
            $results = $userMapper->find($user);

            if (count($results) == 1) {

                $user = $results[0];
                $user->reset("activationCode")->setActivated($this->config->item('public'));

                $userMapper->save($user);

            } else {

                $user->setError("any", "Error");
            }

        }
        return $user;
    }

    /**
     * Envia un email de confirmación al usuario que solicita la baja en la aplicación
     * @return boolean
     */
    public function deleteAccount()
    {

        $this->_ci->load->helper("string");
        $code = random_string();

        $this->_user->setDeleteAccountCode($code);

        $userMapper = User_Mapper::get_instance();
        $userMapper->save($this->_user);

        $mailer = new Mailer();
        $subject = 'Baja en '.$this->_ci->config->item('appName');


        $data = array("id" => $this->_user->getid(), "code" => $code);
        $msg = $this->_ci->load->view("auth/baja_email", $data, true);

        return $mailer->send($this->_user->getEmail(), $subject, $msg);
    }

    /**
     * Elimina un usuario de la base de datos
     * @param User_Data $user
     * @return boolean
     */
    public function deleteAccountConfirm(User_Data $user)
    {

        if ($user->isValid()) {

            $userMapper = User_Mapper::get_instance();
            $results = $userMapper->find($user);

            if (count($results) == 1) {

                $user = $results[0];
                $userMapper->delete($user);
                return true;
            }
        }

        return false;
    }

    /**
     * Genera un nuevo password para el usuario y lo envia a su cuenta de email
     * @param User_Data $user
     * @return User_Data
     */
    public function resetForgottenPassword(User_Data $user)
    {

        if ($user->isValid()) {

            $userMapper = User_Mapper::get_instance();
            $results = $userMapper->find($user);

            if (count($results) == 1) {

                $user = $results[0];
                $this->_ci->load->helper("string");
                 
                $newPass = random_string("alnum", 6);
                $oldPass = $user->getPassword();

                $user->setPassword($newPass, true);
                $userMapper->save($user);

                $mailer = new Mailer();

                $subject = "Su nueva contraseña ".$this->_ci->config->item('appName');
                $msg = $this->_ci->load->view("auth/remember_email", array("newPass" => $newPass), true);

                if (!$mailer->send($user->getEmail(), $subject, $msg)) {

                    $user->setPassword($oldPass);
                    $userMapper->save($user);
                    show_error("Se produjo un error");
                }

            } else {

                $this->_ci->lang->load("auth");
                $user->setError("form", $this->_ci->lang->load("userDoesNotExist"));
            }
        }

        return $user;
    }

    /**
     * Login de usuario
     * @param User_Data $user
     * @param string $passwd
     * @param integer $remember (valor igual a 1 para recordar al usuario más allá de la sesión actual)
     * @return boolean | User_Data
     */
    public function login(User_Data $user, $passwd = null, $remember = 0)
    {
        if (!$user->isValid()) {

            return $user;
        }

        $userMapper = new User_Mapper;
        $tmp_user = array_shift($userMapper->find($user));

        if ($tmp_user instanceof User_Data and (is_null($passwd) or $tmp_user->checkPassword($passwd))) {

            $user = $tmp_user;
            if($user->getActivated() == 0) {

                $this->_ci->lang->load("auth");
                $user->setError("form", $this->_ci->lang->line("unactivedUser"));
                return $user;
            }

            if ( ! defined('CONSOLE')) {

                $user->setLastVisit(date("Y-m-d H:i:s"));
                $userMapper->save($user);
            }

            $this->_ci->session->set_userdata("id", $user->getid());
            $this->_ci->session->set_userdata('accessCounter', 0);

            if ($remember == 1) {
                $this->_ci->session->set_userdata("RememeberMe", $remember);
            }

            //resetear las cuentas para evitar problemas con la persistencia.
            $this->_accounts = null;

            $this->_user = $user;
            return true;

        } else {

            $kont = (int) $this->_ci->session->userdata('accessCounter');
            $this->_ci->session->set_userdata('accessCounter', $kont+1);
            $this->_ci->lang->load("auth");
            $user->setError("form", $this->_ci->lang->line("invalidUserPass"));
            return $user;
        }
    }

    /**
     * Logout
     * @return void
     */
    public function logout()
    {
        $this->_ci->session->sess_destroy();
    }

    /**
     * Crea una nueva cuenta de usuario y envia un email de bienvenida
     * @param User_Data $user
     * @return User_Data
     */
    public function register(User_Data $user)
    {
        if (!$user->isValid()) {
            return $user;
        }

        $userMapper = User_Mapper::get_instance();

        $where = "username = '".$user->getUserName()."'";
        $results = $userMapper->find($where);

        if (is_array($results) and count($results) == 0) {

            $this->_ci->load->helper("string");
            $user->setActivationCode(random_string())
            ->setActivated("0");

            $user->setId($userMapper->save($user));

            if (is_numeric($user->getId())) {

                $mailer = new Mailer();

                $subject = 'Bienvenido a '.$this->_ci->config->item('appName');
                $msg = $this->_ci->load->view("auth/confirmation_email", array("obj" => $user), true);

                if (!$mailer->send($user->getEmail(), $subject, $msg)) {

                    $userMapper->delete($user);
                    $user->resetAll(true);
                }
            }

        } else {

            $this->_ci->lang->load("auth");
            $user->setError("form", $this->_ci->lang->line("alreadyExistingUserEmail"));
        }

        return $user;
    }

    /**
     * Coprueba si el usuario actual tiene permisos de administracion (Id de usuario == 1)
     * @return boolean
     */
    public function isAdmin()
    {
        if ($this->_user->getId() == 1) {

            return true;
        }

        return false;
    }
}