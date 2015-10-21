<?php
Application::Uses("sys.web.TComponent");
class TWidget extends TComponent {
	
    public $templateFile="";
	protected $parameters=null;
	protected $name=null;

	public function __construct() {
		$this->create();
	}
	
	public function create() {
		parent::create();
	}
	
	public function run($name="") {
		$parameters=Application::getWidgetParameters($name);
		$this->name=$name;
		$this->parameters=$parameters;
		$this->init();
		return $this->output();
	}
	
	protected function init() {}
	
	protected function output() {
        return $this->view->fetch("widgets/".substr(strtolower(get_class($this)),1).".tpl");
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
}