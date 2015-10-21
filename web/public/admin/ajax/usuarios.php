<?php
Application::Uses("sys.constants.UserRules");
Application::Uses("sys.web.WebAdminAjax");
Application::Uses("sys.tools.DataTables");
Application::Uses("com.tools.usersUtilities");

class usuarios extends WebAdminAjax {
	
  	public $rules    = 'usuarios';
  	
  	public function create() {
  		parent::create();
  	}
  	

  	public function cambiarEstado() {
  		if ($_POST["usuario"]==1) {
  			$this->data=array("error"=>"El administrador general no puede ser deshabilitado");
  			return;
  		}
  		$this->data=array("usuario"=>$_POST["usuario"]);
  		$this->setStatus("tbl_usuario","usr_estado",$_POST["valor"],"usr_codigo='{$_POST["usuario"]}'");
  	}

  	public function getUsers() {
  		$_POST["type"]="json";
  		
  		$params=$_POST;
  		
  		$params["fields_tables"]="
  			usr.usr_codigo,
  			usr.usr_estado,
  			rol.rol_nombre,
  			usr.usr_user,
  			usr.usr_nombre,
  			usr.usr_apellido,
  			usr.usr_uvisita 
  		from 
  			tbl_usuario usr 
  				left join tbl_usuario_role rol on usr.usr_codrol=rol.rol_codigo
  				";
  		
  		$params["table"]="tbl_usuario inner join tbl_usuario_role on tbl_usuario.usr_codrol=tbl_usuario_role.rol_codigo";
  		$params["where"]="usr_parent='0' and rol_rules like '%backend%'";
  		$params["id_field"]="usr_codigo";
  		$dt=new DataTables($params);
	  		
  		$dt->setColumns(array('usr_estado', 'rol_nombre', 'usr_user', 'usr_nombre',  'usr_apellido', 'usr_uvisita'));
  		
		$this->data=$dt->getData();  		
  	}
  	
  	public function getEmpresas() {
  		$_POST["type"]="json";
  		
  		$params=$_POST;
  		
  		$params["fields_tables"]="
  			usr.usr_codigo,
  			usr.usr_estado,
  			usr.usr_uvisita,
  			emp.* 
  		from 
  			tbl_usuario usr 
  				inner join tbl_empresas emp using(usr_codigo)
  				";
  		
  		$params["table"]="tbl_usuario inner join tbl_empresas emp using(usr_codigo)";
  		$params["where"]="usr_parent='0'";
  		$params["id_field"]="usr_codigo";
  		$dt=new DataTables($params);
	  		
  		$dt->setColumns(array('usr_estado', 'emp_nombre', 'usr_uvisita'));
  		
		$this->data=$dt->getData();  		
  	}
  	
  	public function getCustomers() {
  		$_POST["type"]="json";
  		
  		$params=$_POST;
  		
  		$params["fields_tables"]="
  			usr.usr_codigo,
  			usr.usr_estado,
  			usr.usr_user,
  			usr.usr_nombre,
  			usr.usr_apellido,
  			usr.usr_uvisita 
  		from 
  			tbl_usuario usr 
  				left join tbl_empresas emp using(usr_codigo)
  				";
  		
  		$params["table"]="tbl_usuario left join tbl_empresas emp using(usr_codigo)";
  		$params["where"]="usr_parent='0' and emp.emp_codigo is null and (usr_user<>'root' and usr_user not like '%admin%')";
  		$params["id_field"]="usr_codigo";
  		$dt=new DataTables($params);
	  		
  		$dt->setColumns(array('usr_estado', 'usr_user', 'usr_nombre',  'usr_apellido', 'usr_uvisita'));
		$this->data=$dt->getData();  		
  	}
  	
  	public function getLog() {
  		$_POST["type"]="json";
  		
  		$params=$_POST;
  		$params["table"]="log_usuario inner join tbl_usuario ON log_codusr = usr_codigo";
  		$dt=new DataTables($params);
	  		
  		$dt->setColumns(array( 'usr_nombre', 'log_ip', 'log_uagent', 'log_fecha'));
  		
		$this->data=$dt->getData();  		
  	}
  	
  	public function validarUsuario() {
  		$urlParsed=parse_url($_POST["referrer"]);
  		$queryString=$urlParsed["query"];
  		$parsedData=array();
  		parse_str($queryString,$parsedData);
  		
  		$db=Application::getDatabase();
  		
  		if (!isset($parsedData["id"])) {
  			$row=$db->getRow("SELECT * from tbl_usuario where usr_user='{$_POST["usuario"]}'");
  			if (!empty($row))
  				$this->data=array("status"=>"error","message"=>"El nombre de usuario ya existe");
			else {
				$row=$db->getRow("SELECT * from tbl_usuario where usr_email='{$_POST["email"]}'");
				if (!empty($row))
					$this->data=array("status"=>"error","message"=>"La direccion de email ya fue utilizada");
				else
  					$this->data=array("status"=>"ok");
			} 
  		} else {
  			$row=$db->getRow("SELECT * from tbl_usuario where usr_email='{$_POST["email"]}' and usr_codigo<>'{$parsedData["id"]}'");
  			if (!empty($row))
  				$this->data=array("status"=>"error","message"=>"La direccion de email ya fue utilizada");
  			else {
	  			$this->data=array("status"=>"ok");
  			}
  		}  		
  	}
}
