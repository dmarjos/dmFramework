<?php
Application::Uses("sys.constants.UserRules");
Application::Uses("sys.web.WebAjax");
Application::Uses("sys.tools.DataTables");
Application::Uses("com.tools.usersUtilities");
Application::Uses("com.tools.emailUtilities");
Application::Uses('sys.tools.passwordUtils');
class registro extends WebAjax {

	public $rules    = null;
	 
	public function create() {
		parent::create();
	}

	public function crearCuenta() {
		$db=Application::getDatabase();
		$row=$db->getRow("SELECT * from tbl_usuario where usr_email='{$_POST["email"]}'");
		if (!empty($row))
			$this->data=array("status"=>"error","message"=>"La direccion de email ya fue utilizada");
		else {
			$this->data=array("status"=>"ok");

			$data=array(
				"usr_pass"=>passwordUtils::createHash($_POST["password1"]),
				"usr_user"=>"u_".md5($_POST["email"]),
				"usr_nombre"=>$_POST["nombre"],
				"usr_apellido"=>$_POST["apellido"],
				"usr_estado"=>0,
				"usr_parent"=>0,
				"usr_codrol"=>3,
				"usr_registrado"=>date("Y-m-d H:i:s",time()),
				"usr_email"=>$_POST["email"],
			);
			$usr_codigo=$db->insert("tbl_usuario",$data);
			$db->update("tbl_usuario",array("usr_empresa"=>md5($usr_codigo)),"usr_codigo='{$usr_codigo}'");
			
			$email=new emailUtilities();
			$email->setVariable("nombre_sitio",Application::get('SYSTEM_TITLE'));
			$email->setVariable("link","http://".$_SERVER["SERVER_NAME"].Application::GetLink("/validar-email?hash=".md5($usr_codigo)));
			$result=$email->sendMailByAddress("",$_POST["email"],"validar_email");
		}		
		$_POST["type"]="json";
		header("Content-Type: text/json");
		die(json_encode($this->data));
	}
	
	public function crearCuentaEmpresa() {
		$db=Application::getDatabase();
		$row=$db->getRow("SELECT * from tbl_usuario where usr_email='{$_POST["email"]}'");
		if (!empty($row))
			$this->data=array("status"=>"error","message"=>"La direccion de email ya fue utilizada");
		else {
			$this->data=array("status"=>"ok");

			$data=array(
				"usr_pass"=>passwordUtils::createHash($_POST["password1"]),
				"usr_user"=>"u_".md5($_POST["email"]),
				"usr_nombre"=>'',
				"usr_apellido"=>'',
				"usr_estado"=>0,
				"usr_parent"=>0,
				"usr_codrol"=>3,
				"usr_registrado"=>date("Y-m-d H:i:s",time()),
				"usr_email"=>$_POST["email"],
			);
			$usr_codigo=$db->insert("tbl_usuario",$data);
			$db->update("tbl_usuario",array("usr_hash"=>md5($usr_codigo)),"usr_codigo='{$usr_codigo}'");

			$data=array(
				"usr_codigo"=>$usr_codigo,
				"emp_nombre"=>$_POST["nombre"],
				"emp_direccion"=>$_POST["direccion"],
				"emp_ciudad"=>$_POST["localidad"],
				"emp_pais"=>'',
				"emp_telefono"=>$_POST["telefono"],
			);
			$db->insert("tbl_empresas",$data);
			
			$email=new emailUtilities();
			$email->setVariable("nombre_sitio",Application::get('SYSTEM_TITLE'));
			$email->setVariable("link","http://".$_SERVER["SERVER_NAME"].Application::GetLink("/validar-email?hash=".md5($usr_codigo)));
			$result=$email->sendMailByAddress("",$_POST["email"],"validar_email");
		}		
		$_POST["type"]="json";
		header("Content-Type: text/json");
		die(json_encode($this->data));
	}
	
