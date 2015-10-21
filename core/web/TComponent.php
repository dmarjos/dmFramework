<?php
//Application::Uses("sys.web.TemplateManager");
Application::Uses("sys.library.smarty.Smarty");
class TComponent {
    public $view=null;
    public $wrapper="";
    public function create() {
        $template=Application::get("template");
        $this->wrapper=$template["wrapper"];
        if ($this->view==null)
        	$this->view=new Smarty();
            
        $baseDir=Application::get("BASE_DIR");
        
        $templatesBaseDir=$_SERVER["DOCUMENT_ROOT"].$baseDir."/resources";
        $themeTemplateDir=$templatesBaseDir."/themes/".Application::Get("DEFAULT_THEME").Application::get("TEMPLATE_DIR");

        if (file_exists($themeTemplateDir))
        	$templatesDir=$themeTemplateDir;
        else 
        	$templatesDir=$templatesBaseDir.Application::get("TEMPLATE_DIR");
        
		$this->view->compile_dir=$templatesBaseDir.Application::get("TEMPLATE_C_DIR");
        $this->view->setTemplateDir($templatesDir);
    }

    public function run() {
        
            
    }

}
