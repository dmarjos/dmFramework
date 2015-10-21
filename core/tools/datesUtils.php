<?php
class datesUtils {
	public static function formatDateTime($dateTime,$fromFormat="dd/mm/yy",$toFormat="yyyy-mm-dd") {
		$expressionGroups=array(
				"DD"=>"([0-9]{2})",
				"D"=>"([0-9]{1,2})",
				"MM"=>"([0-9]{2})",
				"M"=>"([0-9]{1,2})",
				"YY"=>"([0-9]{2})",
				"YYYY"=>"([0-9]{4})",
				"H"=>"([0-9]{2})",
				"I"=>"([0-9]{2})",
				"S"=>"([0-9]{2})",
		);
		 
		$parsedFormat=preg_match_all("#(([DMYHIS]{1,4})([\-/:\s\.]?))#si",$fromFormat,$matches,PREG_SET_ORDER);
		if (!$matches) return $dateTime;
		 
		$elements=array();
		$toGroups=array();
		$fromMask="";
		foreach($matches as $position=>$match) {
			$selector=strtoupper($match[2]);
			$separador=$match[3];
			switch($separador) {
				case "-":
					$separador="\-";
					break;
				case " ":
					$separador="\s";
					break;
			}
			$fromMask.=$expressionGroups[strtoupper($selector)].$separador;
			$elements[$position+1]=$selector;
		}
	
		$regExp=";".$fromMask.";";
		if (preg_match_all($regExp,$dateTime,$matches,PREG_SET_ORDER)) {
			foreach($matches[0] as $position=>$match) {
				if ($position==0) continue;
				$groupIdentifier=$elements[$position];
				$toGroups[$groupIdentifier]=$match;
			}
			$parsedFormat=preg_match_all("#(([DMYHIS]{1,4})([\-/:\s\.]?))#si",$toFormat,$matches,PREG_SET_ORDER);
			if (!$matches) return $dateTime;
	
			$retVal=array();
			foreach($matches as $position=>$match) {
				$selector=strtoupper($match[2]);
				$separador=$match[3];
				$retVal[]=$toGroups[$selector];
				$retVal[]=$separador;
			}
			return implode("",$retVal);
		}
		 
		return $dateTime;
	}
}