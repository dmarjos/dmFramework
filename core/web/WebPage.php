<?php
Application::Uses("sys.web.TComponent");
Application::Uses('sys.constants.UserRules');
Application::Uses('sys.constants.UserStates');
Application::Uses('sys.tools.stringUtils');

class WebPage extends TComponent {

    protected $__variables=array();

    protected $rules=null;
    protected $title="Page title!";
    protected $metatags=array();
    private $scripts=array();
    private $styles=array();
    
    public $templateFile="";
    
    public function create() {
    	parent::create();
    	
    	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
    	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
    	
    	//Application::addStyle("/css/mystyles.css",true);
    	Application::addScript("/resources/js/lib/application.js",true);
    	if (Application::get("CONSTRUCCION")==true)
    		$this->templateFile="construct.tpl";
    		else
    		$this->templateFile="main.tpl";

    	$this->view->assign("template","empty.tpl");
    }
    
    
    public function run() {
    	$this->authenticate();
    	if ($this->init())
        	$this->output();
    }
    
    public function init() { return true; }
    
    public function output() {
    	//$this->backTrace();
        $this->view->assign("scripts",Application::get("scripts"));
        $this->view->assign("bottom_scripts",Application::get("bottom_scripts"));
        $this->view->assign("styles",Application::get("styles"));
        $this->view->assign("metas",$this->metatags);
        $this->view->assign("title",$this->title);
        $this->view->assign("favicon",$this->setIcon());

        if (!$this->authenticate()) {
        	$this->view->assign("template","access_denied.tpl");
        } else
	        $this->view->assign("template",$this->getTemplateName());
        $this->view->display($this->templateFile);
        die();
    }
    
    protected function getTemplateName() {
        $template=$this->view->tpl_vars["template"]->value;
        if ($template=="empty.tpl") {
			$requestedUri=str_replace($scriptName,"",$_SERVER["REQUEST_URI"]);
			list($uri,$qs)=@explode("?",$requestedUri);
			if (substr($uri,0,strlen(Application::get("BASE_DIR")))==Application::get("BASE_DIR"))
				$uri=substr($uri,strlen(Application::get("BASE_DIR")));
			
			$sep=(substr($uri,0,1)!="/"?"/":"");
			$uri=strtolower(dirname($uri)."/".basename($uri,".php"));
            $uri=str_replace("//","/",$uri);
            $uri=str_replace("\\/","/",$uri);
			if (substr($uri,0,10)=="/index.php") $uri=substr($uri,10);
			if (substr($uri,0,1)=="/") $uri=substr($uri,1);
			if (empty($uri)) $uri="index";
			$uri=str_replace("-","_",$uri);
			$template=$uri.".tpl";
				

			$baseDir=Application::get("BASE_DIR");
			
			$templatesBaseDir=$_SERVER["DOCUMENT_ROOT"].$baseDir."/resources";
			$themeTemplateDir=$templatesBaseDir."/themes/".Application::Get("DEFAULT_THEME")."/templates";
			if (file_exists($themeTemplateDir))
				$templatesBaseDir.="/themes/".Application::Get("DEFAULT_THEME")."/templates";
			
			if (!file_exists($templatesBaseDir."/{$template}")) {
				$templatesBaseDir=$_SERVER["DOCUMENT_ROOT"].$baseDir."/resources/templates";
				if (!file_exists($templatesBaseDir."/{$template}")) {
					throw new Exception($templatesBaseDir."/{$template} noexiste!");
					$template="empty.tpl";
				} else {
					$template=$templatesBaseDir."/{$template}";
				}				
			}
		}
        return $template;
    } 
    
    protected function backTrace() {
    	$backtrace=debug_backtrace();
    	$index=array_pop($backtrace);
    	//$bt=array_pop($backtrace);
    	$backtrace=array_reverse($backtrace);
    	
    	foreach($backtrace as $idx=>$step) {
    		$margin="";
//    		if ($idx>0) $margin=str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;",$idx);
    		echo '<div style="width:100%x; font-size: 14px; margin-left: 0px; float:left; clear:both">'.$step["file"]." ".$step["line"].'</div><div style="clear:both"></div>';
    		foreach($step as $key=>$value) {
    			if (!in_array($key,array("file","line","object","args")))
    				echo '<div style="width:80px; font-size: 13px; margin-left: 10px; float:left;">'.$key.'</div><div style="width:150px; font-size: 13px; margin-left: 10px; float:left; ">'.$value.'</div><div style="clear:both"></div>';
    		}
    	}
    	die();
    }

    public function setTitle() {
        return '<title>'.$this->title.'</title>';
    }
    
    public function setScripts() {
        $this->scripts=Application::get("scripts");
    	$scripts=array();
        foreach($this->scripts as $script) {
            if (!is_array($script))
                $scripts[]='<script type="text/javascript" src="'.$script.'"></script>';
        }
    	$extraScript='
<script type="text/javascript">
var MAIN_URL="'.Application::getLink('').'";
var SCRIPT_URL="'.Application::get('SELF').'";
</script>
';
        
        return $extraScript."\n".implode("\n",$scripts);
    }
    
