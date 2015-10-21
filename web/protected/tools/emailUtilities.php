<?php
Application::Uses("sys.library.phpmailer.core");
class emailUtilities {
	private $variables=array();
	public $actuallySend=true;
	private $mailer=null;
	
	function __construct() {
		$this->mailer = new PHPMailer();
		$this->mailer->isSMTP();
		$this->mailer->SMTPDebug = 0;
		$this->mailer->Host = Application::get("SMTP_HOST");
		$this->mailer->Port = Application::get("SMTP_PORT");
		$this->mailer->SMTPAuth = true;
		if (Application::get("SMTP_SECURE"))
			$this->mailer->SMTPSecure = 'tls';
		$this->mailer->Username = Application::get("SMTP_USER");
		$this->mailer->Password = Application::get("SMTP_PASS");
		$this->mailer->setFrom(Application::get("SMTP_FROM_MAIL"), Application::get("SMTP_FROM_NAME"));
		
	}
	public function setVariable($var,$value) {
		$this->variables[$var]=$value;
	}
	
	public function replace_all($search,$replacement,$subject) {
		while(strpos($subject,$search)!==false) {
			$subject=str_replace($search,$replacement,$subject);
		}
		return $subject;
	}
	
	public function sendEmail() {
		
	}
	
	public function sendMailByAddress($name,$address,$template) {
		
		$db=Application::getDatabase();
		$emailTemplate=$db->getRow("select * from tbl_email_template where tmp_nombre='{$template}'");
		
		$body=$emailTemplate["tmp_contenido"];

		foreach($this->variables as $var => $val) {
			$body=$this->replace_all("[".$var."]",utf8_decode($val),$body);
			$emailTemplate["tmp_titulo"]=$this->replace_all("[".$var."]",utf8_decode($val),$emailTemplate["tmp_titulo"]);
		}
		if ($this->actuallySend===false)
			$this->mailer->addAddress("desarrollos@danielmarjos.com", "Daniel Marjos");
		else
			$this->mailer->addAddress($address, $name);
		$this->mailer->Subject = utf8_decode($emailTemplate["tmp_titulo"]);
			
		$body=nl2br($body);
		Application::$page->view->assign("EMAIL_CONTENT_FROM_DB",$body);
		Application::$page->view->assign("email_subject",$emailTemplate["tmp_titulo"]);
		Application::$page->view->assign("serverName",$_SERVER["SERVER_NAME"]);
		$emailHtml=$body; //Application::$page->view->fetch("email.tpl");
		
		$this->mailer->Body=utf8_decode($emailHtml);
		$this->mailer->AltBody=utf8_decode(strip_tags(str_replace("<br/>","\n",$body)));
			
		//if (!$this->actuallySend) return true;
		if ($this->mailer->send())
			return true;
		else 
			return $mail->ErrorInfo;
		
	}
	
	public function sendMailByAddresses($addresses,$template) {
		
		$db=Application::getDatabase();
		$emailTemplate=$db->getRow("select * from tbl_email_template where tmp_nombre='{$template}'");
		
		$body=$emailTemplate["tmp_contenido"];

		foreach($this->variables as $var => $val) {
			$body=$this->replace_all("[".$var."]",utf8_decode($val),$body);
			$emailTemplate["tmp_titulo"]=$this->replace_all("[".$var."]",utf8_decode($val),$emailTemplate["tmp_titulo"]);
		}
		
		
		foreach($addresses as $address=>$name)			
			$this->mailer->addAddress($address, $name);
		
		$this->mailer->Subject = $emailTemplate["tmp_titulo"];
			
		$body=nl2br($body);
		Application::$page->view->assign("EMAIL_CONTENT_FROM_DB",$body);
		Application::$page->view->assign("email_subject",$emailTemplate["tmp_titulo"]);
		Application::$page->view->assign("serverName",$_SERVER["SERVER_NAME"]);
		$emailHtml=Application::$page->view->fetch("email.tpl");
		
		$this->mailer->Body=$emailHtml;
		$this->mailer->AltBody=strip_tags(str_replace("<br/>","\n",$body));
			
		if ($this->mailer->send())
			return true;
		else 
			return $mail->ErrorInfo;
		
	}
	
	public function sendMailByUser($usr_codigo,$template) {
		$db=Application::getDatabase();
		$user=$db->getRow("select * from tbl_usuario where usr_codigo='{$usr_codigo}'");
		if (!$user) {
			return false;
		}
		$this->setVariable("nombre_destinatario",$user["usr_nombre"]." ".$user["usr_apellido"]);
		$this->setVariable("email_destinatario",$user["usr_nombre"]." ".$user["usr_apellido"]);
		return $this->sendMailByAddress($user["usr_nombre"]." ".$user["usr_apellido"],$user["usr_email"],$template);
	}
	
	public function sendMailByRules($rules,$template) {
		$db=Application::getDatabase();
		$users=$db->getRecords("tbl_usuario");
		$addresses=array();
		foreach($users["data"] as $user) {
			if (Application::$page->meetRules($rules,UserRules::VIEW,$user["usr_codigo"])) {
				$addresses[$user["usr_email"]]=$user["usr_nombre"]." ".$user["usr_apellido"];
			}
		}
		return $this->sendMailByAddresses($addresses,$template);
	}
	
}