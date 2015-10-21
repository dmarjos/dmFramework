<?php
Application::Uses("sys.tools.agent");

class curl {
	
	private $ch=false;
	
	function __construct($url) {
		$this->ch=curl_init($url);
	}
	
	public function setOption($option,$value) {
		curl_setopt($this->ch, $option, $value);
	}
	
	public function setOptions($options) {
		if (!is_array($options)) return false;
		curl_setopt_array($this->ch, $options);
	}
	
	public function execute($return=true) {
		if ($return) 
			$this->setOption(CURLOPT_RETURNTRANSFER, true);

		$uagent=new agent();
		curl_setopt($this->ch, CURLOPT_USERAGENT, $uagent->random_uagent());
		
		$retVal=curl_exec($this->ch);
		
		return $retVal;
	}
}