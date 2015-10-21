var nombre = '';
$(document.body).ready(function() {
	$(".page-content").mCustomScrollbar("update");
	nombre = $('#nombre').val();
	$('#nombre').bind('keyup',doNombreChange);
});

function setMode(mode,value) {
	var btn = $('#'+mode+'_btn');
	if (modes[mode] != value) {
		switch (mode) {
			case 'add':
				if (value)
					$(btn).bind('click',function() {doAddRol()});
				else
					$(btn).unbind('click');
				break;
			case 'upd':
				if (value)
					$(btn).bind('click',function() {doUpdRol()});
				else
					$(btn).unbind('click');
				break;
			case 'del':
				if (value)
					$(btn).bind('click',function() {doDelRol()});
				else
					$(btn).unbind('click');
				break;
		}
		modes[mode]=value;
	}
	if (value)
		$(btn).removeClass('disabled');
	else
		$(btn).addClass('disabled');
}

function setMode2(mode,value) {
	var btn = $('#'+mode+'_btn2');
	if (modes[mode] != value) {
		switch (mode) {
			case 'add':
				if (value)
					$(btn).bind('click',function() {doAddRul()});
				else
					$(btn).unbind('click');
				break;
			case 'upd':
				if (value)
					$(btn).bind('click',function() {doUpdRul()});
				else
					$(btn).unbind('click');
				break;
			case 'del':
				if (value)
					$(btn).bind('click',function() {doDelRul()});
				else
					$(btn).unbind('click');
				break;
		}
		modes[mode]=value;
	}
	if (value)
		$(btn).removeClass('disabled');
	else
		$(btn).addClass('disabled');
}

function cbClicked() {
	var actionsRow=$("#actions");
	var defaultsRow=$("#defaults");

	if ($(actionsRow).length>0 && $(defaultsRow).length>0) {
		var cells1=$(actionsRow).children();
		var cells2=$(defaultsRow).children();

		var cb1=$(cells1[1]).children(':first');
		var cb2=$(cells2[1]).children(':first');

	    var enabled = $(cb2).is(':checked');

		var cb1=$(cells1[2]).children(':first');
		var cb2=$(cells2[2]).children(':first');
		var shouldDisable=!enabled || !$(cb1).is(':checked');
	    $(cb2).prop("disabled",shouldDisable);
	    
		var cb1=$(cells1[3]).children(':first');
		var cb2=$(cells2[3]).children(':first');
		var shouldDisable=!enabled || !$(cb1).is(':checked');
	    $(cb2).prop("disabled",shouldDisable);

		var cb1=$(cells1[4]).children(':first');
		var cb2=$(cells2[4]).children(':first');
		var shouldDisable=!enabled || !$(cb1).is(':checked');
	    $(cb2).prop("disabled",shouldDisable);

		var cb1=$(cells1[5]).children(':first');
		var cb2=$(cells2[5]).children(':first');
		var shouldDisable=!enabled || !$(cb1).is(':checked');
	    $(cb2).prop("disabled",shouldDisable);
	}
}


function doAddRol(e) {
	$('#submitted').val('1');
	doSubmit();
}

function doUpdRol(e) {
	$('#submitted').val('2');
	doSubmit();
}

function doDelRol(e) {
	if (confirm("¿Esta seguro que desea eliminar el rol seleccionado?\n\nNOTA: los roles que estan siendo utilizados por\nal menos un usuario no seran eliminados.")) {
		$('#submitted').val('3');
		doSubmit();
	}
}

function doAddRul(e) {
	if (confirm("¿Esta seguro que desea agregar una nueva regla?\n\nIMPORTANTE: si no ha establecido un valor por defecto\ndebera hacerlo manualmente rol por rol.")) {
		$('#submitted').val('4');
	    doSubmit();
	}
}

function doUpdRul(e) {
	if (confirm("¿Esta seguro que desea modificar la regla seleccionada?\n\nIMPORTANTE: si ha agregado una nueva accion a la regla debera\nactivarla manualmente para cada rol de usuario\n\nIMPORTANTE: si ha quitado una accion de la regla, la misma\nsera quitada automagicamente de todos los roles de\nusuario existentes\n\nADVERTENCIA: si quita una accion de la regla compruebe que\nno ha sido utilizada en el codigo fuente, de lo contrario parte\ndel codigo podria quedar inaccesible")) {
		$('#submitted').val('5');
	    doSubmit();
	}
}

function doDelRul(e) {
	if (confirm("¿Esta seguro que desea eliminar la regla seleccionada?\n\nIMPORTANTE: si elimina una regla la misma sera quitada\nautomagicamente de todos los roles de usuario existentes\n\nADVERTENCIA: si elimina una regla compruebe que no ha sido\nutilizada en el codigo fuente, de lo contrario parte del codigo\npodria quedar inaccesible")) {
		$('#submitted').val('6');
	    doSubmit();
	}
}

function doSubmit() {
	var values = [];
	for (var i in rules) { values.push(i+'='+rules[i]); }
	$('#values').val(values.join(';'));
	$('#TheForm').submit();
	return false;
}

function doRolClick(cb) {
	var val = cb.value.split(':');
	var rule = val[0], action = parseInt(val[1]);
	rules[rule] = cb.checked ? rules[rule] | action : rules[rule] & ~action;
	if (action == 1) { doActiveChange(cb.parentNode.parentNode.parentNode, cb.checked); }
}

function doActiveChange(row,active,level) {
	var id = row.id.split(':')
	var rule = id[1]
	var action = 1
	var td = $(row).children(':first').next();
	while ($(td)[0]) {
		var div = $(td).children(':first')
		var cb = div ? $(div).children(':first') : null;
	    if (cb && $(cb).is("input")) {
	    	rules[rule] = active ? rules[rule] | action : 0;
	    	$(cb).prop('checked',active ? rules[rule] & action : false);
	    	action > 1 && ($(cb).prop("disabled",!active));
	    }
	    action *= 2;
	    td = $(td).next();
	}
	row = $(row).next()[0];
	level = typeof level == 'undefined' ? parseInt(id[0]) : level;
	row && parseInt(row.id.split(':')[0]) > level && doActiveChange(row,active,level);
}

var modes={add:false,upd:false,del:false};
var modes2={add:false,upd:false,del:false};

