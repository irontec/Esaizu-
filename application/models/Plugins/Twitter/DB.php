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
class Plugins_Twitter_DB extends Common_DB
{
    static protected $_message;
    
    /**
     * @return void
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Zend_Rest_Client_Result $resp
     * @param integer $owner
     * @param integer $idUP
     * @return Message_Data
     */
    public static function save(Zend_Rest_Client_Result $resp, $owner, $idUP)
    {
        $auth = Auth::get_instance();
        $messageMapper = Message_Mapper::get_instance();

        if (!isset($resp->status)) {

            $tmp = $resp;
            $resp = new stdClass();
            $resp->status = $tmp;
        }

        $remoteIds = array();

        foreach ( $resp->status as $response ) {

            $status = array(
                'created_at' => date("Y-m-d H:i:s", strtotime((string) $response->created_at)),
                'id' => (string) $response->id,
                'text' => (string) $response->text,
                'source' => (string) $response->source,
                'favorited' => (string) $response->favorited,
                'user_name' => (string) $response->user->name,
                'user_screen_name' => (string) $response->user->screen_name,
                'retweet_count' => (string) $response->retweet_count,
                'profile_image_url' => (string) $response->user->profile_image_url,
            );

            $data = array(
                'source' => $status['source'],
                'favorited' => $status['favorited'],
                'user_name' => $status['user_name'],
                'retweet_count' => $status['retweet_count'],
                'profile_image_url' => $status['profile_image_url'],
            );

            self::$_message  = New Message_Data();
            self::$_message->setRemoteId($status["id"])
                    ->setIdU($auth->getUserId())
                    ->setIdUP($idUP);

            $existingMessages = $messageMapper->find(self::$_message);
            if (count($existingMessages) > 0) {

                self::$_message = $existingMessages[0];
            }

            self::$_message->setTitle($status["text"])
                    ->setPublishDate($status["created_at"])
                    ->setLink("http://www.twitter.com/".$status["user_screen_name"])
                    ->setData(serialize($data))
                    ->setOwner($owner);

            if (self::$_message->isValid()) {

                $remoteIds[] = self::$_message->getRemoteId();
                //self::$_message->print_r();
                $messageMapper->save(self::$_message);

            } else {

               echo "\r\n\r\nSomething happend:\r\n\r\n<pre>";
               print_r(self::$_message->getErrors());
               print_r(self::$_message->getOptions());
               continue;
            }
        }

        if (self::$_message instanceof Message_Data) {

            /***
             * Borrar mensajes eliminados en twitter
             */
            $where = array(
                "where" => array(
                    "messages.owner" => self::$_message->getOwner(),
                    "messages.remoteId >" => $remoteIds[count($remoteIds)-1],
                    "idup" => $idUP
                ),

                "where_not_in" => array(
                    "messages.remoteId" => $remoteIds
                )
            );

            $messages = $messageMapper->customQuery($where);

            foreach ($messages as $msg) {
    
                if ( defined('CONSOLE')) {

                    echo "Deleting twitter message with id: ".$msg->getId()."\r\n";
                }

                $messageMapper->delete($msg);
            }
        }

        return self::$_message;
    }
}