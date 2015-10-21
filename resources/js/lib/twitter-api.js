function doTwitterLogin() {
	
	Application.callAjaxAndWait('registro',{method:'checkTwitterLogin'},'json',function(data) {
		var twitterLoginURL='https://api.twitter.com/oauth/authenticate?oauth_token='+data.oauth_token;
		window.open(twitterLoginURL,'socnet_login',"width=600, height=100");
	});
	
}