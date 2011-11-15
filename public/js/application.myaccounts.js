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

$.myAccounts = {
	plugins:null,
	accounts : null,
	init: function () {

		this.plugins = $("#account-list li.account-type");

		if(this.plugins.length == 0) {

			return;
		}

		this.plugins.css("cursor","pointer").click(this.toggle);

		this.accounts = $("#account-list li.account");

		if ($.cookie("activeNode")) {

			var hide = true;

		} else {

			var hide = false;
		}

		$("#account-list li").each(function () {

			if ($(this).hasClass("account-type") && $.trim($(this).text()) == $.cookie("activeNode")) {

				hide = false;

			} else if($(this).hasClass("account-type") && $(this).text() !=  $($.myAccounts.plugins[0]).text()) {

				hide = true;
			}

			if(hide && $(this).hasClass("account")) {
				$(this).hide();
			}
		});
		
		$($("#account-list li.account:visible").get(0)).prev().find("span.myAccounts").hide();
		$("#account-list li.account-type").click(this.rememberActiveBlock);
		
	},
	toggle : function () {

		$("#account-list > ul span.myAccounts").show();
		$(this).find("span.myAccounts").hide();

		var childrens = new Array();
		var liNodes;
		var next = $(this).next("li.account");

		while (true) {

			if(next.length > 0 && next.hasClass("account")) {

				childrens[childrens.length] = next;

			} else {

				break;
			}

			next = next.next("li");
		}

		if(childrens.length > 0) {

			$.myAccounts.accounts.hide();
		}

		$(childrens).each(function () {

			this.toggle();
		});
	},
	
	rememberActiveBlock : function () {

		var nodeText = $.trim($(this).text());
		$.cookie("activeNode", nodeText);
	}
}

$(document).ready(function () {

	$.myAccounts.init();
});