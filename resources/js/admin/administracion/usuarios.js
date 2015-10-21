//function combo
function cambiarEstado(object,event) {

	var postData={
		method: "cambiarEstado",
		usuario: $(object).attr("identifier"),
		valor: $(object).val()
	}
	
	Application.callAdminAjax('usuarios',postData,"json",resultado)
}


function resultado(data) {
	if (data.error)
		alert(data.error);
}

function showComboUsuarios(td,theColumn,currentData,userId) {
	$(td).html('');
	if (userId==1) return;
	drawCombo(td,theColumn,currentData,userId)
}

function checkRoles() {
	var postData=$._forms['.block-content.controls'].postData;
	var usuario=postData.usuario;
	var email=postData.email;
	var password=postData.password;
	
	var pData={
		method:'validarUsuario',
		type:'json',
		usuario: usuario,
		email: email,
		password: password,
		referrer: document.location.href
	};
	var usuarioOK=true;
	var causaError="";
	$.ajax({
		url:Application.getLink('/admin/ajax/usuarios'),
		async: false,
		type:'post',
		data:pData,
		dataType:'json',
		success:function(data) {
			if (data.status=='error') {
				usuarioOK=false;
				causaError=data.message;
			}
		}
	});
	if (!usuarioOK) {
		alert(causaError);
		return false;
	}
	
	
	var roles=postData.rol || $('#rol').val(); //$('#rol').val();
	if (roles=='')  {
		alert('Debe seleccionar al menos un Rol de Usuario');
		return false;
	}
	var rolesSplit=roles; //.split(',');
	var roles=[];
	for (var i=0; i<rolesSplit.length;i++) {
		if (rolesSplit[i]!='') roles.push(rolesSplit[i]);
	}
	postData['rol']=roles.join(',');
	$._forms['.block-content.controls'].postData=postData;
	return true;
}

function processForm() {
	if (!checkRoles()) return;
	$('.row-form').each(function() {
		$(this).find('input[type=text]').each(function() {
			var fieldName=$(this).attr('name');
			if (fieldName!=null && fieldName!='redactor') {
				var nuevoCampo=$(this).clone().appendTo('#user-form');
				$(nuevoCampo).val($(this).val());
			}
		})
	});
	$('#user-form').submit();
}