<?php
Application::Uses("sys.constants.UserRules");
Application::Uses("sys.web.WebAdminAjax");
Application::Uses("sys.tools.DataTables");
Application::Uses('sys.tools.stringUtils');

class emails extends WebAdminAjax {
	
  	public $rules    = 'usuarios';
  	
  	public function create() {
  		parent::create();
  	}
  	

  	public function getTemplates() {
  		$_POST["type"]="json";
  		
  		$db=Application::getDatabase();
  		$params=$_POST;
  		$params["table"]="tbl_email_template";
  		$params["id_field"]="tmp_codigo";
  		$dt=new DataTables($params);
  		$dt->setColumns(array( 'tmp_nombre','tmp_titulo')/*,array('tmp_titulo'=>array("encoder"=>"utf8_encode"))*/);
  		
		$this->data=$dt->getData();  		
	  	//dump_var($this->data);
  	}
  	
}
