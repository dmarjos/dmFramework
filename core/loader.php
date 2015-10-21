<?php
function F_ErrorHandler($errno, $errstr, $errfile, $errline) {
	if (!(error_reporting() & $errno)) {
		// This error code is not included in error_reporting
		return;
	}

	Application::error_log("error: {$errno}, {$errstr} in file {$errfile} line {$errline}");

	/* Don't execute PHP internal error handler */
	return true;
}


function __autoload($className) {
	
	if (file_exists(dirname(__FILE__)."/".$className.".php"))
		require_once(dirname(__FILE__)."/".$className.".php");
}

function dump_var($var,$die=true) {
	$debug=debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
	echo "<pre>";
	if (is_object($var)) {
		$methods=get_class_methods($var);
		if ($methods) {
			var_dump($methods);
			echo "</pre><hr/><pre>"; 
		}
	}
	var_dump($var);
	if ($die) die();
}

Application::loadConfig();

if (Application::get("USE_INTERNAL_ERROR_HANDLER")=="1") {
	ini_set("memory_limit","128M");
	$old_error_handler = set_error_handler("F_ErrorHandler");
}
Application::Uses("sys.dmFramework");
