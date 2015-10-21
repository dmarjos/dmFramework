<?php
class arrayUtils {
	
	public static function sanitizeStrings($array=array()) {
		
		foreach($array as $key=>$value) {
			if (is_array($value) || is_object($value)) {
				$array[$key]=self::sanitizeStrings($value);
			} else {
				$entitied=htmlentities($value);
				if ($value!=$entitied)
					$value=array("@cdata"=>htmlentities($value));
				$array[$key]=$value;
			}
		}
		
		return $array;
	}
	
}