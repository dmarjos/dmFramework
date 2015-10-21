<?php
Application::Uses("com.admin.PanelAdmin");
class reset_password extends PanelAdmin {

	public function create() {
		$this->rules="";
		parent::create();
	}
	public function init() {
		$db=Application::GetDatabase();
		if (!$_GET["hash"]) {
			Application::Redirect('/admin');
			die();
		}
		$request=$db->getRow("select * from tbl_usuario where usr_hash='{$_GET["hash"]}'");
		if (empty($request)) {
			$this->view->assign("template","admin/no_request.tpl");
		} else {
			
			$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789#$%&!";
			$pass = '';                           //password is a string
			$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
			for ($i = 0; $i < 12; $i++) {
				$n = mt_rand(0, $alphaLength);
				$pass = $pass.$alphabet[$n];      //append a random character
			}
						
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
			$mail->addAddress($request["usr_email"], $request["usr_nombre"]);
			$mail->Subject = "Solicitud de restablecimiento de contraseña";
			
			$textoEmail="Estimado {$request["usr_nombre"]}.<br/>Se ha restablecido tu contrase&ntilde;a. Tu nueva contrase&ntilde; es {$pass}.<br/>Recuerda por favor cambiarla una vez que ingreses a tu cuenta de usuario.";
	
			$hashedPassword=passwordUtils::createHash($pass);
			
			$db->execute("update tbl_usuario set usr_pass='{$hashedPassword}' where usr_codigo='{$request["usr_codigo"]}'");
			
			$mail->Body=$textoEmail;
			$mail->AltBody=strip_tags(str_replace("<br/>","\n",$textoEmail));
			
			//send the message, check for errors
			if ($mail->send()) {
				Application::Redirect('/admin');
			} else
				die("<pre>".$mail->ErrorInfo);
		}
		return parent::init();
	}
	
}