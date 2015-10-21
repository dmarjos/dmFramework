<?php
Application::Uses("com.web.FrontEndPage");
class logout extends FrontEndPage {
	
	public function create() {
		parent::create();
		session_destroy();
		setcookie(Application::get("USER_COOKIE"),false,time()-3600);
		Application::redirect('/');
	}
}