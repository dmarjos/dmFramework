<?php
function smarty_function_html_form($params, $template) {
	$wgParams=Application::getWidgetParameters($params["id"]);

	$title=$wgParams["title"];
	$subtitle=$wgParams["subtitle"];
	$fields=Application::$page->fields; //$wgParams["fields"];
	if (!$wgParams["SAVE_TEXT"]) $wgParams["SAVE_TEXT"]="Grabar";
	
	$scripts=Application::get("scripts");
	if (is_null($scripts)) $scripts=array();
	$output="";
	if (!in_array("/resources/js/admin/core.js",$scripts)) {
		Application::addScript('/resources/js/lib/core.js');
		$output.='<script type="text/javascript" src="'.Application::GetLink("/resources/js/lib/core.js").'"></script>';
	}
	if (!in_array("/resources/js/admin/jquery.forms.js",$scripts)) {
		Application::addScript('/resources/js/lib/jquery.forms.js');
		$output.='<script type="text/javascript" src="'.Application::GetLink("/resources/js/lib/jquery.forms.js").'"></script>';
	}
	
	$templatesBaseDir=Application::getTemplatesDir();
	
	$widgetFile=$templatesBaseDir."/widgets/form/main.tpl";
	if (file_exists($widgetFile)) {
		$html=file_get_contents($widgetFile);
	} else {
		$html='';
	}
	
	$st=new stringUtils();
	
	foreach($wgParams as $key=>$value) {
		if (is_string($value)) {
			$html=$st->replace_all("[[".$key."]]",$value,$html);
		} 
	}
	if (!$wgParams["globalValidator"])
		$html=$st->replace_all("[[globalValidator]]","null",$html);
	if (!$wgParams["submitSuccessCallBack"])
		$html=$st->replace_all("[[submitSuccessCallBack]]","null",$html);
	if (!$wgParams["validators"])
		$html=$st->replace_all("[[validators]]","null",$html);
	
	$html_fields=array();
	$hasImage=false;
	$hasDate=false;
	foreach($fields as $name=>$data) {
		extract($data);
		if ($data["type"]=="custom") {
			if (method_exists(Application::$page, $method)) {
				$html_fields[]=call_user_func(array(Application::$page, $method),$name,$data);
			}
		} else {
			$funcName="form_field_{$data["type"]}";
			if ($data["type"]=="gallery") {
				$scripts=Application::get("scripts");
				if (is_null($scripts)) $scripts=array();
				if (!in_array("/resources/js/lib/jquery.galleryManager.js",$scripts)) {
					Application::addScript('/resources/js/lib/jquery.galleryManager.js');
					$output.='<script type="text/javascript" src="'.Application::GetLink("/resources/js/lib/jquery.galleryManager.js").'"></script>';
					$hasImage=true;
				}
			}
			if ($data["type"]=="slider") {
				$scripts=Application::get("scripts");
				if (is_null($scripts)) $scripts=array();
				if (!in_array("/resources/js/lib/jquery.slider.js",$scripts)) {
					Application::addScript('/resources/js/lib/jquery.slider.js');
                	$output.='<script type="text/javascript" src="'.Application::GetLink("/resources/js/lib/jquery.slider.js").'"></script>';
					$hasImage=true;
				}
			}
			if (function_exists($funcName)) {
				$html_fields[]=$funcName($name,$data);
			} else
				$html_fields[]="<!-- $funcName no existe! -->";
		}
	}
	
	$html=$st->replace_all("[[FIELDS]]",implode("\n",$html_fields),$html);
	$html=$st->replace_all("[[BACKTO]]",Application::$page->urlBackTo,$html);
	$output.=$html;
	return $output;
}

function preprocess_template($type,$name,$field) {
	$templatesBaseDir=Application::getTemplatesDir();
	
	$widgetFile=$templatesBaseDir."/widgets/form/{$type}.tpl";
	if (file_exists($widgetFile)) {
		$html=file_get_contents($widgetFile);
	} else {
		$html='';
	}
	$st=new stringUtils();
	
	foreach($field as $key=>$value) {
		if ($key=="value") $value=utf8_encode($value);
		
		if (is_string($value)) {
			if (!in_array($key,array("width","readonly","disabled","rows","cols")))
				$html=$st->replace_all("[[".$key."]]",$value,$html);
		} 
	}
	$html=$st->replace_all("[[name]]",$name,$html);
	return $html;
}

function form_field_text($name,$field) {
	extract($field);
	$st=new stringUtils();
	
	$html=preprocess_template("text",$name,$field);
	
	if (Application::$page->mode=="DELETE" || $readonly)
		$html=$st->replace_all("[[readonly]]",'readonly="readonly" ',$html);
	else 
		$html=$st->replace_all("[[readonly]]",'',$html);

	if ($width)
		$html=$st->replace_all("[[width]]",'style="width:'.$width.'" ',$html);
	else 
		$html=$st->replace_all("[[width]]",'',$html);

	return $html;
}

