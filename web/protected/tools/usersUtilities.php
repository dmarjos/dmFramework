<?php
class usersUtilities {

	public static function get_gravatar( $email, $s = 50, $d = 'mm', $r = 'g', $img = false, $atts = array() ) {
		$url = 'http://www.gravatar.com/avatar/';
		$url .= md5( strtolower( trim( $email ) ) );
		$url .= "?s=$s&d=$d&r=$r";
		if ( $img ) {
			$url = '<img src="' . $url . '"';
			foreach ( $atts as $key => $val )
				$url .= ' ' . $key . '="' . $val . '"';
			$url .= ' />';
		}
		return $url;
	}
	
	public static function getGravatar($usr_codigo,$size=50) {
		$db=Application::getDatabase();
		$rec=$db->getRecord("tbl_usuario","usr_codigo='{$usr_codigo}'");
		
		$email=$rec["usr_email"];

		$internalProfileImage=$_SERVER["DOCUMENT_ROOT"].Application::getPath("/resources/img/perfiles/".md5($usr_codigo).".jpg");
		if (file_exists($internalProfileImage)) {
			return Application::getPath("/resources/img/perfiles/".md5($usr_codigo).".jpg");
		}
		return self::get_gravatar($email,$size,"http://".$_SERVER["SERVER_NAME"].Application::getLink("/resources/img/perfiles/default.jpg"));
	} 
}