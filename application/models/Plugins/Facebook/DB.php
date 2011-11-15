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
class Plugins_Facebook_DB extends Common_DB
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
     * @param array $data
     * @param User_Accounts_Data $account
     * @param string $src
     * @return Message_Data
     */
    public static function save(array $data, $account, $src = "home")
    {

        $auth = Auth::get_instance();
        $messageMapper = Message_Mapper::get_instance();

        $msgIds = array();

        foreach ($data as $item) {

            self::$_message = new Message_Data();
            self::$_message->setIdU($auth->getUserId())
                    ->setIdUP($account->getId())
                    ->setRemoteId($item->id);

            $existingMessages = $messageMapper->find(self::$_message);

            if (count($existingMessages) > 0) {

                self::$_message = array_shift($existingMessages);
            }

            $item->created_time = substr($item->created_time, 0, strpos($item->created_time, "+"));

            $item->created_time = date("Y-m-d H:i:s", (strtotime(str_replace("T", " ", $item->created_time))) + 60*60*2);
            self::$_message->setPublishDate($item->created_time);

            $owner = $item->from->id == $account->getMetadata("id") ? "1" : "0";
            self::$_message->setOwner($owner);

            switch ($item->type) {

                case "link":

                    self::$_message->setLink($item->link);
                    self::$_message->setTitle($item->name);

                    $data = array(
                        "text" => isset($item->message) ? $item->message : false,
                        "description" => isset($item->description) ? $item->description : false,
                        "from" => $item->from,
                        "actions" => $item->actions,
                        "type" => $item->type,
                        "picture" => isset($item->picture) ? $item->picture : false,
                        "likes" => isset($item->likes->count) ? $item->likes->count : 0,
                        "comments" => isset($item->comments->count) ? $item->comments->count : 0,
                    );

                    break;

                case "photo":

                    self::$_message->setLink($item->link);

                    if (isset($item->name)) {

                        self::$_message->setTitle($item->name);

                    } else if (isset($item->message)) {

                        self::$_message->setTitle($item->message);
                    }

                    $data = array(
                        "from" => $item->from,
                        "type" => $item->type,
                        "likes" => isset($item->likes->count) ? $item->likes->count : 0,
                        "comments" => isset($item->comments->count) ? $item->comments->count : 0,
                        "properties" => isset($item->properties[0]) ? $item->properties[0] : false,
                        "picture" => isset($item->picture) ? $item->picture : false,
                        "text" => isset($item->caption) ? $item->caption : false,
                    );

                    break;

                case "status":

                    self::$_message->setLink("#");

                    if (isset($item->message) ){

                       self::$_message->setTitle($item->message);

                    } else if (isset($item->caption)) {

                        self::$_message->setTitle($item->caption);

                    } else {

                        //print_r($item);
                        continue;
                    }

                    $data = array(
                        "from" => $item->from,
                        "actions" => $item->actions,
                        "type" => $item->type,
                        "likes" => isset($item->likes->count) ? $item->likes->count : 0,
                        "comments" => isset($item->comments->count) ? $item->comments->count : 0,
                    );

                    break;

                case "video":

                    self::$_message->setLink($item->link);
                    if (isset($item->message)) {

                        self::$_message->setTitle($item->message);
                    }

                    $data = array(
                        "from" => $item->from,
                        "actions" => $item->actions,
                        "type" => $item->type,
                        "likes" => isset($item->likes->count) ? $item->likes->count : 0,
                        "comments" => isset($item->comments->count) ? $item->comments->count : 0,
                        //"title" => isset($item->message) ? $item->message : false,
                        "text" => isset($item->description) ? $item->description : false,
                        "picture" => isset($item->picture) ? $item->picture : false
                    );

                    break;

                default:

                    echo "unknow message type --> ".$item->type."\r\n";
                    break;
            }

            if ($data["likes"] > 0 and isset($item->likes->data)) {

                $data["whoLikes"] = $item->likes->data;
            }
            
            if ($data["comments"] > 0 and isset($item->comments->data)) {

                $data["msgComments"] = $item->comments->data;
            }
            
            self::$_message->setData($data);

            $data = array_merge(array("src" => $src), self::$_message->getData());
            self::$_message->setData($data);

            if (self::$_message->isValid()) {

                self::$_message->getRemoteId();
                $msgIds[] = $messageMapper->save(self::$_message);
            }
        }

        if (self::$_message instanceof Message_Data and count($msgIds) > 0) {

           /***
            * Borrar mensajes eliminados en facebook
            */
            $where = array(
                "where" => array(
                    //"messages.owner" => self::$_message->getOwner(),
                    "messages.id >" => $msgIds[count($msgIds)-1],
                    "idup" => self::$_message->getIdUP()
                ),

                "where_not_in" => array(
                    "messages.id" => $msgIds
                ),

                "like" => array(
                    "data" => 's:3:"src";s:'.strlen($src).':"'.$src.'";'
                )
            );

            $messages = $messageMapper->customQuery($where);

            foreach ($messages as $msg) {

                if ( defined('CONSOLE')) {

                    echo "Deleting facebook message with id: ".$msg->getId()." and remoteId: ".$msg->getRemoteId()."<br />\r\n";
                }

                $messageMapper->delete($msg);
            }
        }
 
        return self::$_message;
    }
}