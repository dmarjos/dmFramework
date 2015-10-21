<?php
function smarty_function_html_widget($params, $template) {
	$wgType=$params["type"];
	$className="T".ucwords(strtolower($wgType));
	Application::Uses("sys.web.widgets.{$className}");
	$widget=new $className();
	return $widget->run($params["name"]);
}