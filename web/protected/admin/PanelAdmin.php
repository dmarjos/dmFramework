<?php
Application::Uses("sys.web.WebAdmin");
Application::Uses("com.tools.usersUtilities");

class PanelAdmin extends WebAdmin {

	public $menu=array();
	public function create() {
		Application::set("DEFAULT_THEME","gemini");
		Application::AddScript("/resources/js/plugins/mcustomscrollbar/jquery.mCustomScrollbar.min.js",true);
        Application::AddScript("/resources/js/plugins/bootstrap/bootstrap.min.js",true);
        Application::AddScript("/resources/js/plugins/jquery/jquery-ui.min.js",true);
        Application::AddScript("/resources/js/plugins/jquery/jquery.js",true);
		parent::create();
		if ($_COOKIE[Application::get("BACKEND_USER_COOKIE")]) $_SESSION[Application::get("BACKEND_SESSION_VAR")]=$_COOKIE[Application::get("BACKEND_USER_COOKIE")];
	}
	
	public function init() {
		$retVal=parent::init();
		$this->title=Application::get('SYSTEM_TITLE')." - Panel Administrativo";
		
		$this->menu=array(
			"administracion"=>array(
				"rules"=>"usuarios|roles|mensajes",
				"text"=>"Administraci&oacute;n",
				"options"=>array(
					array("text"=>"Listado de administradores","rules"=>"usuarios","link"=>Application::GetLink('admin/administracion/usuarios')),
					array("text"=>"Roles y Reglas","rules"=>"roles","link"=>Application::GetLink('admin/administracion/roles-y-reglas')),
				)
			),
			"emails"=>array(
				"rules"=>"usuarios",
				"text"=>"Emails",
				"options"=>array(
					array("text"=>"Templates de email","rules"=>"usuarios","link"=>Application::GetLink('admin/emails/templates'))
				)
			),
		);
		
		foreach($this->menu as $key=>$options) {
			if (preg_match("#/admin/{$key}/#Usi",$_SERVER["REQUEST_URI"])) {
				$this->menu[$key]["status"]="open";
				break;
			}
		}

		$messages=array();
		$messagesCount=0;
		
		$this->view->assign("message_count",$messagesCount);
		$this->view->assign("messages",$messages);
		$this->view->assign("menu",$this->menu);

		return true;
	}
	
	
	public function output() {
		Application::AddScript("/resources/js/common-functions.js");
		Application::AddScript("/resources/js/init-backend.js");
		
		parent::output();
	}
	
	public function isLoggedIn() {
		return isset($_SESSION[Application::get("BACKEND_SESSION_VAR")]);
	}
	
} 