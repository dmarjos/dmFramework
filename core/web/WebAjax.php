<?php
Application::Uses("sys.web.WebPage");

class WebAjax extends WebPage {

	protected $rules=null;

	protected $data;

	public function create() {
		parent::create();
	}

	public function run() {
    	$this->authenticate();
    	if ($this->init())
        	$this->output();
	}
	
	public function init() {
		$retVal=parent::init();
		if (!$_POST && $_GET) $_POST=$_GET;
		if (!$_POST["type"]) $_POST["type"]="json";
  		if ($_POST["method"]) call_user_method($_POST["method"], $this);
		return $retVal;
	}
	
    public function output() {
    	if ($_POST["type"]=="json" && (is_array($this->data) || is_object($this->data))) {
    		header("Content-Type: text/json");
    		die(json_encode($this->data));
    	} else if ($_POST["type"]=="json_text" && (is_array($this->data) || is_object($this->data))) {
    		die(json_encode($this->data));
    	} else {
    		die($this->data);
    	}
    }

}