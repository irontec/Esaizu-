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
$.accounts = {
	form : null,
	switcher:null,
	bloques:null,
	init: function () {

		//cachear el formulario
		this.form = $("form[action*='accounts/add']");
		if(!this.form.length == 1) {
			return;
		}

		//cachear el <select> de selección de plugins
		this.switcher = this.form.find("select[name=plugin]:eq(0)");
		if(!this.switcher.length == 1) {
			return;
		}

		/**
		 * Cachear los bloques del formulario que corresponden a cada plugin
		 */
		this.bloques = this.form.find("div.accountOptions[rel]");

		/**
		 * Mostrar los campos que corresponden al plugin seleccionado, ocultar el resto
		 * y hacer un bind del evento a larzarse cada vez que el plugin seleccionado
		 * cambie
		 */ 
		this.switchBloque();
		this.switcher.change(function () {
			$.accounts.switchBloque();
		});

		//Activar el boton de enviar formulario
		this.form.find("input[type=submit]").removeAttr("disabled");
	},
	/**
	 * Muestra los campos que corresponden al plugin seleccionado
	 * y oculta el resto
	 */
	switchBloque : function () {

		this.bloques.filter(":visible").hide().find("select,input,textarea").attr("disabled");
		
		var idBloqueActivo = this.switcher.attr("value");
		var bloque = this.bloques.filter("*[rel="+idBloqueActivo+"]");
		bloque.show().find("select,input,textarea").removeAttr("disabled");
	}
}

$(document).ready(function () {

	$.accounts.init();
});