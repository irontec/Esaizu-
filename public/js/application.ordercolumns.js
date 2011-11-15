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

$.ordercolumns = {
	nodes : null,	
	init: function () {

		this.nodes = $( "#sortable" ); 

		this.nodes.sortable().children("li").css("cursor","move");
		this.nodes.disableSelection();

		//$("form input[type=submit]").click($.ordercolumns.submit);
	},
	submit : function () {

		var order = new Array();

		$($.ordercolumns.nodes).find("li").each(function () {

			order[order.length] = $(this).find("input").attr("value");
			
		});

		return false;
	}
}

$(document).ready(function () {

	
	$.ordercolumns.init();
});