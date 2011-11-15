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
class Plugins_Wordpress_DB extends Common_DB
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
     * @param array $post
     * @param integer $idUP
     * @return Message_Data
     */
    public static function save(array $post, $idUP)
    {
        $date = $post["dateCreated"];
        $publishDate = $date->year."-".$date->month."-".$date->day." ".$date->hour
        .":".$date->minute.":".$date->second;

        $data = array(
            "text" => $post["description"],
            "category" => $post["categories"],
            "tags" => $post["mt_keywords"],
            "comments" => $post["comments"],
            "fetched" => time()
        );

        $auth = Auth::get_instance();
        self::$_message  = New Message_Data();
        $messageMapper = Message_Mapper::get_instance();

        self::$_message->setRemoteId($post["postid"])
                ->setIdU($auth->getUserId())
                ->setIdUP($idUP);

        $existingMessages = $messageMapper->find(self::$_message);
        if (count($existingMessages) > 0) {

            self::$_message = $existingMessages[0];
        }

        self::$_message->setTitle($post["title"])
                ->setPublishDate($publishDate)
                ->setLink($post["link"])
                ->setData(serialize($data))
                ->setOwner("1");

        if (self::$_message->isValid()) {

            $id = $messageMapper->save(self::$_message);
            self::$_message->setId($id);
        }

        return self::$_message;
    }
}