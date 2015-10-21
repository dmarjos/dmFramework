<?php
Application::Uses("sys.web.WebAdmin");
class logout extends WebAdmin {
	
	public function create() {
		parent::create();
		session_destroy();
		Application::redirect('/admin');
	}
}