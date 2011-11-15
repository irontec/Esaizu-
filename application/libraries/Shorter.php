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
Class Shorter
{
    public static function short($url)
    {
        if (empty($url)) {
        
            return $url;
        }
        
        $ci =& get_instance();
        $ci->config->load('apikeys', TRUE);

        $conf = $ci->config->item('bitly', 'apikeys');

        if ($conf["user"] == "" or $conf["key"] == "") {
        
            return $url;
        }

        $temp = "http://api.bit.ly/v3/shorten?login=".$conf["user"]."&apiKey=".$conf["key"]."&uri=".$url."&format=txt";

        try {

            $data = file_get_contents($temp);

        } catch (Exception $e) {

            $data = $url;
        }

        return $data;
    }
}