<?php
Application::Uses("sys.constants.UserRules");
Application::Uses("sys.web.WebAdminAjax");
Application::Uses("sys.tools.DataTables");
Application::Uses('sys.tools.stringUtils');
Application::Uses('sys.tools.passwordUtils');
Application::Uses('sys.tools.curl');

class perfiles extends WebAdminAjax {
	
  	public $rules    = 'accesoblog';
  	
  	public function create() {
  		parent::create();
  	}
  	
  	public function parseURL() {
  		
  		$url=parse_url($_POST["url"]);
  		$keys=array_keys($url);
  		if (count($keys)>1 || !isset($url["path"]))
  			$this->data=array("status"=>"error","message"=>"Debe indicar unicamente el ID, sin la URL");
  		else 
  			$this->data=array("status"=>"ok");
   	}

  	public function validarRedesSociales() {
  		
  		$id=$_POST["id"];
		switch($_POST["network"]) {
			case "facebook":
				$url="https://graph.facebook.com/{$id}";
				$retVal=file_get_contents($url);
				$info=json_decode($retVal);
				if (is_null($info))  {
  					$this->data=array("status"=>"error","message"=>"El ID indicado no existe. Verifica que este bien escrito.");
  					return;
				}
				break;
			case "twitter":
				$url="https://twitter.com/{$id}";
				$retVal=file_get_contents($url);
				if (is_bool($retVal))  {
  					$this->data=array("status"=>"error","message"=>"El ID indicado no existe. Verifica que este bien escrito.");
  					return;
				}
				break;
		}  		
		/*
  		$curl=new curl($url);
  		$curl->setOption(CURLOPT_HEADER, true);
  		$curl->setOption(CURLOPT_FOLLOWLOCATION, true);
  		$retVal=$curl->execute();
  		*/
		

		$this->data=array("status"=>"ok");
  	}

  	public function validarEmail() {
		$db=Application::GetDatabase();
		$sql="select * from tbl_usuario where usr_email='{$_POST["email"]}' and usr_codigo<>'{$_SESSION[Application::get("BACKEND_SESSION_VAR")]}'";
		$rec=$db->getRow($sql);
//		$this->data=array("status"=>"error","message"=>"La direcci&oacute;n de email ingresada pertenece a otro usuario");
		if (!empty($rec)) {
			$this->data=array("status"=>"error","message"=>"La direccion de email ingresada pertenece a otro usuario");
		} else
			$this->data=array("status"=>"ok");
  	}
  	
