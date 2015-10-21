<?php
function smarty_function_html_widget_parameter($params, $template) {
	$buttons=0;
	$wgParams=Application::getWidgetParameters($params["widget"]);
	return $wgParams[$params["parameter"]];
}