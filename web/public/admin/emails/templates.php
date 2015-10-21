<?php
Application::Uses("sys.constants.UserRules");
Application::Uses("com.admin.PanelAdmin");
class templates extends PanelAdmin {
	
  	public $rules    = 'usuarios';

  	public function create() {
		parent::create();
		$gridParameters=array(
			"url"=>Application::GetLink("/admin/ajax/emails")."?method=getTemplates",
			"form"=>Application::GetLink("/admin/emails/form_templates"),
			"id_field"=>"tmp_codigo",
			"columns"=>array(
				array("field"=>"tmp_nombre","header"=>"Nombre","width"=>"180px"),
				array("field"=>"tmp_titulo","header"=>"Titulo"),
			)
		);
		
		Application::setWidgetParameters("templates_grid", $gridParameters);
	}

	public function run() {
		parent::run();
	}

}