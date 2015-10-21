$(document.body).ready(function() {
	$('#btn-editar-perfil').click(function() {
	    $('#btn-editar-perfil').addClass('hidden');
	    $('#btn-ver-perfil').removeClass('hidden');
	    $('#btn-change-password').removeClass('hidden');
	    $('#btn-security-question').removeClass('hidden');
		$('#perfil-datos').css({'display':'none'});
		$('#perfil-edicion').css({'display':'block'});
		setEdicion();
	});
	
	$('#btn-ver-perfil').click(function() {
		location.reload();
    });
});

function setEdicion() {
    $.fn.editable.defaults.url = Application.getLink('admin/perfiles/editar'); 
    $.fn.editable.defaults.inputclass = 'form-control';

    $('#usr_nombre').editable({
        validate: function(value) {
           if($.trim(value) == '') return 'This field is required';
        }
    });
    
    $('#per_descripcion').editable({
        showbuttons: 'bottom'
    }); 

    $('#per_fecha_nacimiento').editable({viewformat:'DD/MM/YYYY'});
    $('#usr_email').editable({
    	validate: function(value) {
    		var retVal="";
    		var postData={
    			method:'validarEmail',
    			type:'json',
    			email: value
    		};
    		$.ajax({
    			url:Application.getLink('/admin/ajax/perfiles'),
    			async: false,
    			type:'post',
    			data:postData,
    			dataType:'json',
    			success:function(data) {
    				if (data.status=='error') {
    					retVal=data.message;
    				}
    			}
    		});
    		
    		if (retVal!='') return retVal;
    		return retVal;
    	}
    });

    $('#imagen-perfil-edicion').css({
    	'cursor':'pointer',
    	'border':'1px dotted #0000ff',
    });
    
    $('#select-profile-picture .img-circle').css({
    	'background-position':'top left',
    	'background-size':'cover',
    	'background-image':'url('+$('#imagen-perfil-edicion').attr('src')+')'
    });
 
    $('.upload-profile-image').click(function() {
    	$('#upload-image').change(function(){
//            alert($('#upload-image').val());
            $('#upload-form').ajaxSubmit({
            	success:function(data) {
            		data=eval('(' + data + ')');
            		if (data.status=="error") {
            			alert(data.message);
            			return;
            		}
    			    $('#select-profile-picture .img-circle').css({
    			    	'background-position':'top left',
    			    	'background-size':'cover',
    			    	'background-image':'url('+data.imagePath+'?rnd='+Math.random()+')'
    			    });
    			    $('.user-profile-image img').attr('src',data.imagePath+'?rnd='+Math.random());
    			    $('#imagen-perfil-edicion').attr('src',data.imagePath+'?rnd='+Math.random());
            		
            	}
            });
        });
    	$('#upload-image').click();
    	/*
        var block = $(this).parents('.file');
        block.find('input:file').click();
        block.find('input:file').change(function(){
            block.find('input:text').val(block.find('input:file').val());
        });
    	 */
    });
    
    $('#password').bind('keyup',function() {
    	checkPasswordStrength();
    })
    
    $('#select-profile-picture .use-profile-image').click(function() {
    	
    	var network=$(this).attr('network');
    	if (network=='none') {
        	var postData={
    			method:'removeProfilePicture'
    		};
    	} else {
            $('#select-profile-picture .img-circle').css({
            	'background-position':'top left',
            	'background-size':'cover',
            	'background-image':'url(/resources/img/admin/loading.gif)'
            });
            
        	var postData={
    			method:'getProfilePicture',
    			network:network,
    			id:$('#per_facebook').html().trim(),
    		};
    	}

    	$.ajax({
    		url:Application.getLink('/admin/ajax/perfiles'),
    		async: false,
    		type:'post',
    		data:postData,
    		dataType:'json',
    		success:function(data) {
    			if (data.status=='error') {
    				alert(data.message);
    			    $('#select-profile-picture .img-circle').css({
    			    	'background-position':'top left',
    			    	'background-size':'cover',
    			    	'background-image':'url('+$('#imagen-perfil-edicion').attr('src')+')'
    			    });
    			} else {
    			    $('#select-profile-picture .img-circle').css({
    			    	'background-position':'top left',
    			    	'background-size':'cover',
    			    	'background-image':'url('+data.imagePath+'?rnd='+Math.random()+')'
    			    });
    			    $('.user-profile-image img').attr('src',data.imagePath+'?rnd='+Math.random());
    			    $('#imagen-perfil-edicion').attr('src',data.imagePath+'?rnd='+Math.random());
    				
    			}
    		}
    	});
    	
    }); 
}

