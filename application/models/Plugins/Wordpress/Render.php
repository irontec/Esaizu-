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
 * Clase encargada de dibujar el contenido del plugin o
 * devolver las vistas con las que renderizarlo
 */
Class Plugins_Wordpress_Render extends Common_Render
{
    /**
     * @return void
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Devuelve el formulario para la creación de una nueva identidad del tipo wordpress
     *
     * @return string
     */
    /* (non-PHPdoc)
     * @see application/models/Common/Common_Render#getNewAccountFormView()
     */
    public function getNewAccountFormView()
    {
        return "plugins/wordpress/new_account";
    }

    /**
     * Devuelve la vista para la publicación de contenido en una identidad wordpress.
     * Tambien añade las dependencias de javascript/css a el html final
     * @return string
     */
    /* (non-PHPdoc)
     * @see application/models/Common/Common_Render#getPublishFormView()
     */
    public function getPublishFormView()
    {
        $this->_ci->output->injectJs(array(
            "tiny_mce/tiny_mce.js",
            "application.publish.wordpress.js")
        );

        return "plugins/wordpress/publish";
    }

    /**
     * Indica si el plugin en cuestión es capaz de
     * referenciar mensajes de otros plugins
     * @return boolean
     */
    /* (non-PHPdoc)
     * @see application/models/Common/Common_Render#showReferenceButton()
     */
    public function showReferenceButton()
    {
          return false;
    }

    /**
     * Devuelve el html del mensaje ya parseado
     * @param Message_Data $obj
     * @return string
     */
    /* (non-PHPdoc)
     * @see application/models/Common/Common_Render#messageBox($data, $return)
     */
    public function messageBox(Message_Data $obj, $return = false)
    {
        return $this->_ci->load->view("plugins/wordpress/message", array("message" => $obj), $return);
    }
}