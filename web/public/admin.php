<?php
Application::Uses("sys.web.WebAdmin");
Application::Uses("com.admin.PanelAdmin");

class admin extends PanelAdmin {
    
    public $count=0;

    public function create() {

		parent::create();
    	 
    }
    
}
