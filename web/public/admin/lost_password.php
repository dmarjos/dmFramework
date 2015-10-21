<?php
Application::Uses("com.admin.PanelAdmin");
class lost_password extends PanelAdmin {

	public function create() {
		$this->rules="";
		parent::create();
		if ($_POST) {
			$this->processPost();
		}
		
	}
	
	private function processPost() {
		$user=trim($_POST["user"]);
		if (empty($user)) 
			Application::redirect("/admin/lost_password");
		
		$db=Application::GetDatabase();
		$usuario=$db->getRow("select * from tbl_usuario where usr_user='{$_POST["user"]}' or usr_email='{$_POST["user"]}'");
		
		if (empty($usuario)) {
			$this->view->assign("template","admin/lost_password_error.tpl");
			return;
		}		
		switch($_POST["step"]) {
			case "1":
				$pregunta=$db->getRow("select * from tbl_usuario_perfil p inner join tbl_preguntas q on p.pre_codigo=q.pre_codigo inner join tbl_usuario u on p.usr_codigo=u.usr_codigo where usr_user='{$_POST["user"]}' or usr_email='{$_POST["user"]}'");
				if (!empty($pregunta)) {
					$this->view->assign("user",$_POST["user"]);
					$this->view->assign("pregunta",$pregunta["pre_texto"]);
					$this->view->assign("template","admin/lost_password_pregunta.tpl");
					return;
				}
				break;
			case "2":
				$pregunta=$db->getRow("select * from tbl_usuario_perfil p inner join tbl_preguntas q on p.pre_codigo=q.pre_codigo inner join tbl_usuario u on p.usr_codigo=u.usr_codigo where usr_user='{$_POST["user"]}' or usr_email='{$_POST["user"]}'");
				if ($pregunta["per_respuesta"]==$_POST["per_respuesta"]) {
					$this->generarEmailPassword();
					return;
				} else {
					$this->view->assign("user",$_POST["user"]);
					$this->view->assign("pregunta",$pregunta["pre_texto"]);
					$this->view->assign("error","Verifica los datos ingresados, ya que tu respuesta es incorrecta");
					$this->view->assign("template","admin/lost_password_pregunta.tpl");
					return;
				}
				break;
		}
		$this->generarEmailPassword();
	}
	
	private function generarEmailPassword() {
		$db=Application::GetDatabase();
		$usuario=$db->getRow("select * from tbl_usuario where usr_user='{$_POST["user"]}' or usr_email='{$_POST["user"]}'");
		Application::Uses("sys.library.phpmailer.core");
		
		$mail = new PHPMailer();
		$mail->isSMTP();
		$mail->SMTPDebug = 0;
		$mail->Host = Application::get("SMTP_HOST");
		$mail->Port = Application::get("SMTP_PORT");
		$mail->SMTPAuth = true;
		$mail->SMTPSecure = 'tls';
		$mail->Username = Application::get("SMTP_USER");
		$mail->Password = Application::get("SMTP_PASS");
		$mail->setFrom(Application::get("SMTP_FROM_EMAIL"), Application::get("SMTP_FORM_NAME"));
		$mail->addAddress($usuario["usr_email"], $usuario["usr_nombre"]);
		$mail->Subject = "Solicitud de restablecimiento de contraseña";
		
		$textoEmail="Estimado {$usuario["usr_nombre"]}.<br/>Se ha solicitado restablecer la contrase&ntilde;a del usuario {$usuario["usr_user"]}. Si este pedido fue inciado por ti, por favor haz click en el link que figura abajo para iniciar el proceso de restablecimiento de la contrase&ntilde;a. Si tu no has solicitado nada, por favor ignora este email, ya que no se llevara a cabo ninguna accion.<br/><br/>[TAG]<br/><br/>Si tu cliente de correo no te permite hacer click en el link, copia y pega la URL en tu navegador.<br/>[LINK]";

		$hash=md5($_POST["user"].date("YmdHis").serialize($usuario));
		
		$now=date("Y-m-d H:i:s",time());
		$db->execute("update tbl_usuario set usr_hash='{$hash}', usr_fecha_reset_password='{$now}' where usr_codigo='{$usuario["usr_codigo"]}'");
		
		$link="http://".$_SERVER["SERVER_NAME"]."/admin/reset_password?hash=".$hash;
		$tag="<a href=\"{$link}\">Haz click aqu&iacute; para restablecer tu contrase&ntilde;a</a>";
		
		$textoEmail=str_replace('[TAG]',$tag,$textoEmail);
		$textoEmail=str_replace('[LINK]',$link,$textoEmail);
		
		$mail->Body=$textoEmail;
		$mail->AltBody=strip_tags(str_replace("<br/>","\n",$textoEmail));
		
		//send the message, check for errors
		if ($mail->send()) {
			$this->view->assign("template","admin/lost_password_finalizado.tpl");
		} else
			die("<pre>".$mail->ErrorInfo);
		
	}
}