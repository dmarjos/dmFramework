<?php
/**
 * Named exceptions classes used within the framework
 */
class EDatabaseError extends TException {} // queda
class EQueryError extends TException {} // queda
class EDatasetError extends TException {} // queda

class TException extends Exception {

	public $details = '';

	public function __construct($msg, $errno=0, $details='') {
		$this->details = $details;
		parent::__construct($msg, $errno);
	}

	static public function getErrorMessage($e,$advanced=false) {
		$msg = "\n".'<exception><pre style="border-left:1px solid #ccc;padding-left:10px;margin:5px 0px">';
		if ($advanced || Config::DEBUG_MODE) {
			$msg .= '[<b style="color:#c00">'.get_class($e).($e instanceof EPHPError ? ': '.(array_key_exists($e->errno, $e->types) ? $e->types[$e->errno] : 'Unknown') : '').'</b>]<br />';
		}
		$msg .= $e->getMessage();
		if ($advanced || Config::DEBUG_MODE) {
			$msg .= $e instanceof EPHPError ? '<br />#in '.$e->errfile.' ('.$e->errline.')' : '<br />#in '.$e->getFile().' ('.$e->getLine().')';
			if ($e instanceof TException) { $msg .= '<pre style="padding:5px;margin:0px;display:block">'.$e->details."</pre>"; }
      		$msg .= '<pre style="padding:0px;margin:0px;display:block">'.htmlspecialchars($e->getTraceAsString())."</pre>";
    	}
    	return $msg.'</pre></exception>';
	}
}

/**
 * Class used to translate PHP errors
 * to an exception that can be handle with the
 * global exception handler in dmFramework
 */
class EPHPError extends TException {
	public $types = array(
		E_ERROR           => "Error",
		E_WARNING         => "Warning",
		E_PARSE           => "Parsing Error",
		E_NOTICE          => "Notice",
		E_CORE_ERROR      => "Core Error",
		E_CORE_WARNING    => "Core Warning",
		E_COMPILE_ERROR   => "Compile Error",
		E_COMPILE_WARNING => "Compile Warning",
		E_USER_ERROR      => "User Error",
		E_USER_WARNING    => "User Warning",
		E_USER_NOTICE     => "User Notice",
		E_STRICT          => "Runtime Notice"
	);

	public $errno;
	public $errstr;
	public $errfile;
	public $errline;

	public function __construct($errno, $errstr, $errfile, $errline) {
		$this->errfile = $errfile;
		$this->errline = $errline;
		$this->errno = $errno;
		$this->errstr = $errstr;
		parent::__construct($errstr, $errno);
	}
}
