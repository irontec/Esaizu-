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
 * Clase abstracta que define los metodos que han de implementar los renders de la aplicación.
 * @author Mikel Madariaga <mikel@irontec.com>
 */
abstract class Common_Render
{
    protected $_ci;

    function __construct()
    {
        // Get framework instance
        $this->_ci =& get_instance();
    }

     /**
     * Devuelve la ruta al formulario para la creación de una nueva identidad.
     * False en caso de no existir
     *
     * @return string | boolean
     */
    abstract public function getNewAccountFormView();
    
    /**
     * Devuelve la ruta al formulario de publicación.
     * También puede añadir las dependencias javascript/css a el html final mediante
     * los métodos injectJs y injectCss. Por ejemplo:
     * $this->_ci->output->injectJs(array(
     *      "application.publish.twitter.js")
     * );
     * En caso de que el plugin no incluya la funcionalidad de publicar, devolver false
     * @return string | boolean
     */
    abstract public function getPublishFormView();
    
    /**
     * Indica si el plugin en cuestión es capaz de
     * referenciar mensajes publicador por otros plugins
     * @return boolean
     */
    abstract public function showReferenceButton();
    
    /**
     * Devuelve un objetivo Message_Data y lo dibuja en pantalla
     * @param Message_Data $data
     * @param boolean $return
     * @return string
     */
    abstract public function messageBox(Message_Data $data, $return = false);
}
