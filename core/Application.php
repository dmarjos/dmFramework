<?php
error_reporting(E_ALL &~ (E_NOTICE+E_STRICT+E_DEPRECATED));
class Application {
	private static $config=array();
	public static $page;
	public static $db=null;
	
	public static function set($var,$val) {
		if ($var!="page")
			self::$config[$var]=$val;
		else
			self::$page=&$val;
	}
	
	public static function get($var,$src=null) {
		if (is_null($src)) $src=self::$config;
		if ($var!="page") 		
			return (isset($src[$var])?$src[$var]:null);
		else
			return self::$page;
	}

	private static function loadConfigFile($file) {
		require_once($file);
		if (isset($CONFIG) && is_array($CONFIG)) {
			foreach($CONFIG as $option=>$value)
				self::set($option,$value);
		}
		if (isset($LABELS) && is_array($LABELS)) 
			self::set("CONFIG_LABELS",$LABELS);
		else
			self::set("CONFIG_LABELS",array());
	}

	public static function Uses($namespace) {
		$components=explode(".",$namespace);
		$environment=$components[0];
		
		$path=array(
			($environment=="sys"?'core':'web/protected')
		);
		$rest=array_shift($components);
		foreach($components as $pathComponent)
			$path[]=$pathComponent;

		$fileToSeekFor=implode("/",$path);
		require_once(Application::get('PHYS_PATH')."/".$fileToSeekFor.".php");
	}
	
	public static function loadConfig($configFile=false) {
		$physicalFolder=dirname(dirname(__FILE__));
		self::set("DEFAULT_CONTROLLER","index");
		self::set("LOG_USERS","true");
		self::set("PHYS_PATH",$physicalFolder);
		$webPath=str_replace(realpath($_SERVER["DOCUMENT_ROOT"]),'',$physicalFolder);
		self::set("WEB_PATH",$webPath);
		
		$configFolder=$physicalFolder."/config";
		if ($configFile===false) $configFile="config.php";
		if (file_exists($configFolder."/{$configFile}")) {
			self::loadConfigFile($configFolder."/{$configFile}");
		}
				
		if (file_exists($configFolder."/".$_SERVER["SERVER_NAME"]."/{$configFile}")) {
			self::loadConfigFile($configFolder."/".$_SERVER["SERVER_NAME"]."/{$configFile}");
		} else if (file_exists($configFolder."/".$_SERVER["SERVER_NAME"].".{$configFile}")) {
			self::loadConfigFile($configFolder."/".$_SERVER["SERVER_NAME"].".{$configFile}");
		} else if (file_exists($configFolder."/".$_SERVER["SERVER_NAME"].".php") && $configFile=="config.php") {
			self::loadConfigFile($configFolder."/".$_SERVER["SERVER_NAME"].".php");
		} 
		
	}
	
	public static function dumpConfig($die=true) {
		dump_var(array(self::$page,self::$config),$die);
	}
	
	public static function redirect($url) {
		$redirectTo=self::getLink($url);
		header("location:".$redirectTo);
		die();
	}
	
	public static function getLink($path) {
		$path_info=parse_url($path);
		if ($path_info["scheme"] && $path_info["host"]) return $path;
		$retVal="";
		if (substr($path,0,1)!="/") $path="/".$path;
		if (self::$config["MOD_REWRITE"]) 
			$retVal=self::get("WEB_PATH").$path;
		else
			$retVal=self::get("WEB_PATH")."/index.php".$path;
			
		$retVal=str_replace("//","/",$retVal);
		$retVal=str_replace("/index.php//","/index.php/",$retVal);
		$retVal=str_replace("/index.php/index.php/","/index.php/",$retVal);
		//if (!preg_match("/(jpg|js|css|png|gif)/si",$retVal))
			//$retVal=str_replace("-","_",$retVal);
		
		$doubleBaseDir=Application::get("BASE_DIR").Application::get("BASE_DIR");
		$retVal=str_replace($doubleBaseDir,Application::get("BASE_DIR"),$retVal);
		return $retVal;
	}

	public static function getPath($path) {
		if (substr($path,0,1)!="/") $path="/".$path;

		$retVal=self::get("WEB_PATH").$path;
		/*
		$doubleBaseDir=Application::get("BASE_DIR").Application::get("BASE_DIR");
		$retVal=str_replace($doubleBaseDir,Application::get("BASE_DIR"),$retVal);
		*/
		return $retVal;
	}

