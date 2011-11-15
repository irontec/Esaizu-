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

$.app = {
	allColumns : null,

	/*** Columnas standard ***/
	columns : null,
	/*** Columnas del tipo busqueda ***/
	searchColumns: null,
	searchResultTemplate : null,
	searchSinceId : true,

	offsetTop : 0,
	columnWidth : null,
	wrapper : null,
	paginator : null,

	ajaxRequests : new Array(),
	baseUrl : null,
	minimizePrototype : null,
	init: function () {

		//cachear la url base
		this.baseUrl = $("base").attr("href");

		if (this.baseUrl == null) {

			return false;
		}

		//cachear las columnas
		this.allColumns = $("#msgs > div.wrapper > div.column");
		this.columns = this.allColumns.not(".search");
		if(!this.columns.length == 1) {
			return;
		}

		this.searchColumns = this.allColumns.filter(".search");

		this.columns.each(function () {

			if ($.app.offsetTop == 0) {

				$.app.offsetTop = $(this).offset().top;
			}
		});

		$(window).resize(this.checkHeight).load(this.checkHeight)

		this.wrapper = $("#msgs > div.wrapper");
		this.columnWidth = this.wrapper.children("div.column:eq(0)").width() +20;
		this.wrapper.css("width" , this.columnWidth * this.allColumns.length + 20);
		this.paginator = $("#footer > div.pagination > a");
		this.paginator.click(this.pagination);

		/*** Load more ***/
		$("center a.more").click(this.loadMore);

		/*** Minimize ***/
		this.minimizePrototype = $("#sidebar li.prototype");
		$("#msgs div.column a.minim").not(".propiedades").click(this.minimize);

		/*** Restore ***/
		$("#sidebar li.minimized").click(this.restore);

		/*** Check context ***/
		$($("#footer > div.pagination a:visible").get(0)).addClass("selected");

		if ($("#sidebar li.minimized").not(".prototype").length > 0) {

			$("#sidebar li.separator").removeClass("hidden");
		}

		/*** Actualizar los "publicado hace x minutos" ***/
		setInterval("$.app.updatePublishDate()", 5 * 60 * 1000);

		/*** Actualizar los mensajes no leidos de las columnas minimizadas ***/
		setInterval("$.app.checkUnread()", 11 * 60 * 1000);

		/*** Actualizar el contenido de las columnas ***/
		setInterval("$.app.refresh()", 60000);

		/*** Ver comentarios/replicas/me gusta/etc del los bloques expand ***/
		this.columns.find("div.expand div.options a").click($.app.expand);

		/*** Actualizar las columnas del tipo buscador y definir el template de resultados ***/
		this.searchResultTemplate = '\
		<div id="##id##" class="Twitter">\
		  <a href="http://www.twitter.com/##from_user##" target="_blank">\
		    <img style="float: left; padding: 2px;" alt=" ##from_user##" src="##profile_image_url##">\
		  </a>\
		  <p>##text##</p>\
		  <p>\
		    <a href="http://www.twitter.com//##from_user##" target="_blank">\
		      ##from_user##\
		    </a>\
		    hace\
		    <span rel="##timestamp##" class="time">\
		      ##time##\
		    </span>\
		  </p>\
		</div>\
		';

		$.app.search();
		setInterval("$.app.search()", 30 * 1000);
	},
	/*** Paginación ***/
	pagination : function () {

		$(this).blur();

		if ($(this).hasClass("selected")) {

			return;
		}

		var columns = $.app.allColumns.filter(":visible");
		var rel = $.trim($(this).attr("class"));
		var i= 0;

		for (var kont = 0; kont < columns.length; kont++) {

			var currentColumn = $(columns.get(kont));

			if (currentColumn.attr("rel") == rel) {

				break;
			}
			i++;
		}

		var desplazamiento = $.app.columnWidth * i * -1;
		$.app.wrapper.animate({left : desplazamiento},  300, "swing");

		$.app.paginator.filter(".selected").removeClass("selected");
		$(this).addClass("selected");
	},

	/*** petición ajax encargada de actualizar contenido ***/
	refresh : function () {

		$.app.columns.each(function () {

			var tmp = $(this).find("div.overview").children("div:eq(0)"); 

			var data = {
					"msgId" : tmp.attr("id"),
					"msgTimestamp" : tmp.attr("rel"),
					"columnId" : tmp.parents("div.column").attr("rel"),
					"type" : "update",
					"launcher" : $(this),
					"identifier" : new Date().getTime(),
			};

			/*** Valor por defecto para las columnas que aún no tienen ningún mensaje ***/
			if(!data.msgId) {

				data.msgId = 1;
			}

			$.app.ajaxRequests[$.app.ajaxRequests.length] = data; 

			jQuery.ajax({
				"url" : $.app.baseUrl + "app/index/" + data.columnId + "/" + data.msgId,
				"dataType" : "json",
				"data" : { 
					"type" : data.type,
					"identifier" : data.identifier,
					"timestamp" : data.msgTimestamp,
				},
				"success" : $.app.feedColumn
			});
		});
	},

	/*** Botón minimize ***/
	minimize : function () {

		var column = $(this).parents(".column");
		var cId = column.attr("rel");

		//Comprobar si la columna ya esta minimizada, o esta en proceso de hacerlo
		if( $("#sidebar a." + cId ).length > 0 ) {

			return false;
		}

		$.get($(this).attr("href"));

		var prototype = $.app.minimizePrototype.clone();
		var cTitle = $.trim(column.children("div.header").text());

		prototype.children("a").attr("href", prototype.children("a").attr("href") + "/" +cId);
		prototype.children("a").attr("title", cTitle);
		prototype.children("a").attr("class", cId);
		prototype.find("p.columnName").text(cTitle);
		prototype.removeClass("prototype").css("display","none").removeClass("hidden");
		prototype.click(this.restore);

		$.app.minimizePrototype.parent().append(prototype);

		prototype.fadeIn(700);
		column.effect("transfer", { to: prototype }, 800);
		column.fadeOut(300);

		$("#footer a."+cId).addClass("hidden");
		$("#sidebar li.separator").removeClass("hidden");

		return false;
	},

	/*** Botón restaurar columna ***/
	restore : function () {

		$.get($(this).children("a").attr("href"));

		var link = $(this).children("a");
		var tmp = link.attr("href").split("/");
		var cId = tmp[tmp.length -1];

		var column = $("#msgs div.column[rel="+cId+"]");
		column.css("display","none").removeClass("hidden");

		var li = $(this);

		column.fadeIn(700);

		li.effect("transfer", { to: column }, 800, function () {

			var cId = $(this).children("a").attr("class");
			var column = $("div.column[rel="+cId+"]");
			if( parseInt(column.position().left) < 30) {

				$("#footer a."+cId).click();
			}
			$(this).remove();
		});

		li.fadeOut(700);

		$("#footer a."+cId).removeClass("hidden");

		/*** 
		 * LLegados a este punto el li aún no ha sido eliminado (Esta en medio de la animación fadeOut).
		 * Por eso, en caso de que haya solamente un elemento podemos ocultar el separador
		 ***/
		if ($("#sidebar li.minimized").not(".prototype").length == 1) {

			$("#sidebar li.separator").addClass("hidden");
		}

		return false;
	},
	/*** Botón de ver más ***/
	loadMore : function () {

		$(this).blur();
		$(this).addClass("hidden");
		$(this).next().removeClass("hidden");

		var tmp = $(this).parents("div.overview").children("div:last"); 

		var data = {
				"msgId" : tmp.attr("id"),
				"columnId" : tmp.parents("div.column").attr("rel"),
				"type" : "more",
				"launcher" : $(this),
				"identifier" : new Date().getTime(),
				"msgTimestamp" : tmp.attr("rel")
		};

		$.app.ajaxRequests[$.app.ajaxRequests.length] = data; 

		jQuery.ajax({
			"url" : $.app.baseUrl + "app/index/" + data.columnId + "/0/" + data.msgId,
			"dataType" : "json",
			"data" : { 
				"type" : data.type,
				"identifier" : data.identifier,
				"timestamp" : data.msgTimestamp,
			},
			"success" : $.app.feedColumn
		});
	},

	/*** ver bloques expand de los mensajes (Comentarios, etc) ***/
	expand : function () {

		var rel = $(this).blur().attr("rel");
		var uls = $(this).parents("div.expand").find("ul");

		uls.filter("."+rel).toggle();
		uls.not("."+rel).hide();
		return false;
	},

	/*** gestionar respuestas ajax de nuevo contenido ***/
	feedColumn : function (resp) {

		for ( var storedRequest in $.app.ajaxRequests ) {

			request = $.app.ajaxRequests[storedRequest];

			if (request.identifier == resp.identifier && request.columnId == resp.column && request.type == resp.type) {

				if (resp.messageNum > 0) {

					if (resp.type == "more" ) {

						request.launcher.parent().before(resp.html);
						request.launcher.next().addClass("hidden");

						if(resp.messagesPerColumn == resp.messageNum) {

							request.launcher.removeClass("hidden");
						}

						var contenedor = request.launcher.parent().parent();
						var msgNum = contenedor.children("div").length;
						var newMsgsPos = msgNum - resp.messageNum -1;
						
						contenedor.children("div:gt("+ newMsgsPos +")").css("background-color","white")
						.each(function () {

							$(this).animate({
								backgroundColor: "#FAF3A5",
							}, 1000, function () {

								$(this).animate({
									backgroundColor: "#ffffff",
								}, 800);
							});
						});

					} else {

						var contenedor = request.launcher.find("div.overview"); 

						if (contenedor.children("div").length == 0) {

							contenedor.children("center").children().toggleClass("hidden");
						}

						contenedor.prepend(resp.html);

						contenedor.children("div:lt("+ resp.messageNum +")").css("background-color","white")
						.each(function () {

							$(this).addClass("unread").animate({

								backgroundColor: "#FAF6CF",

							}, 1000, function () {

								if (resp.type == "search" ) {

									$(this).animate({
										backgroundColor: "#FFFFFF",
									}, 1800);
								}
							});
						});

						/*** Hacer limpieza de mensajes viejos ***/
						$.app.columns.each(function () {

							$(this).find("div.overview > div:gt(20)").remove();
						});

						$.app.searchColumns.each(function () {

							$(this).find("div.overview > div:gt(60)").remove();
						});
					}
				}

				delete $.app.ajaxRequests[storedRequest];
			}
		}
	},

	setCookie : function (c_name,value,exdays)
	{
		var exdate=new Date();
		exdate.setDate(exdate.getDate() + exdays);
		var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
		document.cookie=c_name + "=" + c_value;
	},
	
	/*** Control del height de las columnas para el scroll javascript ***/
	checkHeight : function (column) {

		if($("#msgs").find("div.column:visible").length == 0) {

			 return;
		}

		var newWindowHeight = ($(window).height() - $("#msgs").offset().top - $("#footer").height() - 10);
		var newViewportHeight = newWindowHeight - 90;

		$("body > div.app").css("height", newWindowHeight);

		/*** Set a cookie with current height values ***/
		$.app.setCookie("windowHeight", parseInt(newWindowHeight), 60);
		$.app.setCookie("viewportHeight", parseInt(newViewportHeight), 60);

		$.app.columns.each(function () {

			if (typeof(column) == "object" || column == $(this).attr("rel")) {

				$(this).find("div.body > div.viewport").each(function () {

					$(this).css({
						"height": newViewportHeight
					});
				});
			}
		});
		
		$.app.searchColumns.each(function () {

			if (typeof(column) == "object" || column == $(this).attr("rel")) {

				$(this).find("div.body > div.viewport").each(function () {

					$(this).css({
						"height": newViewportHeight
					});
				});
			}
		});
	},

	/*** 
	 * Comprobar el múmero de mensajes no leidos en las columnas minimizadas 
	 * Y restaurar los colores de las columnas resaltadas tras x minutos
	 * ***/
	checkUnread : function () {

		var lis = $("#sidebar li.minimized").not(".prototype");
		var ColumnIds = new Array();

		lis.each(function () {

			var uri = $(this).children("a").attr("href").split("/");
			uri = uri[uri.length - 1];

			ColumnIds[ColumnIds.length] = uri;
		});

		if (ColumnIds.length > 0) {

			$.post($.app.baseUrl + "app/checkUnread/", {"cIds" : ColumnIds}, function (resp) {

				for (var id in resp) {

					if (resp[id] > 0 ) {

						$("#sidebar li.minimized a."+id+" span")
						.removeClass("hidden").html(resp[id]);
					}
				}

			}, "json");
		}

		$.app.columns.filter(":visible").find("div.unread span.time").each(function () {

			var node = $(this);
			var parent = $($(this).parents("div.unread"));

			var t = new Date( parseInt(node.attr("rel"))*1000 );
			var now = new Date();

			var timeDiff = parseInt((now.getTime() - t.getTime()) / 1000);
			
			if( timeDiff >= 1) {

				parent.animate({
					backgroundColor: "#FFFFFF",
				}, 1000);
			}
		});
	},

	/*** Actualizar los mensajes de publicado hace x días ***/
	updatePublishDate : function (root) {

		if (root == undefined || root == "undefined") {

			root = $("#msgs");
		}

		root.find("span.time").each(function () {

			var newValue = $.app.dateDiff(parseInt($(this).attr("rel"))*1000);
			$(this).html(newValue);
		});
	},

	dateDiff : function (timestamp) {

		var t = new Date(timestamp);
		var now = new Date();

		var $timeDiff = parseInt((now.getTime() - t.getTime()) / 1000);
		
		var $oneMonth = 60*60*24*31;
		var $oneDay = 60*60*24;
		var $oneHour = 60*60;

	    if ($timeDiff > $oneMonth) {

	        return "Más de un mes";

	    } else if ($timeDiff > $oneDay) {

	        $days = parseInt(($timeDiff/$oneDay));

	        if ($days == 1) {

	            return $days + " dia";

	        } else {

	        	return $days + " días";
	        }

	    } else if ($timeDiff > $oneHour) {

	        $hours = parseInt(($timeDiff/$oneHour));

	        if ($hours == 1) {

	            return $hours + " hora";

	        } else {

	        	return $hours + " horas";
	        }

	    } else {

	       $minutes = parseInt(($timeDiff/60));

	       if ($minutes == 1) {

	            return $minutes + " minuto";

	       } else {

	    	   return $minutes + " minutos";
	       }
	    }
	},
	search : function() {

		$.app.searchColumns.each(function () {

			var data = {
					"msgId" : null,
					"columnId" : $(this).attr("id"),
					"type" : "search",
					"launcher" : $(this),
					"identifier" : new Date().getTime(),
					"msgTimestamp" : new Date().getTime() / 1000
			};

			$.app.ajaxRequests[$.app.ajaxRequests.length] = data; 

			var querySegments = $(this).attr("id").split("|");
			var query = {
				q : ""
			}
			
			var timeline = {
				q :	""
			}

			for (var $i = 0; $i < querySegments.length; $i++) {

				var subSegment = querySegments[$i].split("=");

				switch (subSegment[0]) {

					case "text":
					case "hashtags":

						query.q = query.q + subSegment[1].replace("#","%23");
						break;
						
					case "user":
						
						var url = "http://twitter.com/status/user_timeline/##username##.json?count=10";
						if (subSegment[1] != "") {
							timeline.q = url.replace("##username##",subSegment[1]);
						}

						break;

					case "language":

						query.lang = subSegment[1];
						break;					
				} 
			}

			if(timeline.q != "") {

		        $.ajax({
	                type: "GET",
	                url: timeline.q,
	                success: function(response) {

		        		return $.app.parseTimeLineResults(response, query, data);
	                },
	                dataType: "jsonp",
	                error: function(XMLHttpRequest, textStatus, errorThrown){
	                	//console.log("error.");
	                }
		        });
			}

			if(query.q == "") {

				return;
			}

			var lastMsg = $(this).find("div.overview > div:first");

			if (lastMsg.length > 0 && $.app.searchSinceId) {

				query.since_id = (parseInt(lastMsg.attr("id"))+1);

			} else if (lastMsg.length > 0) {

				$.app.searchSinceId = true;
			}

	        $.ajax({
                type: "GET",
                url: "http://search.twitter.com/search.json",
                data: query,
                success: function(response) {

	        		return $.app.parseSearchResults(response, query, data);
                },
                dataType: "jsonp",
                error: function(XMLHttpRequest, textStatus, errorThrown){
                	//console.log("error.");
                }
	        });
		});
	},

	parseTimeLineResults : function (response, query, data) {

		if(response.error == "since date or since_id is too old") {

			//fix para el error "since date or since_id is too old"
			$.app.searchSinceId = false;
		}

    	if( typeof (response.length) == 0) {

    		return;
    	}

    	var msgs = new Array();

    	if(query.since_id == undefined || query.since_id == "undefined") {

			query.since_id = data.launcher.find("div.overview > div:first").attr("id");
		}

    	for (var i = 0; i < response.length ; i++) {

    		var result = response[i];

    		if (parseInt(query.since_id) >= parseInt(result.id)) {

    			continue;
    		}

    		var msg = $.app.searchResultTemplate;
    		var createdAt = new Date(result.created_at.substring(4)).getTime();

    		msg = msg.replace("##id##",result.id_str);
    		msg = msg.replace(/##from_user##/g,result.user.screen_name);
    		msg = msg.replace("##profile_image_url##",result.user.profile_image_url);
    		msg = msg.replace("##text##", $.app.linkWrap(result.text));
    		msg = msg.replace("##timestamp##", (createdAt / 1000));
    		msg = msg.replace("##time##", $.app.dateDiff(createdAt));
    		msgs[msgs.length] = msg;
    	}

    	if (msgs.length > 0) {

    		var content = {
				"column": data.columnId,
				"type":"search",
				"identifier": data.identifier,
				"messagesPerColumn":20,
				"messageNum":msgs.length,
				"html": msgs.join(" ")
    		}

    		$.app.feedColumn(content);                		
    	}
    	
    	$.app.updatePublishDate(data.launcher);
	},

	parseSearchResults : function (response, query, data) {

		if(response.error == "since date or since_id is too old") {

			//fix para el error "since date or since_id is too old"
			$.app.searchSinceId = false;
		}

    	if( typeof (response.results) != "object") {

    		return;
    	}

    	var msgs = new Array();

    	if(query.since_id == undefined || query.since_id == "undefined") {

			query.since_id = data.launcher.find("div.overview > div:first").attr("id");
		}

    	for (var i = 0; i < response.results.length ; i++) {

    		var result = response.results[i];

    		if (parseInt(query.since_id) >= parseInt(result.id)) {

    			continue;
    		}

    		var msg = $.app.searchResultTemplate;
    		var createdAt = new Date(result.created_at.substring(5)).getTime();

    		msg = msg.replace("##id##",result.id_str);
    		msg = msg.replace(/##from_user##/g,result.from_user);
    		msg = msg.replace("##profile_image_url##",result.profile_image_url);
    		msg = msg.replace("##text##", $.app.linkWrap(result.text));
    		msg = msg.replace("##timestamp##", (createdAt / 1000));
    		msg = msg.replace("##time##", $.app.dateDiff(createdAt));
    		msgs[msgs.length] = msg;
    	}

    	if (msgs.length > 0) {

    		var content = {
				"column": data.columnId,
				"type":"search",
				"identifier": data.identifier,
				"messagesPerColumn":20,
				"messageNum":msgs.length,
				"html": msgs.join(" ")
    		}

    		$.app.feedColumn(content);                		
    	}
    	
    	$.app.updatePublishDate(data.launcher);
	},

	linkWrap : function (text) {

		return text.replace(/(https?:\/\/(\S)*\b)/ig, '<a target="_blank" href="$1">$1</a>');
	}
}

$(document).ready(function () {

	$.app.init();
});