    public function setStyles() {
        $this->styles=Application::get("styles");
        $styles=array();
        foreach($this->styles as $style) {
            if (!is_array($style))
                $styles[]='<link rel="stylesheet" type="text/css" href="'.$style.'" />';
            else {
                if ($style["condition"])
                    $styles[]='<!--[if '.$style["condition"].']>';
                if ($style["media"])
                    $styles[]='<link rel="stylesheet" type="text/css" href="'.$style["file"].'" media="'.$style["media"].'"/>';
                else
                    $styles[]='<link rel="stylesheet" type="text/css" href="'.$style["file"].'"/>';
                if ($style["condition"])
                    $styles[]='<![endif]-->';
            }
        }
        
        return implode("\n",$styles);
    }
    
    public function setIcon() {
        $html="";
        if (file_exists(Application::get("WEB_PATH")."/favicon.ico"))
            $html='<link rel="shortcut icon" href="'.Application::get("WEB_PATH").'/favicon.ico">';

        
        return $html;
    }
    
    public function setPage($auxPlaceHolder,$params) {
        $template=Application::get("template");
        if (file_exists($template["controller"]) && !empty($template["controller"])) {
            ob_start();
            require_once($template["controller"]);
            $content=ob_get_contents();
            ob_end_clean();
            $auxPlaceHolder=str_replace($params["full_tag"],$content,$auxPlaceHolder);
       } else {
            $auxPlaceHolder=str_replace($params["full_tag"],"",$auxPlaceHolder);
       }
       
       $auxPlaceHolder=$this->view->prProcessTemplate($auxPlaceHolder);
       return $auxPlaceHolder;
    }

    public function addStyle($path) {
    	Application::addStyle($path);
    }

    public function addScript($path) {
    	Application::addScript($path);
    }
    
    public function addMeta($type, $name, $content) {
    	$this->meta[]=array("type"=>$type,"name"=>$name,"content"=>$content);
    }
    
    public function __set($var,$val) {
        $this->__variables[$var]=$val;
    }
    
    public function __get($var) {
        return (isset($this->__variables[$var])?$this->__variables[$var]:null);
    }
    
  	public function getLogin() {
    	Application::Uses('sys.session.login');
    	return new login();
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
        			if (!isset($_SESSION[Application::get("BACKEND_SESSION_VAR")])) {
        				$this->view->assign("menu_ok","no");
        				$this->view->assign("template","login.tpl");
        			} else {
        				$this->view->assign("menu_ok","yes");
        			}
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
    	unset($login);
    }

  	public function meetRules($rules,$rule,$user=0,$rol=0) {
  		if ($rules===null) return true;
    	if (intval($user) > 0) {
      		$db = Application::GetDatabase();
      		$user = $db->getRow("SELECT tbl_usuario WHERE usr_codigo = '".intval($user)."'");
      		if (!empty($user)) {
      			$roles=explode(",",$user["usr_codrol"]); //LEFT JOIN tbl_usuario_role ON usr_codrol = rol_codigo
      			$res=$db->execute("SELECT * from tbl_usuario_role where rol_codigo in('".implode("','",$roles)."')");
      			$_rules=array();
      			while($rec=$db->getNextRecord($res)) {
					$rol_rules=unserialize($rec['rol_rules']);
      				$_rules=array_merge($_rules,$rol_rules);
      			}
      			$user['rol_rules'] = $_rules; //unserialize($user['rol_rules']); 
      		}
    	} else if (intval($rol) > 0) {
    		$db = Application::GetDatabase();
      		$user = $db->getRow("SELECT * FROM tbl_usuario_role WHERE rol_codigo = '".intval($rol)."'");
      		if (!empty($user)) { $user['rol_rules'] = unserialize($user['rol_rules']); }
    	} else {
    		$user = Application::get("user");
    	}
    	$result = !empty($user);
    	/*
    	if ($rules=="roles" && $rule==UserRules::UPDATE)
    		dump_var($user);
    	*/
    	if ($result) {
      		$rules = is_array($rules) ? $rules : (empty($rules) ? array() : array($rules));
      		foreach ($rules as $list) {
        		$local = false;
        		$list = explode('|',$list);
        		foreach ($list as $r) {
          			$r = trim($r);
          			if ((intval($user['rol_rules'][$r]) & $rule)==$rule) return true;
          			$local = $local || ((intval($user['rol_rules'][$r]) & $rule) == $rule);
        		}
        		$result = $result && $local;
      		}
    	}
    	return $result;
  	}
    
  	public function isLoggedIn() {
  		if ($this->rules===null) return true;
  		return isset($_SESSION[Application::get("FRONTEND_SESSION_VAR")]);
  	}
  	
  	 
}
