<?php
Application::Uses("sys.constants.UserRules");
Application::Uses("com.admin.PanelAdminForm");
Application::Uses("sys.tools.stringUtils");
Application::Uses("sys.library.phpmailer.core");

class emails extends PanelAdminForm {

	public $rules    = 'roles';

	public $fields=array(
		
		"subject"=>array(
			"title"=>"Asunto",
			"width"=>"100%",
			"type"=>"text",
			"tableField"=>"tmp_nombre"
		),
		
		"from_name"=>array(
			"title"=>"Nombre del Remitente",
			"width"=>"100%",
			"type"=>"text",
			"tableField"=>"tmp_nombre"
		),
		"from_email"=>array(
			"title"=>"Direccion de email",
			"width"=>"100%",
			"maxlength"=>64,
			"type"=>"text",
			"tableField"=>"tmp_titulo"
		),
		"to_name"=>array(
			"title"=>"Nombre del destinatario",
			"width"=>"100%",
			"type"=>"text",
			"tableField"=>"tmp_nombre"
		),
		"to_email"=>array(
			"title"=>"Direccion de email",
			"width"=>"100%",
			"maxlength"=>64,
			"type"=>"text",
			"tableField"=>"tmp_titulo"
		),
		"texto"=>array(
			"title"=>"Contenido del email",
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
		$_GET["action"]="upd";
	}

	public function init() {
		$retVal=parent::init();
		if ($retVal) {
			$formParameters=array(
				"title"=>"Diagnosticos",
				"submitByAjax"=>false,
				"submitUrl"=>$_SERVER["REQUEST_URI"],
				"fields"=>$this->fields
			);
			switch($_GET["action"]) {
				case "upd":
					$this->mode="UPDATE";
					$formParameters["subtitle"]="Envio de email de prueba";
					break;
			}
			
			Application::setWidgetParameters("emails_form", $formParameters);
		}
		return $retVal;
	}
	
	public function run() {
		parent::run();
		
		
	}

	protected function prepareUpdate() {
	
		$db=Application::getDatabase();
		if (!$_POST) {
			foreach($this->fields as $field => &$data) {
				$data["value"]="";
			}
			//$this->fields["from_name"]["value"]="Daniel Marjos";
			//$this->fields["from_email"]["value"]="marjosdaniel@gmail.com";
		} else {
			$this->updateRecord();
		}
	
		//Application::dumpConfig();
		return true;
	}
	
  	protected function updateRecord() {
  		$mail = new PHPMailer();
  		$mail->isSMTP();
  		$mail->SMTPDebug = 2;
  		$mail->Debugoutput="echo";
  		$mail->Host = Application::get("SMTP_HOST");
  		$mail->Port = Application::get("SMTP_PORT");
  		$mail->SMTPAuth = true;
  		if (Application::get("SMTP_SECURE"))
  			$mail->SMTPSecure = 'tls';
  		else
  			$mail->SMTPSecure = '';
  		$mail->Username = Application::get("SMTP_USER");
  		$mail->Password = Application::get("SMTP_PASS");
		$mail->setFrom($_POST["from_email"],$_POST["from_name"]);  		
		$mail->addAddress($_POST["to_email"],$_POST["to_name"]);
		$mail->Subject = $_POST["subject"];
			
		$body=nl2br(utf8_decode($_POST["texto"]));
		$mail->Body=$body;
		$mail->AltBody=strip_tags(str_replace("<br/>","\n",$body));
			
		if (!$mail->send())
			die($mail->ErrorInfo);
		die(); 
		Application::redirect("/admin/diagnosticos/emails");
  	}
  	
}
