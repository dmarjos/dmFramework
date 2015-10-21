<?php
/* Arregla HTML sin finalizar usando la libreria DOM de PHP. (Puto Truncate) */

function smarty_modifier_dom($html){
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml version="1.0" encoding="UTF-8"?>' . $html);
    $yourText = $doc->saveHTML();    
	return $yourText;
}