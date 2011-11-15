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

$.publish.twitter = {
	"textarea" : null,
	"caracterCounter" : null,

	init : function () {

		this.textarea = $("#msgs > form textarea[name=tw_content]");
		if(this.textarea.length == 0) {
			return false;
		}

		this.caracterCounter = $("#tw_cnum");
		if(this.caracterCounter.length == 0) {
			return false;
		}

		this.textarea.keyup(function () {

			$.publish.twitter.update();
		});
		
		this.update();
	},

	update : function () {

		this.caracterCounter.text(this.textarea.attr("value").length);
	}
}

$(document).ready(function () {
	
	$.publish.twitter.init();
});