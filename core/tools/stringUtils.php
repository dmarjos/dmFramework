<?php
class stringUtils {

	public function countWords($string) {
		$string=preg_replace('/[^a-z0-9]+/',"-",$string);
		$string=$this->replace_all("--","-",$string);
		if (substr($string,-1)=="-") {
			$string=substr($string,0,-1);
		}
		$words=explode("-",$string);
		return count($words);		
	}

	public static function makeClickableLinks($source) {
		return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $source);
	}
	public static function makePlainString($string) {
		$string=strip_tags($string);
		$string=utf8_decode(strtolower($string));
		$string = strtr($string,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜâêîôûÂÊÎÔÛàèìòùÀÈÌÒÙãõÃÕñÑçÇ','aeiouaeiouaeiouaeiouaeiouaeiouaeiouaeiouaoaonncc');
		return $string;
	}
	
	
	public function makeURL($string) {
		$stopWords=file($_SERVER["DOCUMENT_ROOT"].Application::getPath('resources/misc/stop-words-spanish.txt'));
		foreach($stopWords as &$word) $word=trim($word);
		$string=utf8_decode(strtolower($string));
		$string = strtr($string,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜâêîôûÂÊÎÔÛàèìòùÀÈÌÒÙãõÃÕñÑçÇ','aeiouaeiouaeiouaeiouaeiouaeiouaeiouaeiouaoaonncc');
		$string=preg_replace('/\b('.implode('|',$stopWords).')\b/','',$string);
		$string=trim($string);
		$string=self::replace_all("  "," ",$string);
		$string=preg_replace('/[^a-z0-9]+/',"-",$string);
		$string=self::replace_all("--","-",$string);
			if (substr($string,-1)=="-") {
			$string=substr($string,0,-1);
		}
		
		if (substr($string,0,1)=="-") {
			$string=substr($string,1);
		}
		
		return $string;
	}

	public function replace_all($search,$replacement,$subject) {
		while(strpos($subject,$search)!==false) {
			$subject=str_replace($search,$replacement,$subject);
		}
		return $subject;
	}
	
	public static function makeExcerpt($str) {
		if (strpos($str,"<hr>")!==false)
			list($excerpt,$rest)=explode("<hr>",$str);
		elseif (strpos($str,"<hr/>")!==false)
			list($excerpt,$rest)=explode("<hr/>",$str);
		elseif (strpos($str,"[mas]")!==false)
			list($excerpt,$rest)=explode("[mas]",$str);
		else 
			$excerpt=$str;
		
		$excerpt=strip_tags($excerpt);
		if (strlen($excerpt)>200) {
			$excerpt=substr($excerpt,0,197)."...";
		}
		return utf8_decode($excerpt);
	}
	
	public static function getMonthName($mes,$corto=true) {
		$mes=intval($mes);
		$mes=substr("00$mes",-2);
		$meses=array(
			"01"=>"Enero",
			"02"=>"Febrero",
			"03"=>"Marzo",
			"04"=>"Abril",
			"05"=>"Mayo",
			"06"=>"Junio",
			"07"=>"Julio",
			"08"=>"Agosto",
			"09"=>"Setiembre",
			"10"=>"Octubre",
			"11"=>"Noviembre",
			"12"=>"Diciembre",
		);
		
		$nombre=$meses[$mes];
		if ($corto) $nombre=substr($nombre,0,3);
		return $nombre;
	}

	public static function stringOccurrence($needle,$haystack) {
		$occurrences=0;
		$pos=strpos($haystack,$needle);
		while($pos!==false) {
			$occurrences++;
			$haystack=substr($haystack,$pos+1);
			$pos=strpos($haystack,$needle);
		}
		
		return $occurrences;
	} 

	public static function removeTags($content) {
		
		$validBlocks=array();
		preg_match_all("~<(pre|code)([^>]*)>(.*)</\\1>~Usi",$content,$matches,PREG_SET_ORDER);
		foreach($matches as $idx=>$match) {
			$tagIdentifier="[[".$match[1]."_".($idx+1)."]]";
			$content=str_replace($match[0],$tagIdentifier,$content);
			$validBlocks[$idx]=$match;
//			dump_var($match);
		}
		preg_match_all("~<([a-z\-]*)([^>]*)>(.*)</\\1>~msi",$content,$matches,PREG_SET_ORDER);
		foreach($matches as $match) {
			$content=str_replace($match[0],"",$content);
		}
		
		preg_match_all("~<([a-z\-]*)>(.*)</\\1>~msi",$content,$matches,PREG_SET_ORDER);
		foreach($matches as $match) {
			$content=str_replace($match[0],"",$content);
		}
		preg_match_all("~<([a-z\-]*)([^>]*)>~msi",$content,$matches,PREG_SET_ORDER);
		foreach($matches as $match) {
			$content=str_replace($match[0],"",$content);
		}
		
		$content=trim($content);
		
		foreach($validBlocks as $idx => $match) {
			$validBlock=$match[0];
			preg_match_all("~<(pre|code)([^>]*)>(.*)</\\1>~si",$validBlock,$matches,PREG_SET_ORDER);
			$validBlock=str_replace($matches[0][3],htmlentities($matches[0][3]),$validBlock);
			$validBlock=str_replace("<{$matches[0][1]}{$matches[0][2]}>","<pre class=\"prettycode\">",$validBlock);
			$validBlock=str_replace("</{$matches[0][1]}>","</pre>",$validBlock);
			$tagIdentifier="[[".$match[1]."_".($idx+1)."]]";
			$content=str_replace($tagIdentifier,$validBlock,$content);
		}

		return $content;
	}
}