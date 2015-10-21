function drawCombo(td,theColumn,currentData,comboId) {
	var formater=theColumn["formater"];
	var theCombo=$('<select/>');
	$(theCombo).attr("identifier",comboId);
	var options=formater["options"].split(',');
	for(var o=0; o<options.length; o++) {
		var parts=$.trim(options[o]).split(":");
		var option=$('<option/>');
		if (parts[0]==currentData)
			$(option).prop('selected', true);
		option.attr('value',parts[0]).html(parts[1]).appendTo(theCombo);
	}
	
	if (formater['events']) {
		var events=formater["events"].split(',');
		for(var e=0; e<events.length; e++) {
			var parts=$.trim(events[e]).split(":");
			var theEvent=parts[0];
			var theMethod=parts[1];
			var theFunction=eval("window['"+theMethod+"']");
			if (typeof(theFunction)!="undefined" ) {
				$(theCombo).bind(theEvent,function(event) {
					event=event || {};
					event.parentRow=$(this).parent().parent();
					theFunction(this,event);
				});
			}
		}
	}
	$(td).html('');
	$(theCombo).appendTo(td);
}


var oLanguage= {
    "sLengthMenu": "Mostrar _MENU_ registros por pagina",
    "sSearch": "Buscar",
    "oPaginate": {
    	"sFirst":"Primer Pag.",
    	"sLast":"Ultima Pag.",
    	"sNext":"Siguiente",
    	"sPrevious":"Anterior",
    },
    "sZeroRecords": "No hay informacion",
    "sProcessing": "Procesando",
    "sLoadingRecords": "Cargando informacion",
    "sEmptyTable": "No hay informacion para mostrar",
    "sInfo": "Mostrando _START_ a _END_ de _TOTAL_ registros",
    "sInfoEmpty": "Mostrando 0 a 0 de 0 registros",
    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)"
}