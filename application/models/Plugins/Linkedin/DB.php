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
class Plugins_Linkedin_DB extends Common_DB
{

    static protected $_message;

    /***
    * Constantes necesarias para el parseo del xml
    */
    const updateContent = "update-content";
    const firstName = "first-name";
    const lastName = "last-name";
    const currentShare = "current-share";
    const pictureUrl = "picture-url";
    const shortenedUrl = "shortened-url";
    const siteStandardProfileRequest = "site-standard-profile-request";
    const thumbnailUrl = "thumbnail-url";

    /**
     * @return void
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * @param SimpleXMLElement $data
     * @param integer $owner
     * @param User_Accounts_Data $account
     * @return Message_Data
     */
    public static function save(SimpleXMLElement $data, $owner = 0, $account)
    {
        $auth = Auth::get_instance();
        $messageMapper = Message_Mapper::get_instance();

        $remoteIds = array();

        foreach ($data->update as $item) {

            $item = $item->{self::updateContent}->person;

            $user = array(
                "id" => (string) $item->id,
                "firstName" => (string) $item->{self::firstName},
                "lastName" => (string) $item->{self::lastName},
                "headline" => (string) $item->headline,
                "pictureUrl" => (string) $item->{self::pictureUrl},
                "profile" => (string) $item->{self::siteStandardProfileRequest}->url
            );

            $item = $item->{self::currentShare};
 
            $commnt = (string) $item->comment;
            $encoding = mb_detect_encoding($commnt, "auto");

            if($encoding != "UTF-8") {

                $commnt = utf8_encode($commnt);
            }

            $content = array(
                "contentId" => (string) $item->id,
                "timestamp" => (string) $item->timestamp,
                "comment" => $commnt,
                "link" => isset($item->content->{self::shortenedUrl}) ? (string) $item->content->{self::shortenedUrl} : "",
                "thumbnailUrl" => isset($item->content->{self::thumbnailUrl}) ? (string) $item->content->{self::thumbnailUrl} : "",
                "description" => isset($item->content->description) ? (string) $item->content->description : "",
                "title" => isset($item->content->title) ? (string) $item->content->title : "",
            );

            self::$_message  = New Message_Data();
            self::$_message->setRemoteId($content["contentId"])
                    ->setIdU($auth->getUserId())
                    ->setIdUP($account->getId());

            $existingMessages = $messageMapper->find(self::$_message);
            if (count($existingMessages) > 0) {

                self::$_message = $existingMessages[0];
            }

            $link = $content["link"] != "" ? $content["link"] : $user["profile"];

            self::$_message->setTitle($content["comment"])
                    ->setPublishDate( date("Y-m-d H:i:s", substr($content["timestamp"], 0, -3)) )
                    ->setLink($link)
                    ->setData(array_merge($user, $content))
                    ->setOwner($owner);

            if (self::$_message->isValid()) {

                $remoteIds[] = self::$_message->getRemoteId();
                $mId = $messageMapper->save(self::$_message);

                self::$_message->setId($mId);

            } else {

               echo "\r\n\r\nSomething happend:\r\n\r\n";
               print_r(self::$_message->getErrors());
               print_r(self::$_message->getOptions());
               continue;
            }
        }

        if (self::$_message instanceof Message_Data and count($remoteIds) > 0) {

            /***
             * Borrar mensajes eliminados en linkedin
             */
            $where = array(

                "where" => array(
                    "messages.owner" => self::$_message->getOwner(),
                    "messages.remoteId >" => $remoteIds[count($remoteIds)-1],
                    "idup" => self::$_message->getIdUP()
                ),

                "where_not_in" => array(
                    "messages.remoteId" => $remoteIds
                )
            );

            $messages = $messageMapper->customQuery($where);

            foreach ($messages as $msg) {

                if ( defined('CONSOLE')) {

                    echo "Deleting Linkedin message with id: ".$msg->getId()."\r\n";
                }

                $messageMapper->delete($msg);
            }
        }

        return self::$_message;
    }
}