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
 * Métodos públicos accesibles desde la propia interface de la applicación
 */
class Plugins_Facebook_Public extends Common_Public
{
    /**
     * @return void
     */
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * @param integer $msgId
     * @return void
     */
    public function like ($msgId)
    {
        $msgMapper = Message_Mapper::get_instance();

        $msg = new Message_Data();
        $msg->setId($msgId);
        $msg = array_shift($msgMapper->find($msg));

        $idUP = $msg->getIdUP();

        if (! is_numeric($idUP)) {

            return;
        }

        $account = new User_Accounts_Data();
        $account->setId($idUP);
        $account->setIdU($this->_auth->getUserId());

        $accountMapper = User_Accounts_Mapper::get_instance();
        $account = array_shift($accountMapper->find($account));

        if (! $account instanceof User_Accounts_Data) {

            return;
        }

        $link = "https://graph.facebook.com/".$msg->getRemoteId()."/likes";

        $mytoken = unserialize($account->getAuth());

        $httpClient = new Zend_Http_Client($link, array(
            'maxredirects' => 0,
            'timeout'      => 30)
        );

        $requestData = array(
            'access_token'  => $mytoken["token"],
        );

        $httpClient->setParameterPost($requestData);
        $response = $httpClient->request('POST');

        if ($response->getStatus() != 200) {
        
            //error
        }

        redirect("","refresh");
    }

}