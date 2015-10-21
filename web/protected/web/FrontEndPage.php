<?php
Application::Uses("sys.web.WebPage");
Application::Uses("com.tools.usersUtilities");
class FrontEndPage extends WebPage {
	
	protected $rules="frontend";
	public $bodyClasses=array();
	
	public function create() {

		Application::AddScript("http://netdna.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js");
		Application::AddStyle("http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css");
		Application::AddStyle("http://pingendo.github.io/pingendo-bootstrap/themes/default/bootstrap.css");
		Application::AddScript("/resources/js/lib/jquery.infinitescroll.js");
		Application::AddScript("/resources/js/front/functions/pull-notifications.js");
		Application::AddScript("/resources/js/front/scripts.js");
		if ($_COOKIE[Application::get("USER_COOKIE")]) $_SESSION[Application::get("FRONTEND_SESSION_VAR")]=$_COOKIE[Application::get("USER_COOKIE")];
		
		parent::create();
		Application::AddScript("/resources/js/lib/jquery-1.11.0.min.js",true);
	}

	public function init() {
		$retVal=parent::init();
		$db=Application::getDatabase();
		
		$this->view->assign("usuario",$usuario);
		$shortcuts=array();
		$shortcuts[]=array("text"=>"inicio","link"=>Application::GetLink("/"));
		$this->view->assign("shortcuts",$shortcuts);
		return $retVal;
	}
	
	
	public function getLogin() {
		Application::Uses('sys.session.login');
		return new login("frontend");
	}
	
	protected function authenticate() {
		$login = $this->getLogin();
		$userInfo=$login->getUserInfo();
		Application::set("user",$userInfo);
		if (!$this->meetRules($this->rules,UserRules::VIEW)) {
			Application::set("user",$login->run());
			if (!$this->meetRules($this->rules,UserRules::VIEW)) {
				if (!empty($this->rules) || isset($_POST['LoginForm'])) {
					if (!isset($_SESSION[Application::get("FRONTEND_SESSION_VAR")])) {
						$this->view->assign("menu_ok","no");
					} else {
						$this->view->assign("menu_ok","yes");
					}
				}
			} else {
				$userInfo=Application::get("user");
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
		unset($login);
		return true;
	}

	public function isLoggedIn() {
		return isset($_SESSION[Application::get("FRONTEND_SESSION_VAR")]);
	}
	
	public function output() {
		if (!$this->bodyClasses) $this->bodyClasses[]="page-mainstream";
		if (!$this->isLoggedIn() && Application::get('RUNNING_CONTROLLER')!="/registro") {
	        Application::AddScript("/resources/js/front/registro.js");
	        if ($_GET["reset_hash"]) {
	        	$db=Application::getDatabase();
	        	$user=$db->getRecord("tbl_usuario","usr_hash='{$_GET["reset_hash"]}'");
	        	if (!$user) {
	        		$_SESSION["mensaje"]=array("text"=>"El codigo de seguridad es inv&aacute;lido!","level"=>1);
	        		$this->templateFile="login.tpl";
	        	} else {
	        		$this->templateFile="reset_password.tpl";
	        		$this->view->assign("recover_user",$user);
	        	}
	        } else 
				$this->templateFile="login.tpl";
	         
		} else { 
			$this->view->assign("navigation",$this->navigation);
			$this->view->assign("body_classes",$this->bodyClasses);
			preg_match_all("~pagina/([0-9]*)\.html~si",$_SERVER["REQUEST_URI"],$matches,PREG_SET_ORDER);
			list($u,$q)=explode("?",$_SERVER["REQUEST_URI"]);
			if ($matches) {
				$u=str_replace($matches[0][0],"",$u);
				$currentPage=$matches[0][1];
			} else {
				$currentPage=1;
			}
			if (substr($u,-1)!="/") $u.="/";
			$u.="pagina/".($currentPage+1).".html";
			if ($q) $u.="?".$q;
			$this->view->assign("next_page_url",$u);
			$this->view->assign("first_page",true);
			if ($currentPage>1) {
				$this->templateFile="raw_template.tpl";
				$this->view->assign("first_page",false);
			}
		}
		if ($_SESSION["mensaje"]) {$this->view->assign("mensaje",$_SESSION["mensaje"]); unset($_SESSION["mensaje"]);}
		parent::output();
	}
} 