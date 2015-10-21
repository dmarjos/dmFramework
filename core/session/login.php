<?php
Application::Uses("sys.web.TComponent");
Application::Uses('sys.tools.passwordUtils');
class login extends TComponent {
	
	public $wrapper="";
	public $ip=null;
	public $errors=array();
	private $sessionVariable="usuario";
	
	public function __construct($variable=false) {
		if (!$variable) $variable=Application::get("FRONTEND_SESSION_VAR");
		$this->sessionVariable=$variable;
		$this->create();
	}
	
	public function create() {
		parent::create();
		$db=Application::getDatabase();
	}
	
	public function run() {
		if ($_POST['LoginForm']) {
			$db = Application::GetDatabase();
			$username = trim($_POST['user']);
      		$pass = trim($_POST['pass']);
      		if (empty($username)) { $this->errors[] = "El nombre de usuario no puede quedar vac&iacute;o."; }
      		if (empty($pass)) { $this->errors[] = "La contrase&ntilde;a no puede quedar vac&iacute;a."; }
      		if (empty($this->errors)) {
        		try {
        			//	"LEFT JOIN tbl_usuario_role ON rol_codigo = usr_codrol ".
        		
          			$sql="SELECT * FROM tbl_usuario ".
                            "WHERE ".
                            "usr_user = '".$db->escape($username)."' or usr_email = '".$db->escape($username)."'";
        			$user = $db->getRow($sql);
 					if (empty($user)) {
            			$this->errors[] = "Nombre de Usuario incorrecto.";
            			$_SESSION["mensaje"]=array("text"=>"Nombre de usuario o contraseña incorrecta","level"=>3);
          			} else {
          				//2425d4bf627b07ca3b0c7aa3e154a56b152bb456
          				$passOk=passwordUtils::createHash($pass,$user["usr_pass"]);
          				if ($passOk!=$user["usr_pass"]) {
          					$this->errors[] = "La contrase&ntilde;a es incorrecta.";
            				$_SESSION["mensaje"]=array("text"=>"Nombre de usuario o contraseña incorrecta","level"=>3);
          				} else {
			    	        // 	Comprobar estados de cuenta.
							switch ($user['usr_estado']) {
	              				case UserStates::WAITING:  $_SESSION["mensaje"]=array("text"=>"La cuenta no ha sido validada aun.","level"=>3); break;
	              				case UserStates::LOCKED:  $_SESSION["mensaje"]=array("text"=>"Su cuenta ha sido bloqueada.","level"=>3); break;
	              				case UserStates::BANNED:  $_SESSION["mensaje"]=array("text"=>"Su cuenta ha sido cerrada.","level"=>3); break;
	              				default: 
	              					$_SESSION[$this->sessionVariable] = $user['usr_codigo'];
	                       			$this->logUser(intval($user['usr_codigo']));
	                       			if ($_POST["recordar"]=="1") {
	                       				setcookie(Application::get("USER_COOKIE"),$user['usr_codigo'],time()+(24*3600*30));
	                       				Application::Redirect("/");
	                       			}
	            			}
          				}
         			} 
				} catch (Exception $e) {
          			$this->errors[] = "Se produjo un error al procesar el formulario, intente nuevamente.";
        		}
      		} else {
      			$_SESSION["mensaje"]=array("text"=>"Nombre de usuario o contraseña incorrecta","level"=>3);
      		}
    	}
    	return $this->getUserInfo();
	} 
	
  	public function getUserInfo($codusr=null,$fullRoles=false) {
  		
    	$db = Application::GetDatabase();
    	$codusr = $codusr === null ? intval($_SESSION[$this->sessionVariable]) : intval($codusr);
    	//"LEFT JOIN tbl_usuario_role ON rol_codigo = usr_codrol ".
    	try {
    	$user = $db->getRow("SELECT * FROM tbl_usuario ".
			"WHERE ".
			"usr_codigo = $codusr");
    	} catch (Exception $e) {
    		
    	}
    	if (!empty($user)) {
			$roles=explode(",",$user["usr_codrol"]); //LEFT JOIN tbl_usuario_role ON usr_codrol = rol_codigo
			$sql="SELECT * from tbl_usuario_role where rol_codigo in('".implode("','",$roles)."')";
      		$res=$db->execute("SELECT * from tbl_usuario_role where rol_codigo in('".implode("','",$roles)."')");
      		$rules=array();
      		$rolNombre="";
      		$rolNombres=array();
      		while($rec=$db->getNextRecord($res)) {
      			if (!$fullRoles) {
	      			if (empty($rolNombre))
	      				$rolNombre=$rec['rol_nombre'];
      			} else {
      				$rolNombres[]=$rec['rol_nombre'];
      			}
      			$rules=array_merge($rules,unserialize($rec['rol_rules']));
      		}
      		$user['rol_rules'] = $rules; //unserialize($user['rol_rules']); 
      		if (!$fullRoles) 
      			$user['rol_nombre'] = $rolNombre; //unserialize($user['rol_rules']);
      		else 
      			$user['rol_nombre']=implode(' / ',$rolNombres);
      		//$user['rol_rules'] = unserialize($user['rol_rules']);
    	}
   	 
    	return $user;
  	}

