<?php
Application::Uses("sys.constants.UserRules");
Application::Uses("com.admin.PanelAdmin");
class usuarios extends PanelAdmin {
	
  	public $rules    = 'usuarios';

  	public function create() {
		parent::create();
		Application::addScript("/resources/js/admin/administracion/usuarios.js");
		$gridParameters=array(
			"title"=>"Listado de Usuarios",
			"search_fields"=>array(
				"rol_nombre"=>"Rol",
				"usr_user"=>"Usuario",
				"usr_nombre"=>"Nombre",
			),
			
			"url"=>Application::GetLink("/admin/ajax/usuarios")."?method=getUsers",
			"form"=>Application::GetLink("/admin/administracion/form_usuarios"),
			"id_field"=>"usr_codigo",
			"columns"=>array(
				array("field"=>"usr_estado","header"=>"Estado","width"=>"83px","formater"=>
					array(
						"class"=>"custom",
						"function"=>"showComboUsuarios",
						"field"=>"usr_codigo",
						"options"=>"1:Activo,0:Inactivo",
						"events"=>"change:cambiarEstado"
				)),
				array("field"=>"rol_nombre","header"=>"Rol","width"=>"125px"),
				array("field"=>"usr_user","header"=>"Usuario","width"=>"125px","sorteable"=>true,"sorted"=>"asc"),
				array("field"=>"usr_nombre","header"=>"Nombre","width"=>"","sorteable"=>true),
				array("field"=>"usr_apellido","header"=>"Apellido","width"=>"","sorteable"=>true),
				array("field"=>"usr_uvisita","header"=>"Ult Visita","width"=>"160px")
		)
		);
		
		Application::setWidgetParameters("users_grid", $gridParameters);
	}

	public function run() {
		parent::run();
	}

}