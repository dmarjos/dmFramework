<?php
Application::Uses("sys.constants.UserRules");
Application::Uses("com.admin.PanelAdminForm");
Application::Uses("sys.tools.passwordUtils");

class form_usuarios extends PanelAdminForm {

	public $rules    = 'usuarios';

	public $fields=array(
		"usuario"=>array(
			"title"=>"Login del usuario",
			"width"=>"114px",
			"type"=>"text",
			"tableField"=>"usr_user"
		),
		"password"=>array(
			"title"=>"Contrase&ntilde;a",
			"width"=>"114px",
			"type"=>"text",
			"tableField"=>"usr_pass"
		),
		"email"=>array(
			"title"=>"E-mail",
			"width"=>"114px",
			"type"=>"text",
			"tableField"=>"usr_email"
		),
		"nombre"=>array(
			"title"=>"Nombre",
			"width"=>"411px",
			"type"=>"text",
			"tableField"=>"usr_nombre"
		),
		"apellido"=>array(
			"title"=>"Apellido",
			"width"=>"411px",
			"type"=>"text",
			"tableField"=>"usr_apellido"
		),
		"rol"=>array(
			"title"=>"Rol del Usuario",
			"width"=>"177px",
			"type"=>"select",
			"optionsFunction"=>"getRoles",
			"tableField"=>"usr_codrol"
		)
	);
	
	public function create() {
		$this->setMainTable("tbl_usuario");
		$this->setIndexField("usr_codigo");
		$this->urlBackTo=Application::getLink("/admin/administracion/usuarios");
		Application::addScript("/resources/js/admin/administracion/usuarios.js");
		//Application::addScript("/resources/js/lib/jquery.multiselect.js");
		//Application::addScript("/resources/js/plugins/bootstrap-multiselect/bootstrap-multiselect.js");
		//Application::addStyle("/resources/css/bootstrap-multiselect/bootstrap-multiselect.css");
		
		parent::create();
		Application::setWidgetParameters("usuarios_form", $formParameters);
	}

	public function init() {
		$retVal=parent::init();
		if ($retVal) {
			$formParameters=array(
				"title"=>"Usuarios",
				"submitByAjax"=>false,
				"submitUrl"=>$_SERVER["REQUEST_URI"],
				"globalValidator"=>"checkRoles",
				"fields"=>$this->fields
			);
			switch($_GET["action"]) {
				case "add":
					$this->mode="INSERT";
					$formParameters["subtitle"]="Nuevo usuario";
					break;
				case "upd":
					$this->mode="UPDATE";
					$formParameters["subtitle"]="Modificar usuario";
					break;
				case "del":
					$this->mode="DELETE";
					$formParameters["subtitle"]="Borrar usuario";
					break;
			}
			
			Application::setWidgetParameters("usuarios_form", $formParameters);
		}
		return $retVal;
	}
	
	public function run() {
		parent::run();
		
		
	}

 	public function getRoles($selected) {
 		$selectedOptions=explode(",",$selected);
    	try {
      		$db = Application::GetDatabase();
      		$recs = $db->execute("SELECT * FROM tbl_usuario_role where rol_rules like '%backend%' ORDER BY rol_nombre asc");
      		$retVal=""; //<option value=\"\">Seleccione el rol correspondiente</option>";
      		while ($rec=$db->getNextRecord($recs)) {
        		$select = (in_array($rec['rol_codigo'],$selectedOptions)) ? ' selected="selected"' : '';
        		$retVal.="<option value=\"{$rec['rol_codigo']}\"$select>".Application::GetString($rec['rol_nombre'])."</option>";
      		}
    	} catch (Exception $e) {
      		$retVal.="<option id=\"-1\">Error al obtener la lista de roles</option>";
    	}
    	
    	return $retVal;
  	}
  	
  	protected function canDeleteRecord() {
  		if (intval($_GET["id"])===1) {
  			return array(
  				"status"=>false,
  				"err_message"=>"El usuario Administrador no puede ser eliminado",
  				"url_back"=>Application::GetLink('/admin/administracion/usuarios')
  			);
  		}
  		return array(
			"status"=>true
  		);
  	}
  	
  	protected function prepareUpdate() {
  		unset($this->fields["password"]);
  		$this->fields["usuario"]["readonly"]=true;
  		parent::prepareUpdate();
  	}

  	protected function insertRecord() {

		$_POST["password"]=passwordUtils::createHash($_POST["password"]);
  		$_POST["parent"]='0';
  		$this->fields["parent"]["tableField"]="usr_parent";
  		$_POST["registrado"]=date('Y-m-d',time());
  		$this->fields["registrado"]["tableField"]="usr_registrado";
  		parent::insertRecord();
  	}

}