	public static function getDatabase($prefix="") {

		if ($prefix) $prefix=strtoupper("{$prefix}_");
		$db=self::get("{$prefix}db");
		if ($db==null) {
			if (self::get("{$prefix}DB_DRIVER") && self::get("{$prefix}DB_HOST") && self::get("{$prefix}DB_NAME") && self::get("{$prefix}DB_USER")) {
				$driver=strtolower(self::get("{$prefix}DB_DRIVER"));
				
				Application::Uses("sys.db.drivers.".$driver);
				$className="TDB_".ucwords($driver);
				$db=new $className(self::get("{$prefix}DB_HOST"),self::get("{$prefix}DB_USER"),self::get("{$prefix}DB_PASS"),self::get("{$prefix}DB_NAME"));
				self::set("{$prefix}db",$db);
			}
		} 
		self::$db=$db;
		return $db;
	}
	
	public static function GetString($val, $trim=0, $noBlank=false, $noBreak=true, $hints=true) {
	    if ($trim > 0 && strlen($val)-1 > $trim) {
	      $untrimmed = $val;
	      $val = substr($val, 0, $trim);
	      $dots = true;
	    }
	    $val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $val);
	    $val = preg_replace('/&(#)?(.*?);/i','[AMP]\\1\\2;',$val);
	    $val = htmlspecialchars($val, ENT_QUOTES);
	    $val = preg_replace('/\[AMP\](#)?(.*?);/i','&\\1\\2;',$val);
	    if ($noBreak) {
	    	$val = str_replace("\r\n", '<br />', $val);
			$val = str_replace("\r", '<br />', $val);
			$val = str_replace("\n", '<br />', $val);
	    }
	    return $noBlank && trim($val) == '' ? '&nbsp;' : ($dots ? ($hints ? '<span class="alt" title="'.self::GetString(trim($untrimmed),false,false).'">' : '').$val.'<span class="dots">...</span>'.($hints ? '</span>' : '') : $val);
  	}
  	
  	public static function escape($string) {
  		$db=self::getDatabase();
  		return $db->escape($string);
  	}
  	
  	public static function setWidgetParameters($name,$parameters) {
  		$params=self::get("WIDGET_PARAMS");
  		if (!is_array($params)) $params=array();
  		$params[$name]=$parameters;
  		self::set("WIDGET_PARAMS",$params);
  	}

  	public static function getWidgetParameters($name) {
  		$params=self::get("WIDGET_PARAMS");
  		if (!is_array($params)) $params=array();
  		return $params[$name];
  	}

    public static function addStyle($path) {
        if (substr($path,0,14)=="/resources/css") {
        	$baseDir=Application::get("BASE_DIR");
    		$cssBaseDir=$baseDir."/resources";
			$physBaseDir=$_SERVER["DOCUMENT_ROOT"].$cssBaseDir."/themes/".Application::get("DEFAULT_THEME");
			if (file_exists($physBaseDir)."/css") {
				$path=$cssBaseDir."/themes/".Application::get("DEFAULT_THEME")."/css".substr($path,14);
			}
    	} else if (substr($path,0,4)=="/css") {
    		$baseDir=Application::get("BASE_DIR");
    		$cssBaseDir=$baseDir."/resources";
    		$physBaseDir=$_SERVER["DOCUMENT_ROOT"].$cssBaseDir."/themes/".Application::get("DEFAULT_THEME");
    		if (file_exists($physBaseDir)."/css") {
    			$path=$cssBaseDir."/themes/".Application::get("DEFAULT_THEME").$path;
    		}
    	}
    		
    	$baseDir=Application::Get("BASE_DIR");
    	if (substr($path,0,strlen($baseDir))==$baseDir && $baseDir)
    		$path=substr($path,strlen($baseDir));
        $webPath=Application::get("WEB_PATH");
        
        $styles=Application::get("styles");
        if (!is_array($styles)) $styles=array();
        if (is_array($path)) 
        	$fileName=$path['file'];
        else
        	$fileName=$path;
        
        
        if (!$styles) $styles=array();
        
        if (count(explode("://",$fileName))==1) 
            $fileName=$webPath.$fileName;

        if (is_array($path)) 
        	$path["file"]=$fileName;
		else
			$path=$fileName;       
            
        $styles[$fileName]=$path;
        Application::set("styles",$styles);
    }

    public static function getCSSLink($path) {
    	$path=Application::GetLink($path);
        $baseDir=Application::get("BASE_DIR");
    	$path=substr($path,strlen($baseDir));
    	
    	if (substr($path,0,14)=="/resources/css") {
    		$cssBaseDir=$baseDir."/resources";
			$physBaseDir=$_SERVER["DOCUMENT_ROOT"].$cssBaseDir."/themes/".Application::get("DEFAULT_THEME");
			if (file_exists($physBaseDir)."/css") {
				$path=$cssBaseDir."/themes/".Application::get("DEFAULT_THEME")."/css".substr($path,14);
			}
    	} else if (substr($path,0,4)=="/css") {
    		$cssBaseDir=$baseDir."/resources";
    		$physBaseDir=$_SERVER["DOCUMENT_ROOT"].$cssBaseDir."/themes/".Application::get("DEFAULT_THEME");
    		if (file_exists($physBaseDir)."/css") {
    			$path=$cssBaseDir."/themes/".Application::get("DEFAULT_THEME").$path;
    		}
    	}
    	return $path;
    } 
    
    public static function getTemplatesDir() {
		$baseDir=Application::get("BASE_DIR");
    	$templatesBaseDir=$_SERVER["DOCUMENT_ROOT"].$baseDir."/resources";
    	$themeTemplateDir=$templatesBaseDir."/themes/".Application::Get("DEFAULT_THEME")."/templates";
    	if (file_exists($themeTemplateDir))
    		return $themeTemplateDir;
    	return $templatesBaseDir."/templates";
    }
    public static function removeScript($path) {
    	if (substr($path,0,13)=="/resources/js") {
    		$baseDir=Application::get("BASE_DIR");
    		$jsBaseDir=$baseDir."/resources";
    		$physBaseDir=$_SERVER["DOCUMENT_ROOT"].$jsBaseDir."/themes/".Application::get("DEFAULT_THEME");
    		if (file_exists($physBaseDir."/js".substr($path,13))) {
    			$path=$jsBaseDir."/themes/".Application::get("DEFAULT_THEME")."/js".substr($path,13);
    		}
    	} else if (substr($path,0,3)=="/js") {
    		$baseDir=Application::get("BASE_DIR");
    		$jsBaseDir=$baseDir."/resources";
    		$physBaseDir=$_SERVER["DOCUMENT_ROOT"].$jsBaseDir."/themes/".Application::get("DEFAULT_THEME");
    		if (file_exists($physBaseDir)."/js".substr($path,3)) {
    			$path=$jsBaseDir."/themes/".Application::get("DEFAULT_THEME").$path;
    		}
    	}
    	
    	 
    	//0123456789012345678901234567890123456789
   		///resources/css
    	$baseDir=Application::Get("BASE_DIR");
    	if (substr($path,0,strlen($baseDir))==$baseDir && $baseDir)
    		$path=substr($path,strlen($baseDir));
    	
    	$scripts=Application::get("scripts");
        if (!is_array($scripts)) $scripts=array();
    	$webPath=Application::get("WEB_PATH");
        if (count(explode("://",$path))==1)
            $script=$webPath.$path;
        else
            $script=$path;

        if (in_array($script, $scripts)) {
        	$_script=array();
        	foreach($scripts as $theScript) {
        		if ($script!=$theScript) {
        			$_scripts[]=$theScript;
        		}
        	}
        	$scripts=$_scripts;
        }
        
    	Application::set("scripts",$scripts);
    }
    public static function addScript($path,$forceTop=false) {
    	if (substr($path,0,13)=="/resources/js") {
    		$baseDir=Application::get("BASE_DIR");
    		$jsBaseDir=$baseDir."/resources";
    		$physBaseDir=$_SERVER["DOCUMENT_ROOT"].$jsBaseDir."/themes/".Application::get("DEFAULT_THEME");
    		if (file_exists($physBaseDir."/js".substr($path,13))) {
    			$path=$jsBaseDir."/themes/".Application::get("DEFAULT_THEME")."/js".substr($path,13);
    		}
    	} else if (substr($path,0,3)=="/js") {
    		$baseDir=Application::get("BASE_DIR");
    		$jsBaseDir=$baseDir."/resources";
    		$physBaseDir=$_SERVER["DOCUMENT_ROOT"].$jsBaseDir."/themes/".Application::get("DEFAULT_THEME");
    		if (file_exists($physBaseDir)."/js".substr($path,3)) {
    			$path=$jsBaseDir."/themes/".Application::get("DEFAULT_THEME").$path;
    		}
    	}
    	
    	 
    	//0123456789012345678901234567890123456789
   		///resources/css
    	$baseDir=Application::Get("BASE_DIR");
    	if (substr($path,0,strlen($baseDir))==$baseDir && $baseDir)
    		$path=substr($path,strlen($baseDir));
    	$scripts=Application::get("scripts");
        if (!is_array($scripts)) $scripts=array();
    	$webPath=Application::get("WEB_PATH");
        if (count(explode("://",$path))==1)
            $script=$webPath.$path;
        else
            $script=$path;

        
        
        if (!in_array($script, $scripts)) {
            if (!$forceTop) 
            	$scripts[]=$script;
           	else 
           		array_unshift($scripts, $script);
        }
        
    	Application::set("scripts",$scripts);
    }

    public static function addBottomScript($path) {
    	if (substr($path,0,13)=="/resources/js") {
    		$baseDir=Application::get("BASE_DIR");
    		$jsBaseDir=$baseDir."/resources";
    		$physBaseDir=$_SERVER["DOCUMENT_ROOT"].$jsBaseDir."/themes/".Application::get("DEFAULT_THEME");
    		if (file_exists($physBaseDir."/js".substr($path,13))) {
    			$path=$jsBaseDir."/themes/".Application::get("DEFAULT_THEME")."/js".substr($path,13);
    		}
    	} else if (substr($path,0,3)=="/js") {
    		$baseDir=Application::get("BASE_DIR");
    		$jsBaseDir=$baseDir."/resources";
    		$physBaseDir=$_SERVER["DOCUMENT_ROOT"].$jsBaseDir."/themes/".Application::get("DEFAULT_THEME");
    		if (file_exists($physBaseDir)."/js".substr($path,3)) {
    			$path=$jsBaseDir."/themes/".Application::get("DEFAULT_THEME").$path;
    		}
    	}
    	
    	 
    	//0123456789012345678901234567890123456789
   		///resources/css
    	$baseDir=Application::Get("BASE_DIR");
    	if (substr($path,0,strlen($baseDir))==$baseDir && $baseDir)
    		$path=substr($path,strlen($baseDir));
    	$scripts=Application::get("bottom_scripts");
        if (!is_array($scripts)) $scripts=array();
    	$webPath=Application::get("WEB_PATH");
        if (count(explode("://",$path))==1)
            $script=$webPath.$path;
        else
            $script=$path;

        
        
        if (!in_array($script, $scripts)) {
           	$scripts[]=$script;
        }
        
    	Application::set("bottom_scripts",$scripts);
    }

    public static function getUserRole($usr_codigo=null) {
    	Application::Uses("sys.session.login");
    	$login=new login();
    	$info=$login->getUserInfo($usr_codigo);
    	return $info["rol_nombre"];
    }

    public static function getUserRoles($usr_codigo) {
    	Application::Uses("sys.session.login");
    	$login=new login();
    	$info=$login->getUserInfo($usr_codigo,true);
    	return $info["rol_nombre"];
    }
    
    public static function getUserName() {
    	Application::Uses("sys.session.login");
    	$login=new login();
    	$info=$login->getUserInfo();
    	return $info["usr_nombre"];
    }

	public static function getThemePath($path) {
		if (substr($path,0,1)!="/") $path="/".$path;
		$thePath=self::getLink('/resources/themes/'.self::get('DEFAULT_THEME').$path);
		return $thePath;		
	}

	public static function error_log($text) {
		$thePath=self::getLink('/resources/logs/error_log');
		$currentLog=@file_get_contents($_SERVER["DOCUMENT_ROOT"]."/".$thePath);
		if (!$currentLog) $currentLog="";
		$currentLog.="[".date("Y-m-d H:i:s",time())."] [custom:error] [client {$_SERVER["REMOTE_ADDR"]}] {$text}\n";
		file_put_contents($_SERVER["DOCUMENT_ROOT"]."/".$thePath,$currentLog);
	}
}
