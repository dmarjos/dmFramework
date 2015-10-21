<?php
Application::Uses("sys.constants.UserRules");
Application::Uses("com.admin.PanelAdminForm");
Application::Uses("sys.tools.stringUtils");

class form_templates extends PanelAdminForm {

	public $rules    = 'usuarios';

	public $fields=array(
		"nombre"=>array(
			"title"=>"Nombre del template",
			"width"=>"114px",
			"type"=>"text",
			"tableField"=>"tmp_nombre"
		),
		"titulo"=>array(
			"title"=>"Titulo del template",
			"width"=>"100%",
			"maxlength"=>64,
			"type"=>"text",
			"tableField"=>"tmp_titulo"
		),
		"texto"=>array(
			"title"=>"Contenido del template",
			"width"=>"100%",
			"rows"=>"10",
			"type"=>"textarea",
			"tableField"=>"tmp_contenido"
		),
	);
	
	public function create() {
		$this->setMainTable("tbl_email_template");
		$this->setIndexField("tmp_codigo");
		$this->urlBackTo=Application::getLink("/admin/emails/templates");
		parent::create();
		Application::AddScript('/resources/js/admin/emails/edit_template.js');
	}

	public function init() {
		$retVal=parent::init();
		if ($retVal) {
			$formParameters=array(
				"title"=>"Templates de Emails",
				"submitByAjax"=>false,
				"submitUrl"=>$_SERVER["REQUEST_URI"],
				"globalValidator"=>"processDescription",
				"fields"=>$this->fields
			);
			switch($_GET["action"]) {
				case "add":
					$this->mode="INSERT";
					$formParameters["subtitle"]="Nuevo Template para Emails";
					break;
				case "upd":
					$this->mode="UPDATE";
					$formParameters["subtitle"]="Modificar Template para Emailsd";
					break;
				case "del":
					$this->mode="DELETE";
					$formParameters["subtitle"]="Borrar Template para Emails";
					break;
			}
			
			Application::setWidgetParameters("templates_form", $formParameters);
		}
		return $retVal;
	}
	
	public function run() {
		parent::run();
		
		
	}

	protected function prepareUpdate() {
	
  		$this->fields["nombre"]["readonly"]=true;
		$db=Application::getDatabase();
		if (!$_POST) {
			$sql="SELECT * from tbl_email_template where tmp_codigo='{$_GET["id"]}'";
				
			$currentRecord=$db->getRow($sql);
			if (empty($currentRecord)) {
				$this->view->assign("template","error.tpl");
				$this->view->assign("err_message","El template de email solicitado no existe");
				$this->view->assign("url_back",$retVal["url_back"]?$retVal["url_back"]:Application::GetLink("/admin"));
				return;
			}
			
			$retVal=$this->buildSelectQuery();
				
			$sql=$retVal["query"];
				
			$this->currentRecord=$db->getRow($sql);
			foreach($this->fields as $field => &$data) {
				if ($data["tableField"] && $this->currentRecord[$data["tableField"]])
					$data["value"]=utf8_decode($this->currentRecord[$data["tableField"]]);
			}
							
		} else {
			$this->updateRecord();
		}
	
		//Application::dumpConfig();
		return true;
	}
	
	protected function canDeleteRecord() {
		return array(
  			"status"=>false,
  			"err_message"=>"No puede eliminarse los templates de emails",
  			"url_back"=>Application::GetLink('/admin/emails/templates')
  		);
  	}
  	
  	protected function updateRecord() {
  		$_POST["texto"]=stringUtils::replace_all("#br#","\n",$_POST["texto"]);
  		parent::updateRecord();
  	}
  	
  	protected function insertRecord() {
  		$_POST["texto"]=stringUtils::replace_all("#br#","\n",$_POST["texto"]);
  		parent::insertRecord();
  	}
  	
}
