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

$(document).ready(function () {

	if(typeof(tinyMCE) !== "object") {

		return false;
	}

	tinyMCE.init({
        // General options
        mode : "textareas",
        theme : "advanced",
        plugins : "pagebreak,insertdatetime,preview,fullscreen,xhtmlxtras,phpimage",
        editor_selector : "html",

        // Theme options
        theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect,forecolor",
        theme_advanced_buttons2 : "bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,phpimage,cleanup,code,|,insertdate,inserttime,preview,|,pagebreak,fullscreen",
        theme_advanced_buttons3 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,

        // Skin options
        skin : "o2k7",
        skin_variant : "silver",

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "js/template_list.js",
        external_link_list_url : "js/link_list.js",
        external_image_list_url : "js/image_list.js",
        media_external_list_url : "js/media_list.js",
	});
});

