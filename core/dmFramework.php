<?php
Application::Uses("sys.tools.RewriteManager");
Application::Uses("sys.db.manager");
class dmFramework {
	
	public function run() {
    	session_start();
		manager::checkDatabase();
		RewriteManager::processRules();
				
		$scriptName=$_SERVER["SCRIPT_NAME"];
		
		if ($_SERVER["REDIRECT_URL"])
			$requestedUri=$_SERVER["REDIRECT_URL"].($_SERVER["REDIRECT_QUERY_STRING"]?"?".$_SERVER["REDIRECT_QUERY_STRING"]:"");
		else
			$requestedUri=str_replace($scriptName,"",$_SERVER["REQUEST_URI"]);
		list($uri,$qs)=@explode("?",$requestedUri);
		$uri=str_replace("-","_",$uri);
		$sep=(substr($uri,0,1)!="/"?"/":"");
		$uri=strtolower(dirname($uri)."/".basename($uri,".php"));
		if (substr($uri,0,strlen(Application::get("BASE_DIR")))==Application::get("BASE_DIR"))
			$uri=substr($uri,strlen(Application::get("BASE_DIR")));
		
		if (substr($uri,0,2)=="//") $uri=substr($uri,1);
		
		$controller=Application::get("PHYS_PATH")."/web/public".$uri.".php";
		Application::set("RUNNING_CONTROLLER",$uri);
		
		if (!file_exists($controller)) {
			$defaultController=Application::get("DEFAULT_CONTROLLER");
			$defaultController=str_replace(".php","",$defaultController);
			if (substr($defaultController,0,1)!="/") $defaultController="/".$defaultController;
			$controller=Application::get("PHYS_PATH")."/web/public{$defaultController}.php";
			if (!file_exists($controller)) {
				header('HTTP/1.0 404 not found');				
				die();
			}
		}
		$className=basename($controller,".php");
		require_once($controller);
		if (!class_exists($className)) 
			die("Class {$className} not defined on {$controller}");
			
		$page=new $className();
		if (!$page instanceof WebPage) 
			die("Class {$className} must be an instance of WebPage or a child of WebPage");
            
		Application::$page=&$page;
        Application::set("template",array(
            "wrapper"=>Application::get("PHYS_PATH")."/web/public/main.html",
            "controller"=>dirname($controller)."/".basename($controller,".php").".html"
            )
        );

		Application::set("URI",$uri);
		Application::set("SELF",$_SERVER["REQUEST_URI"]);
		Application::set("QUERY_STRING",$qs);

		$page->create();
        $page->run();
	}
	
	public function done() {
		if (Application::get("db")) {
			$db=Application::getDatabase();
			$db->close();
		}
	}
}
