<?php
Application::Uses("com.admin.PanelAdminForm");
Application::Uses('sys.tools.utils.stringUtils');
Application::Uses('sys.tools.utils.passwordUtils');

class editar extends PanelAdminForm {

	private $timeline=array();
	
	public function create() {
		Application::addScript('/resources/js/plugins/noty/jquery.noty.js');
		Application::addScript('/resources/js/plugins/noty/layouts/topCenter.js');
		Application::addScript('/resources/js/plugins/noty/layouts/topLeft.js');
		Application::addScript('/resources/js/plugins/noty/layouts/topRight.js');
		Application::addScript('/resources/js/plugins/noty/themes/default.js');
		Application::addScript("/resources/js/admin/perfiles/editar.js");
		Application::addScript("/resources/js/admin/perfiles/jquery.ajaxform.js");
		
		Application::addScript("/resources/js/plugins/bootstrap-editable/bootstrap-editable.min.js");
		Application::addScript("/resources/js/plugins/daterangepicker/moment.min.js");
		
		parent::create();
	}
	public function init() {
		parent::init();
		if ($_POST) {
			$this->guardarDatos();
			return;
		}
		$db=Application::getDatabase();
		
		$usuario=$db->getRow("select * from tbl_usuario where usr_codigo='{$_SESSION[Application::get("BACKEND_SESSION_VAR")]}'");
		$perfil=$db->getRow("select * from tbl_usuario_perfil where usr_codigo='{$_SESSION[Application::get("BACKEND_SESSION_VAR")]}'");

		if (empty($usuario["usr_email"]))
			$usuario["usr_email"]="<i>No hay email a&uacute;n</i>";
		
		$this->view->assign("usr_codigo",$usuario["usr_codigo"]);
		$this->view->assign("usr_nombre",$usuario["usr_nombre"]);
		$this->view->assign("usr_user",$usuario["usr_user"]);
		$this->view->assign("usr_email",$usuario["usr_email"]);
		$this->view->assign("usr_rol",Application::getUserRoles($usuario["usr_codigo"]));
		
		if (empty($perfil["per_imagen"]))
			$perfil["per_imagen"]="default.jpg";
		
		if (empty($perfil["per_descripcion"]))
			$perfil["per_descripcion"]="<i>No hay descripci&oacute;n a&uacute;n</i>";


		if (empty($perfil["per_fecha_nacimiento"]) || $perfil["per_fecha_nacimiento"]=="0000-00-00")
			$perfil["per_fecha_nacimiento"]="<i>&iquest;Cuando naciste?</i>";
		else if (!empty($perfil["per_fecha_nacimiento"]) && $perfil["per_fecha_nacimiento"]!="0000-00-00")		
			$perfil["per_fecha_nacimiento"]=implode("/",array_reverse(explode("-",$perfil["per_fecha_nacimiento"])));
		
		$perfil["per_imagen"]=Application::GetPath("resources/img/perfiles/".$perfil["per_imagen"]);
		$this->view->assign("perfil",$perfil);
		$this->view->assign("timeline",array());
		return true;
	}

	private function guardarDatos() {
		$db=Application::getDatabase();
		if (substr($_POST["name"],0,4)=="usr_") {
			$sql="UPDATE tbl_usuario SET [fields] where usr_codigo='{$_SESSION[Application::get("BACKEND_SESSION_VAR")]}'";
		} else {
			$perfil=$db->getRow("select * from tbl_usuario_perfil where usr_codigo='{$_SESSION[Application::get("BACKEND_SESSION_VAR")]}'");
			if (!$perfil) {
				$sql="INSERT INTO tbl_usuario_perfil SET [fields],usr_codigo='{$_SESSION[Application::get("BACKEND_SESSION_VAR")]}'";
			} else {
				$sql="UPDATE tbl_usuario_perfil SET [fields] where usr_codigo='{$_SESSION[Application::get("BACKEND_SESSION_VAR")]}'";
			}
		}

		$fields=$_POST["name"]."='".Application::escape($_POST["value"])."'";

		
		$sql=str_replace("[fields]",$fields,$sql);
		$db->execute($sql);		
	}
}