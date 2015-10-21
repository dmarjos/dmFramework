<?php

function smarty_modifier_ucstr($string){	
	return ucfirst(strtolower($string));
}