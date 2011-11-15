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
class Login extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        if (count($_POST) > 0) {

            $user = new User_Data();
            $user->setUserName($_POST["nickname"]);

            if ($user->isValid()) {

                $auth = Auth::get_instance();

                if (isset($_POST["remember"]) and $_POST["remember"] == 1) {
                
                    $remember = 1;

                } else {
                
                    $remember = 0;
                }

                if ( true === ($user = $auth->login($user, $_POST["passwd"], $remember)) ) {

                    if($auth->isAdmin())
                        redirect("/admin", "refresh");
                    else
                        redirect("/", "refresh");
                    return;
                }
            }

            $this->load->masterview("auth/login", array("obj" => $user));
            return;
        }

        $user = new User_Data();
        $this->load->masterview("auth/login", array("obj" => $user));
    }

    public function registro()
    {
        if (count($_POST) > 0) {

            $user = new User_Data();
            $user->setUserName($_POST["userName"])
                 ->setEmail($_POST["email"])
                 ->setPassword($_POST["passwd"], true);

            if ($_POST["passwd"] != $_POST["passwd2"]) {

                $this->lang->load("errormessages");
                $user->setError("password2", $this->lang->line("passwordMismatch"));
            }

            if ($user->isValid()) {

                $auth = Auth::get_instance();
                $user = $auth->register($user);

                if ( $user->isValid() and is_numeric($user->getId())) {

                    $this->load->masterview("auth/check_email");
                    return;

                } else if ($user->isValid()) {

                    show_error('Se produjo un error', 500);
                    return;
                }
            }

        } else {

            $user = new User_Data();
        }

        $this->load->masterview("auth/registro", array("obj" => $user));
    }

    //confirmar alta
    public function confirm($id, $activationCode)
    {
        if ($id == "" or $activationCode == "") {

            show_error("Se produjo un error", 500);
            return;
        }

        $user = new User_Data();
        $user->setId($id)->setActivated("0")->setActivationCode($activationCode);

        $auth = Auth::get_instance();
        $user = $auth->activation($user);

        if ($user->isValid()) {

            $auth->login($user);
            $this->load->masterview("auth/registrado", array("obj" => $user));

        } else {

            $str = "Esta cuenta ya ha sido activada. ";
            $str .= "Puede darse acceder a la applicaci&oacute;n desde <a href='".
            site_url("login")."'>aqu&iacute;</a> o pedir una <a href='".
            site_url("login/remember")."'>nueva contrase&ntilde;a</a>";

            show_error($str, 500);
        }
    }

    public function baja()
    {

        $auth = Auth::get_instance();
        if ($auth->deleteAccount()) {

            $this->load->masterview("auth/baja");

        } else {

            show_error("Seprodujo un error", 500);
        }
    }

    public function baja_confirm($id, $code)
    {

        if ($id == "" or $code == "") {

            show_error("Se produjo un error", 500);
            return;
        }

        $user = new User_Data();
        $user->setId($id)->setActivated("1")->setDeleteAccountCode($code);

        $auth = Auth::get_instance();

        if ($auth->deleteAccountConfirm($user)) {

            $this->load->masterview("auth/baja_confirmada");

        } else {

            show_error("Código no valido", 500);
        }
    }

    public function logout()
    {

       $auth = Auth::get_instance();
       $auth->logout();
       redirect("login", "refresh");
    }

    public function remember()
    {
        if (count($_POST) > 0) {

            $user = new User_Data();
            $user->setUserName($_POST["email"]);

            if ($user->isValid()) {

                $auth = Auth::get_instance();
                $auth->resetForgottenPassword($user);
                $this->load->masterview("auth/remember_done");
                $this->session->set_userdata('accessCounter', "0");
                return;
            }

        } else {

            $user = new User_Data();
        }

        $this->load->masterview("auth/remember", array("obj" => $user));
    }
}