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

$.publish = {
	form : null,
	switcher:null,
	bloques:null,
	referencias: null,
	init: function () {

		//cachear el formulario
		this.form = $("#msgs form");
			
		if(!this.form.length == 1) {
			return;
		}

		//cachear el <select> de selección de plugins
		this.switcher = this.form.find("select[name=publishIn]:eq(0)");
		if(!this.switcher.length == 1) {
			return;
		}

		//cachear los checkbox de referencias
		this.referencias = this.form.find("*[name='referenceIn[]']");

		/**
		 * Cachear los bloques del formulario que corresponden a cada plugin
		 */
		this.bloques = this.form.find("div.accounts[rel]");
		if(!this.bloques.length == 1) {
			return;
		}

		/**
		 * Mostrar los campos que corresponden al plugin seleccionado, ocultar el resto
		 * y hacer un bind del evento a larzarse cada vez que el plugin seleccionado
		 * cambie
		 */ 
		this.switchBloque();
		this.switcher.change(function () {
			$.publish.switchBloque();

			$.publish.bloques.find("p.error").each(function () {

				$(this).text("");
				
			});
		});

		//Activar el boton de enviar formulario
		this.form.find("input[type=submit]").removeAttr("disabled");

		this.initUI();
	},

	initUI : function () {

		$.datepicker.setDefaults(
			$.parseJSON($("#datepickerOptions").attr("value"))
		);

		$("#dia_programado").datepicker({ 
			minDate: 0, 
			//maxDate: "+6M +10D", 
			altField: "#dia_programado_dbformat",
			altFormat: "yy-mm-dd",
			showAnim: "blind"
		}); 
	},
	/**
	 * Muestra los campos que corresponden al plugin seleccionado
	 * y oculta el resto
	 */
	switchBloque : function () {

		/***
		 * Bloques
		 */
		this.bloques.filter(":visible").hide().find("select,input,textarea").attr("disabled");
		var idBloqueActivo = this.switcher.children("option:selected").attr("rel");
		var idUserPluginId = this.switcher.children("option:selected").attr("value");
		var bloque = this.bloques.filter("*[rel="+idBloqueActivo+"]");
		bloque.show().find("select,input,textarea").removeAttr("disabled");
		
		/***
		 * Elementos internos de cada bloque con un attributo rel que haga refencias a una cuenta en concreto 
		 */
		var idCuentaActiva = this.switcher.children("option:selected").attr("value");

		bloque.find("*[rel]").filter("*[rel!="+idCuentaActiva+"]").attr("disabled","disabled").hide();
		bloque.find("*[rel]").filter("*[rel="+idCuentaActiva+"]").removeAttr("disabled").show();

		/***
		 * Referencias
		 */
		
		this.referencias.filter("*[value="+idUserPluginId+"]").attr("disabled","disabled").parent().hide();
		this.referencias.filter("*[value!="+idUserPluginId+"]").removeAttr("disabled").parent().show();
	}
}

$(document).ready(function () {

	$.publish.init();
});