  	public function uploadPicture() {
  		
  		$valid_formats = array("jpg", "png", "jpeg");
  		if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST") {
  			$name = strtolower($_FILES['upload-image']['name']);
  			$size = $_FILES['upload-image']['size'];
  			if(strlen($name)) {
  				list($txt, $ext) = explode(".", $name);
  				if(in_array($ext,$valid_formats)) {
  					if($size<(1024*1024)) {// Image size max 1 MB
  						$tmp = $_FILES['upload-image']['tmp_name'];
  						$fileName=md5($_SESSION[Application::get("BACKEND_SESSION_VAR")]).".jpg";
  						if(move_uploaded_file($tmp, $_SERVER["DOCUMENT_ROOT"].Application::getPath("/resources/img/perfiles/{$fileName}"))) {
	  						$db=Application::getDatabase();
	  						$perfil=$db->getRow("select * from tbl_usuario_perfil where usr_codigo='{$_SESSION[Application::get("BACKEND_SESSION_VAR")]}'");
	  						if (!$perfil) {
	  							$sql="INSERT INTO tbl_usuario_perfil SET per_imagen='{$fileName}',usr_codigo='{$_SESSION[Application::get("BACKEND_SESSION_VAR")]}'";
	  						} else {
	  							$sql="UPDATE tbl_usuario_perfil SET per_imagen='{$fileName}' where usr_codigo='{$_SESSION[Application::get("BACKEND_SESSION_VAR")]}'";
	  						}
	  						
	  						$db->execute($sql);
							$this->data=array("status"=>"ok","imagePath"=>Application::getPath("/resources/img/perfiles/{$fileName}"));
  						} else
  							$this->data=array("status"=>"error","message"=>"Error al recibir el archivo");
  					} else
  						$this->data=array("status"=>"error","message"=>"Archivo demasiado grande. Max: 1Mb");
  				} else
  					$this->data=array("status"=>"error","message"=>"Formato de archvio invalido");
   			} else
  				$this->data=array("status"=>"error","message"=>"Seleccione una imagen, por favor!");
  		}
   	}
  	
  	public function removeProfilePicture() {
		$id=$_POST["id"];
		$db=Application::getDatabase();
		$perfil=$db->getRow("select * from tbl_usuario_perfil where usr_codigo='{$_SESSION[Application::get("BACKEND_SESSION_VAR")]}'");
		if ($perfil) {
			$sql="UPDATE tbl_usuario_perfil SET per_imagen='' where usr_codigo='{$_SESSION[Application::get("BACKEND_SESSION_VAR")]}'";
		}
		
		$db->execute($sql);		
		$this->data=array("status"=>"ok","imagePath"=>Application::getPath("/resources/img/perfiles/default.jpg"));
  	}
  	
	public function getProfilePicture() {
		$id=$_POST["id"];
		switch($_POST["network"]) {
			case "facebook":
				$url="https://graph.facebook.com/{$id}/picture?type=large";
				$retVal=file_get_contents($url);
				if (is_bool($retVal))  {
					$this->data=array("status"=>"error","message"=>"No se pudo obtener la imagen de perfil de Facebook.");
					return;
				}
				$fileName=md5($_SESSION[Application::get("BACKEND_SESSION_VAR")]).".jpg";
				file_put_contents($_SERVER["DOCUMENT_ROOT"].Application::getPath("/resources/img/perfiles/{$fileName}"),$retVal);

				$db=Application::getDatabase();
				$perfil=$db->getRow("select * from tbl_usuario_perfil where usr_codigo='{$_SESSION[Application::get("BACKEND_SESSION_VAR")]}'");
				if (!$perfil) {
					$sql="INSERT INTO tbl_usuario_perfil SET per_imagen='{$fileName}',usr_codigo='{$_SESSION[Application::get("BACKEND_SESSION_VAR")]}'";
				} else {
					$sql="UPDATE tbl_usuario_perfil SET per_imagen='{$fileName}' where usr_codigo='{$_SESSION[Application::get("BACKEND_SESSION_VAR")]}'";
				}
				
				$db->execute($sql);		
				$this->data=array("status"=>"ok","imagePath"=>Application::getPath("/resources/img/perfiles/{$fileName}"));
				return;
				break;
		}
		
		$this->data=array("status"=>"ok","imagePath"=>Application::getPath("/resources/img/perfiles/default.jpg"));
	}  	
	
	function changeSecurityQuestion() {
		$db=Application::getDatabase();
		$record=$db->getRow("select * from tbl_usuario where usr_codigo='{$_SESSION[Application::get("BACKEND_SESSION_VAR")]}'");
		$passOk=passwordUtils::createHash($_POST["password"],$record["usr_pass"]);
		if ($passOk!=$record["usr_pass"]) {
			$this->data = array("status"=>"error","message"=>"La contrase&ntilde;a es incorrecta.");
			return;
		}

		$rec=$db->getRow("select * from tbl_usuario_perfil where usr_codigo='{$_SESSION[Application::get("BACKEND_SESSION_VAR")]}'");
		if (empty($rec)) {
			$sql="insert into tbl_usuario_perfil ";
			$where="";
		} else {
			$sql="update tbl_usuario_perfil ";
			$where=" where usr_codigo='{$_SESSION[Application::get("BACKEND_SESSION_VAR")]}'";
		}
		$respuesta=Application::escape($_POST["answer"]);
		$sql.="set pre_codigo='{$_POST["question"]}', per_respuesta='{$respuesta}'".$where;
		
        try {
			$db->execute($sql);
        	$this->data = array("status"=>"ok");
        } catch (Exception $e) {
        	$this->data = array("status"=>"error","message"=>"error sql: $sql");
        }
	}
	
	function changePassword() {
		$db=Application::getDatabase();
		$record=$db->getRow("select * from tbl_usuario where usr_codigo='{$_SESSION[Application::get("BACKEND_SESSION_VAR")]}'");
		$passOk=passwordUtils::createHash($_POST["oldPassword"],$record["usr_pass"]);
		
        if ($passOk!=$record["usr_pass"]) {
        	$this->data = array("status"=>"error","message"=>"La contrase&ntilde;a es incorrecta.");
        	return;
        }
        $newPass=passwordUtils::createHash($_POST["newPassword"]);
        $sql="update tbl_usuario set usr_pass='{$newPass}' where usr_codigo='{$_SESSION[Application::get("BACKEND_SESSION_VAR")]}'";
        try {
			$db->execute($sql);
        	$this->data = array("status"=>"ok");
        } catch (Exception $e) {
        	$this->data = array("status"=>"error","message"=>"error sql: $sql");
        }
	}
}
