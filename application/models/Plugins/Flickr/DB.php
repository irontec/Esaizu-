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
class Plugins_Flickr_DB extends Common_DB
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
     * @param array $images
     * @param User_Accounts_Data $account
     * @return Message_Data
     */
    public static function save(array $images, $account)
    {
        $auth = Auth::get_instance();
        $messageMapper = Message_Mapper::get_instance();

        $aboutMe = unserialize($account->getAuth());

        foreach ($images as $item) {

            if (!isset($item["activities"])) {

                continue;
            }

            $image = $item;
            unset($image["activities"]);

            foreach ($item["activities"] as $activity) {

                self::$_message = new Message_Data();
                self::$_message->setIdU($auth->getuserId())
                        ->setidUP($account->getId())
                        ->setLink("http://www.flickr.com/photos/".$item["owner"]."/".$item["id"]."/");

                switch($activity["type"]) {

                   case "fave":

                         self::$_message->setTitle("favorite");
                         self::$_message->setRemoteId("fa".$activity["user"].$activity["dateadded"].$image["id"]);

                         break;

                   case "comment":

                           self::$_message->setTitle($activity["_content"]);
                           self::$_message->setRemoteId("co".$activity["commentid"]);

                           unset($activity["_content"]);
                           unset($activity["commentid"]);

                           break;

                   case "uploaded" :

                           if (isset($activity["photos_uploaded"]) and $activity["photos_uploaded"] == "")
                           self::$_message->setTitle($activity["photos_uploaded"]);
                           self::$_message->setRemoteId("up".$image["owner"].$image["id"]);
                           break;

                   case "app" :

                           self::$_message->setTitle($image["title"]);
                           self::$_message->setRemoteId("up".$image["owner"].$image["id"]);
                           break;

                   default:

                           continue;
                           break;
                }

                $existingMessages = $messageMapper->find(self::$_message);
                if(count($existingMessages) > 0) {

                    self::$_message = array_shift($existingMessages);
                }

                $metadata = array(
                    "image" => $image,
                    "event" => $activity
                );

                $mine = 0;
                if ($activity["type"] == "app") {

                   $mine = 1;

                } else if (isset($activity["user"]) and $activity["user"] == $aboutMe["user"]["nsid"]) {

                    $mine = 1;
                }

                self::$_message->setOwner($mine);

                if (isset($activity["dateadded"])) {
                
                    self::$_message->setPublishDate(date("Y-m-d H:i:s", $activity["dateadded"]));

                } else {
                
                    self::$_message->setPublishDate(date("Y-m-d H:i:s", time()));
                }
 
                self::$_message->setData($metadata);

                $messageMapper->save(self::$_message);
            }
        }

        return self::$_message;
    }
}