function form_field_number($name,$field) {
	extract($field);
	$st=new stringUtils();
	
	$html=preprocess_template("number",$name,$field);
	
	if (Application::$page->mode=="DELETE" || $readonly)
		$html=$st->replace_all("[[readonly]]",'readonly="readonly" ',$html);
	else 
		$html=$st->replace_all("[[readonly]]",'',$html);

	if ($width)
		$html=$st->replace_all("[[width]]",'style="width:'.$width.'" ',$html);
	else 
		$html=$st->replace_all("[[width]]",'',$html);

	return $html;
}

function form_field_custom($name,$field) {
	extract($field);
	$st=new stringUtils();
	
	$html=preprocess_template("custom",$name,$field);
	$html=$st->replace_all("[[content]]",$content,$html);
	return $html;
	
}
function form_field_hidden($name,$field) {
	extract($field);
	$st=new stringUtils();
	$html=preprocess_template("hidden",$name,$field);
	
	if (Application::$page->mode=="DELETE" || $readonly)
		$html=$st->replace_all("[[readonly]]",'readonly="readonly" ',$html);
	else 
		$html=$st->replace_all("[[readonly]]",'',$html);

	if ($width)
		$html=$st->replace_all("[[width]]",'style="width:'.$width.'" ',$html);
	else 
		$html=$st->replace_all("[[width]]",'',$html);

	return $html;
}

function form_field_gallery($name,$field) {
	extract($field);
	$st=new stringUtils();
	
	$html=preprocess_template("gallery",$name,$field);
	
	if (Application::$page->mode=="DELETE" || $readonly)
		$html=$st->replace_all("[[readonly]]",'readonly="readonly" ',$html);
	else 
		$html=$st->replace_all("[[readonly]]",'',$html);

	if ($width)
		$html=$st->replace_all("[[width]]",'style="width:'.$width.'" ',$html);
	else 
		$html=$st->replace_all("[[width]]",'',$html);

	return $html;
}

function form_field_slider($name,$field) {
	extract($field);
	$st=new stringUtils();
	
	$html=preprocess_template("slider",$name,$field);
	
	if (Application::$page->mode=="DELETE" || $readonly)
		$html=$st->replace_all("[[readonly]]",'readonly="readonly" ',$html);
	else 
		$html=$st->replace_all("[[readonly]]",'',$html);

	if ($width)
		$html=$st->replace_all("[[width]]",'style="width:'.$width.'" ',$html);
	else 
		$html=$st->replace_all("[[width]]",'',$html);

	return $html;
}

function form_field_textarea($name,$field) {
	extract($field);
	$st=new stringUtils();
	
	$html=preprocess_template("textarea",$name,$field);
		
	if (Application::$page->mode=="DELETE" || $readonly)
		$html=$st->replace_all("[[readonly]]",'readonly="readonly" ',$html);
	else
		$html=$st->replace_all("[[readonly]]",'',$html);
	
	if ($width || $height) {
		$styles=array();
		if ($height) $styles[]="height: {$height};";
		if ($width) $styles[]="width: {$width};";
		$html=$st->replace_all("[[styles]]",'style="'.implode("",$styles).'" ',$html);
	} else 
		$html=$st->replace_all("[[styles]]",'',$html);

	if ($rows)
		$html=$st->replace_all("[[rows]]",'rows="'.$rows.'" ',$html);
	else 
		$html=$st->replace_all("[[rows]]",'',$html);
	if ($cols)
		$html=$st->replace_all("[[cols]]",'cols="'.$cols.'" ',$html);
	else 
		$html=$st->replace_all("[[cols]]",'',$html);
	return $html;
}

function form_field_select($name,$field) {
	extract($field);
	$st=new stringUtils();
	
	$html=preprocess_template("select",$name,$field);
	if (Application::$page->mode=="DELETE" || $readonly)
		$html=$st->replace_all("[[disabled]]",'disabled="disabled" ',$html);
	else
		$html=$st->replace_all("[[disabled]]",'',$html);
	if ($select_size)
		$html=$st->replace_all("[[size]]",'size="'.$select_size.'" multiple="multiple" ',$html);
	else
		$html=$st->replace_all("[[size]]",'',$html);
	
	if (method_exists(Application::$page, $optionsFunction)) {
		$html=$st->replace_all("[[OPTIONS]]",Application::$page->$optionsFunction($value),$html);
	} else {
		$html=$st->replace_all("[[OPTIONS]]",'',$html);
	}
	return $html;
}