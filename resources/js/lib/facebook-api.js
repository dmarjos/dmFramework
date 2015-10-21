// This is called with the results from from FB.getLoginStatus().
function statusChangeCallback(response) {
	console.log('statusChangeCallback');
	console.log(response);
    if (response.status === 'connected') {
    } else if (response.status === 'not_authorized') {
    } else {
    }
}

// This function is called when someone finishes with the Login
// Button.  See the onlogin handler attached to it in the sample
// code below.
function checkLoginState() {
	FB.getLoginStatus(function(response) {
		statusChangeCallback(response);
	});
}

// Here we run a very simple test of the Graph API after login is
// successful.  See statusChangeCallback() for when this call is made.
function testAPI() {
	FB.api('/me', function(response) {
	});
}

function doFacebookSignUp() {
	FB.login(function(response){
		if (response.status === 'connected') {
			FB.api('/me', function(response) {
				response['method']='checkFacebookLogin';
				response['skip_insert']='1';
				Application.callAjax('registro',response,'json',function(data) {
	            	if (data.status=="error") {
	            		$('div.modal.fade#register .close').click();
	            		showInfo(data.message,{level:gravedadInfo.ADVERTENCIA});
	            		return;
	            	}
	            	if (data.status=="ya_registrado")
	            		location.href=Application.getLink('/');
	            	else {
	            		/*
	            		$('#email').val(data.email).attr('disabled','disabled');
	            		$('#nombre').val(data.nombre).attr('disabled','disabled');
	            		$('#apellido').val(data.apellido).attr('disabled','disabled');
	            		$('.open-login-button.context-register.action-login.facebook').parent().remove();
	            		$('#username').focus();
	            		$('html,body').animate({scrollTop: 0}, 400);
	            		*/
	            	}
					
				});
			});
			// Logged into your app and Facebook.
		} else if (response.status === 'not_authorized') {
			// The person is logged into Facebook, but not your app.
		} else {
			// The person is not logged into Facebook, so we're not sure if
			// they are logged into this app or not.
		}
		
	}, {scope: 'public_profile,email'});
}
function doFacebookLogin() {
	FB.login(function(response){
		// Handle the response object, like in statusChangeCallback() in our demo
		// code.
		if (response.status === 'connected') {
			FB.api('/me', function(response) {
				response['method']='checkFacebookLogin';
				Application.callAjaxAndWait('registro',response,'json',function(data) {
	            	if (data.status=="error") {
	            		$('div.modal.fade#register .close').click();
	            		showInfo(data.message,{level:gravedadInfo.ADVERTENCIA});
	            		return;
	            	}
            		location.href=Application.getLink('/');
				});
			});
		}
		
	}, {scope: 'public_profile,email'});
}