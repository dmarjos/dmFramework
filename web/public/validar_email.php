<?php
Application::Uses("com.web.FrontEndPage");
class validar_email extends FrontEndPage {
	
	protected $rules=null;
	
	public function create() {
		parent::create();
		$db=Application::GetDatabase();

		$row=$db->getRow("select * from tbl_usuario where usr_hash='{$_GET["hash"]}' and usr_estado='0'");
		if ($row) {
			$db->update("tbl_usuario",array("usr_hash"=>"","usr_estado"=>1),"usr_codigo='{$row["usr_codigo"]}'");
			$_SESSION[Application::get("FRONTEND_SESSION_VAR")]=$row["usr_codigo"];
			Application::Redirect("/");
		}
	}
    
	public function run() {
    	parent::run();
    }
}
