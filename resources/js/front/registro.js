var postData;
$(document).ready(function () {
	$('#acepta-terminos').click(function() {
		if ($(this).prop('checked')) {
			$('#crear-cuenta').removeAttr('disabled');
		} else {
			$('#crear-cuenta').attr('disabled','disabled');
			
		}
	});
	$('#recuperar-password').click(function() {
		$('#forgotPassword .close').click();
		var emailAddress=$("#email-address").val();
		if ($.trim(emailAddress)=="") {
			showInfo('Debes indicar tu direcci&oacute;n de email',{level:severityLevel.WARNING});
			return false;
		}
		postData={
			method:"recuperarPassword",
			emailAddress: emailAddress
		};
		Application.callAjax('profiles',postData,'json',function(data) {
			if (data.status=="ok") {
				showInfo("Por favor, verifica tu casilla de correo. Recibiras instrucciones para proseguir con el restablecimiento de tu contrase&ntilde;a",{level:severityLevel.READY});
			} else {
				showInfo(data.mensaje,{level:severityLevel.WARNING,buttons:[
                    {
                    text:'Reintentar',
                    action:"$('.alert .close').click(); $('#forgotPassword').modal();"
                    }
				]});
			}
		});
	});
	
	$('#reset-password').click(function() {
		$('#resetPassword .close').click();
		var pass1=$.trim($('#pass1').val());
		var pass2=$.trim($('#pass2').val());
		if (pass1=="") {
			showInfo('No has ingresado la nueva contraseña.',{level:severityLevel.WARNING,buttons:[
	          {
	          text:'Reintentar',
	          action:"$('.alert .close').click(); $('#resetPassword').modal();"
	          }
			]});
			return;
			
		}
		if (pass1!=pass2) {
			showInfo('Las contraseñas no coinciden.',{level:severityLevel.WARNING,buttons:[
              {
              text:'Reintentar',
              action:"$('.alert .close').click(); $('#resetPassword').modal();"
              }
			]});
			return;
		}
	
		var postData={
			method:'resetPassword',
			newPassword:pass1,
			emailAddress:$(this).attr("data-user-email"),
			hash:$(this).attr("data-user-hash"),
		};
		Application.callAjax('profiles',postData,'json',function() {
			showInfo('Tu contrase&ntilde;a ha sido modificada exitosamente.',{level:severityLevel.READY,buttons:[
              {
              text:'Ingresar',
              action:"$('.alert .close').click(); $('#loginModal').modal();"
              }
			]});
		});
	});
	
	$('#crear-cuenta').click(function() {
		postData={
			method:"crearCuenta"
		};
		if (!validForm()) {
			return false;
		}
		$('#signupform').find('input.required').each(function() {
			var value=$.trim($(this).val());
			var name=$.trim($(this).attr('name'));
			postData[name]=value;
			$(this).attr("disabled","disabled");
		});
		$('#crear-cuenta').css('display','none');
		
		
        $.ajax({
    		url:Application.getLink('/ajax/registro'),
    		async: true,
    		type:'post',
    		data:postData,
    		dataType:'json',
            success: function(data) {
            	if (data.status=="error") {
            		showError(data.message);
//            		showError('<div style="text-align: left;">Error:<br/>'+data.message+'<br/><br/>Por favor verifique los datos ingresados y vuelva a intentarlo</div>');
            		$('#crear-cuenta').css('display','');
            		errores=true;
            		$('#signupform').find('input.required').each(function() {
            			$(this).removeAttr("disabled");
            		});
            		return;
            	}
            	$("#registro").modal('hide');
            	$("#checkEmail").modal();
            	$('#btn-crear-cuenta').css('display','none');
            },
            error:function(data) {
        		$('#signupform').find('input.required').each(function() {
        			$(this).removeAttr("disabled");
        		});
        		$('#crear-cuenta').css('display','');
            }
        });
		return false;
	});

	$('#crear-cuenta-empresa').click(function() {
		postData={
			method:"crearCuentaEmpresa"
		};
		if (!validForm(true)) {
			return false;
		}
		$('#signupform-empresa').find('input.required').each(function() {
			var value=$.trim($(this).val());
			var name=$.trim($(this).attr('name'));
			postData[name]=value;
			$(this).attr("disabled","disabled");
		});
		$('#crear-cuenta-empresa').css('display','none');
		
		
        $.ajax({
    		url:Application.getLink('/ajax/registro'),
    		async: true,
    		type:'post',
    		data:postData,
    		dataType:'json',
            success: function(data) {
            	if (data.status=="error") {
            		showError(data.message);
//            		showError('<div style="text-align: left;">Error:<br/>'+data.message+'<br/><br/>Por favor verifique los datos ingresados y vuelva a intentarlo</div>');
            		$('#crear-cuenta-empresa').css('display','');
            		errores=true;
            		$('#signupform-empresa').find('input.required').each(function() {
            			$(this).removeAttr("disabled");
            		});
            		return;
            	}
            	$("#registro-empresa").modal('hide');
            	$("#checkEmail").modal();
            	$('#btn-crear-cuenta-empresa').css('display','none');
            },
            error:function(data) {
        		$('#signupform-empresa').find('input.required').each(function() {
        			$(this).removeAttr("disabled");
        		});
        		$('#crear-cuenta-empresa').css('display','');
            }
        });
		return false;
	});

});

function validForm(esEmpresa) {
	esEmpresa=esEmpresa || false;
	var errores=false;
	var postData={
		usr_email:$.trim($('#email').val()),
		method:'validateRegistry'
	};
	
	$.ajax({
		url:Application.getLink('/ajax/registro'),
		async: false,
		type:'post',
		data:postData,
		dataType:'json',
        success: function(data) {
        	if (data.status!="ok") {
        		errores=true;
        		showError(data.message);
//        		showError('<div style="text-align: left;">Error:<br/>'+data.message+'<br/><br/>Por favor verifique los datos ingresados y vuelva a intentarlo</div>');
        	}
        }
	});

	if (errores) 
		return false;
	
	var errores=[];
	$('#signupform'+(esEmpresa?'-empresa':'')).find('input.required').each(function() {
		var message=$(this).attr("data-description");
		var value=$.trim($(this).val());
		var name=$.trim($(this).attr('name'));
		if (value=="") {
			errores.push("El campo "+message+" est&aacute; vac&iacute;o");
		} else {
			postData[name]=value;
		}
	});
	if ($.trim($('#email').val())!='') {
		if (!isEmail($('#email').val()))
			errores.push("Ingrese una direcci&oacute;n de email v&aacute;lida");
	}

	if ($.trim($('#password1').val())!='' && $.trim($('#password2').val())!='') {
		if ($('#password1').val()!=$('#password1').val())
			errores.push("Las contrase&ntilde;as ingresadas no concuerdan.");
	}
	
	if (errores.length==0) return true;

	showError('Error(es):\n'+errores.join('\n'));
//	showError('<div style="text-align: left;">Error(es):<br/>'+errores.join('<br/>')+'<br/><br/>Por favor verifique los datos ingresados y vuelva a intentarlo</div>')
	return false;
}