	function validateUserData() {
		$db=Application::getDatabase();
		if ($_POST["usr_user"]) {
			$user=$db->getRecord("tbl_usuario","usr_user='{$_POST["usr_user"]}' and usr_codigo<>'{$_SESSION[Application::get("FRONTEND_SESSION_VAR")]}'");
			if ($user) $this->returnData(array("status"=>"error","message"=>"El nombre de usuario elegido pertenece a otro usuario"));
		}
		$user=$db->getRecord("tbl_usuario","usr_email='{$_POST["usr_email"]}' and usr_codigo<>'{$_SESSION[Application::get("FRONTEND_SESSION_VAR")]}'");
		if ($user) $this->returnData(array("status"=>"error","message"=>"La direccion de email elegida pertenece a otro usuario"));
		$this->returnData(array("status"=>"ok"));
	}
	
	function validateRegistry() {
		$db=Application::getDatabase();
		$user=$db->getRecord("tbl_usuario","usr_email='{$_POST["usr_email"]}'");
		$this->returnData(array("status"=>"ok"));
		if ($user) {
			$this->returnData(array("status"=>"error","message"=>"La direccion de email elegida pertenece a otro usuario"));
		} else if (Application::get("CLOSED_REGISTRATION")===true) {
			$invite=$db->getRecord("tbl_invitaciones_usuarios","inv_email='{$_POST["usr_email"]}'");
			if (!$invite) $this->returnData(array("status"=>"error","message"=>"La direccion de email no ha recibido una invitaci&oacute; para registrarse."));
		}
		
	}
	
	function returnData($data) {
		header('Content-Type: text/json');
		die(json_encode($data));
	}
	
	function checkFacebookLogin() {
		$_POST["type"]="json";
		
		$db=Application::getDatabase();
		$fbUser=$db->getRecord("tbl_usuario","fbk_codigo='{$_POST["id"]}' or usr_email='{$_POST["email"]}'");
		if (!$fbUser) {
			$invite=$db->getRecord("tbl_invitaciones_usuarios","inv_email='{$_POST["email"]}'");
			if (Application::Get("CLOSED_REGISTRATION")) {
				if (!$invite) {
					$this->returnData(array("status"=>"error","message"=>"La direccion de email no ha recibido una invitaci&oacute; para registrarse."));
				}
			}
			 
			$fbUser=array(
				"usr_email"=>$_POST["email"],
				"usr_user"=>"u_".md5($_POST["email"]),
				"usr_nombre"=>$_POST["first_name"],
				"usr_apellido"=>$_POST["last_name"],
				"usr_codrol"=>3,
				"usr_invitaciones"=>usersUtilities::getMaxInvites($_POST["email"]),
				"usr_parent"=>0,
				"fbk_codigo"=>$_POST["id"],
				"usr_registrado"=>date("Y-m-d H:i:s",time()),
				"usr_uvisita"=>date("Y-m-d H:i:s",time()),
				"usr_estado"=>1
			);
			$usr_codigo=$db->insert("tbl_usuario",$fbUser);
			setcookie("primer_ingreso","1",time()+(365*24*3600),Application::getLink("/"));
			$fbUser["usr_codigo"]=$usr_codigo;
			if ($invite) {
				$db->insert("tbl_relaciones",array(
					"usr_codsrc"=>$invite["usr_codigo"],
					"usr_coddst"=>$fbUser["usr_codigo"],
					"rel_iniciada"=>date("Y-m-d H:i:s",time())
				));
				$db->insert("tbl_relaciones",array(
					"usr_codsrc"=>$fbUser["usr_codigo"],
					"usr_coddst"=>$invite["usr_codigo"],
					"rel_iniciada"=>date("Y-m-d H:i:s",time())
				));
			}
			
		}
		$_SESSION[Application::get("FRONTEND_SESSION_VAR")]=$fbUser["usr_codigo"];
		$this->data=array("status"=>"ok");
	}
	
	function logoutFacebook() {
		unset($_SESSION[Application::get("FRONTEND_SESSION_VAR")]);
		$this->data=array("status"=>"ok");
	}
	
	function checkTwitterLogin() {
		$_POST["type"]="json";
		
		Application::Uses("sys.tools.api.twitter.twitteroauth");
		$twitter=new TwitterOAuth(Application::get("TWITTER_APP_ID"),Application::get("TWITTER_APP_SECRET"));
		$token=$twitter->getRequestToken("http://".$_SERVER["SERVER_NAME"].Application::getLink("/twitter_callback/"));
		
		$this->data=$token;
		$this->data["status"]=($token['oauth_callback_confirmed']?"ok":'error');
		
	}
}