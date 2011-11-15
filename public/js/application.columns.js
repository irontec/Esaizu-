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

$.columns = {
	tipos : null,
	bloquestTipo : null,
	bloques : null,

	init: function () {

		//Nueva columna
		this.tipos = $("input[name=type]");
		if (this.tipos.length > 0) {

			this.tipos.click(this.switchType);			
			this.tipos.filter(":checked").click();
		}

		//editar columnas
		this.bloques = $("#msgs form ul");
		if (this.bloques.length > 0) {

			this.bloques.find("h3").css("cursor","pointer").click($.columns.switchBloque);
		}
	},

	switchType : function (obj) {

		var cNameToShow = $(this).attr("value");
		var cNameToHide = cNameToShow == "standard" ? "search" : "standard";

		$("form div."+cNameToShow).show();
		$("form div."+cNameToHide).hide();
	},
	/**
	 * Muestra los campos que corresponden al plugin seleccionado
	 * y oculta el resto
	 */
	switchBloque : function () {

		$(this).blur();

		var cSpan = $(this).children("span:visible")

		if( cSpan.length > 0) {

			$.columns.bloques.find("h3 span").show();
			cSpan.hide();

			$.columns.bloques.each(function () {

				$(this).children("li").not(".account-type").hide();
			});

			$(this).parents("ul").children("li").not(".account-type").show();
		}
	}
}

$(document).ready(function () {

	$.columns.init();
});