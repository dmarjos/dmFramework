<?php
class xmlBuilder {
	private $xmlData=array();
	private $debug=false;

	function enableDebug() {
		$this->debug=true;
	}
		
	function addNode($nodePath,$value) {
		$pathParts=explode(".",$nodePath);
		$arrayIndices=array();
		foreach($pathParts as $node) {
			list($nodeName,$index)=explode("|",$node);
			if (is_numeric($index)) {
				$arrayIndices[]="[\"$nodeName\"][$index]";
			} else {
				$arrayIndices[]="[\"$nodeName\"]";
			}
		}
		if ($this->debug) echo "\$this->xmlData".implode("",$arrayIndices)."=$value;<br/>";
		if (is_array($value)) {
			eval("\$this->xmlData".implode("",$arrayIndices)."=$value;");
		} else {
			$value=addslashes($value);
			eval("\$this->xmlData".implode("",$arrayIndices)."=\"$value\";");
		}
	}

	function nodeHasAttributes($root) {
		$retVal=false;
		foreach($root as $key=>$value) {
			if (!is_numeric($key) && !is_array($value)) {
				$value=stripslashes($value);
				if (substr($key,0,1)=="@")
					return true;
			}
		}
		return false;
	}
	
	function getAttributes($root) {
		$retVal=false;
		$xmlAttrs="";
		foreach($root as $key=>$value) {
			if (!is_numeric($key) && !is_array($value)) {
				$value=stripslashes($value);
				if (substr($key,0,1)=="@") {
					$key=substr($key,1);
					$xmlAttrs.="$key=\"$value\" ";
				}
			}
		}
		return trim($xmlAttrs);
	}
	
	function buildXML($root="") {
		if (!$root) 
			$root=$this->xmlData;
			
		$xmlInfo="";
		foreach($root as $key=>$value) {
			if (is_numeric($key)) {
				foreach($value as $idx => $node) {
					if (is_array($node)) {
						$xmlInfo.=$this->buildXML($node)."\n";
					}
					else if (!is_numeric($idx)) {
						$xmlInfo.="<$idx>$node</".$idx.">\n";
					}
				}
			} else if (is_array($value)) {
				if (isset($value[0])) {
					foreach($value as $itemID => $item) {
						if ($this->nodeHasAttributes($item)) {
							$xmlInfo.="<$key ".$this->getAttributes($item); //.">"."\n";
						} else $xmlInfo.="<$key"; //>\n";
						
						$content=$this->buildXML($item);
						if (trim($content))
							$xmlInfo.=">\n".$content."</".$key.">"."\n";
						else
							$xmlInfo.="/>\n";
					}
				} else {
					if ($this->nodeHasAttributes($value)) {
						$xmlInfo.="<$key ".$this->getAttributes($value); //.">\n";
					} else $xmlInfo.="<$key"; //">\n";
					
					$content=$this->buildXML($value);
					if (trim($content))
						$xmlInfo.=">\n".$this->buildXML($value)."</".$key.">"."\n";
					else
						$xmlInfo.="/>\n";
				}
			} else {
				$value=stripslashes($value);
				if (substr($key,0,1)!="@")
					$xmlInfo.="<$key>$value</".$key.">\n";
			}
		}
		
		$xmlInfo=preg_replace("/\n<PSVAL>(.*?)<\/PSVAL>\n/i","\\1",$xmlInfo);
		return $xmlInfo;
	}
	
	function Generate($toOutput=true) {
		if ($toOutput) {
			if (!$this->debug)
				header("Content-Type: text/xml");
			die($this->buildXML());		
		} else {
			return $this->buildXML();
		}
	}

	function debugXML() {
		if (!$this->debug) return;
		fputs(fopen($_SERVER["DOCUMENT_ROOT"]."/debug.xml","w+"),$this->buildXML()."\n");
	}

	function getAsArray() { return $this->xmlData; }
}