  	private function getIP() {
	    // Only get IP once
	    if ($this->ip === null) {
	    	$this->ip = 'Unresolved';
	      	foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $var) {
	        	if (empty($_SERVER[$var])) continue;
				// IP found
	        	$curip = $_SERVER[$var];
	        	$curip = explode('.', $curip);
	        	// Valid IPv4?
	        	if (count($curip) === 4) {
	          		$this->ip = implode('.',$curip);
	          		break;
	        	}
	      	}
	    }
    	return $this->ip;
  	}
	
  	private function logUser($id) {
      	$db = Application::GetDatabase();
  		if (!$db->table_exists("log_usuario"))
  			$this->createTableLogUsuario();
    	if (Application::get("LOG_USERS")) {
      		try {
        		$ip = $this->getIP();
        		$ua = $_SERVER['HTTP_USER_AGENT'];
        		$date = date('Y-m-d H:i:s');
        		$host = $ip != 'Unresolved' ? @gethostbyaddr($ip) : 'Unresolved';
        		$db->execute("UPDATE tbl_usuario SET usr_uvisita = '$date' WHERE usr_codigo = $id");
        		$db->execute("INSERT INTO log_usuario (log_codusr, log_ip, log_host, log_uagent, log_fecha) VALUES (".
                     "'".intval($id)."',".
                     "'".$ip."',".
                     "'".$db->escape($host)."',".
                     "'".$db->escape($ua)."',".
                     "'".$date."')");
      		} catch (Exception $e) {}
    	}
  	}
  	
  	
	/**
	 * database related methods
	 */
	private function createTableUsuario() {
		$sql="CREATE TABLE `tbl_usuario` (`usr_codigo` int(11) unsigned NOT NULL AUTO_INCREMENT, `usr_parent` int(11), `usr_codrol` varchar(254) NOT NULL DEFAULT '0', `usr_estado` int(11) NOT NULL DEFAULT '0', `usr_user` varchar(15) NOT NULL DEFAULT '', `usr_pass` varchar(40) NOT NULL DEFAULT '',`usr_nombre` varchar(255) NOT NULL DEFAULT '', `usr_apellido` varchar(255) NOT NULL DEFAULT '', `usr_pais` varchar(255) NOT NULL, `usr_ciudad` varchar(255) NOT NULL, `usr_direccion` varchar(255) NOT NULL DEFAULT '', `usr_telefono` varchar(255) NOT NULL DEFAULT '', `usr_email` varchar(255) NOT NULL DEFAULT '', `usr_empresa` varchar(255) NOT NULL DEFAULT '', usr_puntos int(11), `usr_registrado` datetime NOT NULL DEFAULT '0000-00-00 00:00:00', `usr_uvisita` datetime NOT NULL DEFAULT '0000-00-00 00:00:00', PRIMARY KEY (`usr_codigo`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";
    	$db = Application::GetDatabase();
		$db->execute($sql);
		
		
		$sql="INSERT INTO 
			`tbl_usuario` (
				`usr_codigo`, 
				`usr_parent`, 
				`usr_codrol`, 
				`usr_estado`, 
				`usr_user`, 
				`usr_pass`, 
				`usr_nombre`, 
				`usr_apellido`, 
				`usr_pais`, 
				`usr_ciudad`, 
				`usr_direccion`, 
				`usr_telefono`, 
				`usr_email`, 
				`usr_empresa`, 
				`usr_puntos`, 
				`usr_registrado`, 
				`usr_uvisita`)
			VALUES
				(null,0, 1, 1, 'root', '".passwordUtils::createHash('admin')."', 'Administrador','General', '', '', '', '', '".Application::Get('ADMIN_EMAIL')."', '', 0, now(), '0000-00-00 00:00:00');";
		$db->execute($sql);
	}

	private function createTableUsuarioPerfil() {
		$sql="CREATE TABLE `tbl_usuario_perfil` (  `per_codigo` int(11) NOT NULL AUTO_INCREMENT, `usr_codigo` int(11) NOT NULL, `per_fecha_nacimiento` date NOT NULL, `per_imagen` varchar(40) NOT NULL, `per_descripcion` varchar(400) NOT NULL, PRIMARY KEY (`per_codigo`) ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
		$db = Application::GetDatabase();
		$db->execute($sql);
	}
	
	private function createTableUsuarioRule() {
		$sql="CREATE TABLE IF NOT EXISTS `tbl_usuario_rule` (`rul_codigo` int(11) unsigned NOT NULL AUTO_INCREMENT, `rul_codrul` int(11) NOT NULL DEFAULT '0', `rul_rules` int(11) NOT NULL DEFAULT '0', `rul_nombre` varchar(25) NOT NULL DEFAULT '', `rul_descripcion` varchar(255) NOT NULL DEFAULT '', PRIMARY KEY (`rul_codigo`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;";	
    	$db = Application::GetDatabase();
	//	$db->execute("drop table `tbl_usuario_rule`;");
		$db->execute($sql);
		$sql="INSERT INTO `tbl_usuario_rule` (`rul_codigo`, `rul_codrul`, `rul_rules`, `rul_nombre`, `rul_descripcion`) VALUES
(1, 0, 1, 'backend', 'Herramientas Administrativas'),
(2, 1, 1, 'administracion', 'Menu Administracion'),
(3, 2, 15, 'usuarios', 'Usuarios'),
(4, 2, 15, 'roles', 'Roles y Reglas'),
(5, 4, 1, 'reglas', 'Reglas'),
(6, 0, 1, 'modulos', 'Modulos'),
(7, 0, 15, 'categoriasusuarios', 'Tipos de usuario'),
(8, 7, 15, 'administrator', 'Administrador'),
(9, 7, 15, 'user', 'Usuario'),
(12, 6, 15, 'categoriasposts', 'Categorias'),
(17, 7, 15, 'moderator', 'Moderador');";
		try {
			$db->execute($sql);
		} catch (Exception $e) {
		}
	}	

	private function createTableUsuarioRole() {
		$sql="CREATE TABLE `tbl_usuario_role` (`rol_codigo` int(11) unsigned NOT NULL AUTO_INCREMENT, `rol_front` int(11) NOT NULL DEFAULT '0', `rol_nombre` varchar(255) NOT NULL DEFAULT '', `rol_rules` text NOT NULL, PRIMARY KEY (`rol_codigo`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";
    	$db = Application::GetDatabase();
//		$db->execute("drop table `tbl_usuario_role`;");
    	$db->execute($sql);
		
		$sql="INSERT INTO `tbl_usuario_role` (`rol_codigo`, `rol_front`, `rol_nombre`, `rol_rules`) VALUES
(1, 0, 'Super Administrador', 'a:5:{s:7:\"backend\";i:1;s:14:\"administracion\";i:1;s:5:\"roles\";i:15;s:6:\"reglas\";i:1;s:8:\"usuarios\";i:15;}'),
(2, 0, 'Administrador', 'a:5:{s:7:\"backend\";i:1;s:13:\"administrator\";i:15;s:14:\"administracion\";i:1;s:8:\"usuarios\";i:15;s:15:\"categoriasposts\";i:15;}'),
(5, 0, 'Moderador', 'a:1:{s:9:\"moderator\";i:15;}'),
(4, 0, 'Usuario', 'a:1:{s:4:\"user\";i:15;}');";
		$db->execute($sql);
	}
	
	private function createTableLogUsuario() {
		$sql="CREATE TABLE `log_usuario` (`log_codigo` bigint(20) unsigned NOT NULL AUTO_INCREMENT, `log_codusr` int(11) NOT NULL DEFAULT '0', `log_ip` varchar(50) NOT NULL DEFAULT '', `log_host` varchar(255) NOT NULL DEFAULT '', `log_uagent` varchar(255) NOT NULL DEFAULT '', `log_fecha` datetime NOT NULL DEFAULT '0000-00-00 00:00:00', PRIMARY KEY (`log_codigo`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";
    	$db = Application::GetDatabase();
		$db->execute($sql);
	}

}