function validarRedesSociales(redSocial,idUsuario) {
	var retVal='';

	var postData={
		method:'parseURL',
		url:idUsuario,
	};

	$.ajax({
		url:Application.getLink('/admin/ajax/perfiles'),
		async: false,
		type:'post',
		data:postData,
		dataType:'json',
		success:function(data) {
			if (data.status=='error') {
				retVal=data.message;
			}
		}
	});
	
	if (retVal!='') return retVal;
	postData={
		method:'validarRedesSociales',
		network:redSocial,
		id:idUsuario,
	};

	
	$.ajax({
		url:Application.getLink('/admin/ajax/perfiles'),
		async: false,
		type:'post',
		data:postData,
		dataType:'json',
		success:function(data) {
			if (data.status=='error') {
				retVal=data.message;
			}
		}
	});
	return retVal;
}

function scorePassword(pass) {
    var score = 0;
    if (!pass)
        return score;

    // award every unique letter until 5 repetitions
    var letters = new Object();
    for (var i=0; i<pass.length; i++) {
        letters[pass[i]] = (letters[pass[i]] || 0) + 1;
        score += 5.0 / letters[pass[i]];
    }

    // bonus points for mixing it up
    var variations = {
        digits: /\d/.test(pass),
        lower: /[a-z]/.test(pass),
        upper: /[A-Z]/.test(pass),
        nonWords: /\W/.test(pass),
    }

    variationCount = 0;
    for (var check in variations) {
        variationCount += (variations[check] == true) ? 1 : 0;
    }
    score += (variationCount - 1) * 10;

    return parseInt(score);
}

function checkPasswordStrength() {
	var pass=$('#password').val().trim();
	
	var score=scorePassword(pass);
	if (score<=30) {
		$('#password-meter').removeClass('label-success label-warning').addClass('label-danger').html('Debil');
	} else if (score>=60) {
		$('#password-meter').removeClass('label-danger label-warning').addClass('label-success').html('Fuerte');
	} else {
		$('#password-meter').removeClass('label-success label-danger').addClass('label-warning').html('Media');
	}
	$('#password_strength').val();
}

function validarPassword() {
	if ($('.i-am-new').length!=0) $('.i-am-new').remove();
	var oldPass=$('#current_password').val().trim();
	var pass=$('#password').val().trim();
	var pass2=$('#password2').val().trim();
	var notyParameters={
        type: 'error',
        text: '',      
        force:true,
        killer:true,
        buttons: [
            {
            	addClass: 'btn btn-danger btn-clean', text: 'Cerrar', onClick: function($noty) {
                    $noty.close();
                }
            }
        ]
    };
	
	if (pass=='' || pass2=='') {
		notyParameters.text='Las contrase&ntilde;as no pueden estar vacias';
		noty(notyParameters);
		return;
	}
	
	if (pass!=pass2) {
		notyParameters.text='Las contrase&ntilde;as no coinciden';
		noty(notyParameters);
		return;
	}
	
	postData={
		method:'changePassword',
		oldPassword:oldPass,
		newPassword:pass,
		type:'json'
	};

	
	$.ajax({
		url:Application.getLink('/admin/ajax/perfiles'),
		async: false,
		type:'post',
		data:postData,
		dataType:'json',
		success:function(data) {
			if (data.status=='error') {
				notyParameters.text=data.message;
				noty(notyParameters);
				return;
			} else {
				$('#close-change-password').click();
			}
		}
	});
	
}

function guardarPreguntaSeguridad() {
	if ($('.i-am-new').length!=0) $('.i-am-new').remove();
	var pass=$('#sq_password').val().trim();
	var question=$('#question').val().trim();
	var answer=$('#answer').val().trim();
	var notyParameters={
        type: 'error',
        text: '',      
        force:true,
        killer:true,
        buttons: [
            {
            	addClass: 'btn btn-danger btn-clean', text: 'Cerrar', onClick: function($noty) {
                    $noty.close();
                }
            }
        ]
    };
	
	if (question=='') {
		notyParameters.text='Por favor, selecciona tu pregunta de seguridad';
		noty(notyParameters);
		return;
	}
	
	if (answer=='') {
		notyParameters.text='Por favor, ingresa tu respuesta';
		noty(notyParameters);
		return;
	}
	
	postData={
		method:'changeSecurityQuestion',
		password:pass,
		question:question,
		answer:answer,
		type:'json'
	};

	
	$.ajax({
		url:Application.getLink('/admin/ajax/perfiles'),
		async: false,
		type:'post',
		data:postData,
		dataType:'json',
		success:function(data) {
			if (data.status=='error') {
				notyParameters.text=data.message;
				noty(notyParameters);
				return;
			} else {
				$('#close-security-question').click();
			}
		}
	});
	
}