<?php
Application::Uses("sys.web.WebPage");

class WebAdmin extends WebPage {

	protected $rules="backend";
	
	public function create() {
		parent::create();
		Application::addScript("/resources/js/lib/application.js");
	}

	public function getLogin() {
		Application::Uses('sys.session.login');
		return new login(Application::Get("BACKEND_SESSION_VAR"));
	}

	public function isLoggedIn() {
		return isset($_SESSION[Application::get("BACKEND_SESSION_VAR")]);
	}
	
	protected function authenticate() {
		$login = $this->getLogin();
		$userInfo=$login->getUserInfo();
		Application::set("user",$userInfo);
		$userHasAccess=$this->meetRules($this->rules,UserRules::VIEW);
		if (!$userHasAccess) {
			Application::set("user",$login->run());
			if (!$this->meetRules($this->rules,UserRules::VIEW)) {
				if (!empty($this->rules) || isset($_POST['LoginForm'])) {
					$this->view->assign("menu_ok","no");
					if ($this->isLoggedIn()) {
						return false;	
					} else 
						$this->view->assign("template","login.tpl");
				}
			} else {
				$this->view->assign("menu_ok","yes");
				if ($_POST['LoginForm']) {
					$url=Application::get("SELF");
					$qs=Application::get("QUERY_STRING");
					if (!empty($qs))
						$url.="?".$qs;
					Application::Redirect($url);
				}
			}
		}
		return true;
		unset($login);
	}
	
	
	public function output() {
		$this->templateFile="main_admin.tpl";
		parent::output();
	}
	
}