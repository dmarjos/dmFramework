<?php
Application::Uses("sys.tools.soap.http");
Application::Uses("sys.tools.xml.xmlparser");

class Soap extends Http {
	
	private $internalVariables=array(
		"SOAPAction"=>"",
		"SOAPOperation"=>"olaservice",
		"SOAPOperationNS"=>"",
		"SOAPEnv"=>"SOAP-ENV",
		"SOAPMethod"=>"POST",
		"nsPrefix"=>"ns1",
		"charset"=>"utf-8",
		"namespaces"=>array(),
		"soapHeaders"=>array(),
		"response"=>array(),
		"useCache"=>false,
		"cacheTimeOut"=>600,
		"cacheDir"=>'',
		"user"=>'',
		"pass"=>'',
		"debugStr"=>'',
		"debugMode"=>false,
		"contentType"=>array(),
		"contentIsCData"=>false
	);

	function addDebugMessage($msg) {
		$this->debugStr.=$msg."\n";
	}
	
	function saveDebug() {
		if (!$this->debugMode) return;
		file_put_contents(dirname(__FILE__)."/log/soap-".$this->SOAPAction.".log",$this->debugStr);
	}
	function init($url,$port=80) {
		parent::init($url,$port);
	}

	function addSoapHeader($header) {
		$this->addDebugMessage("ADD SOAP HEADER: ".$header);
		$headers=$this->soapHeaders;
		$headers[]=$header;
		$this->soapHeaders=$headers;
	}

	function addContentType($type) {
		$this->addDebugMessage("ADD CONTENT-TYPE: ".$type);
		$contentType=$this->contentType;
		$contentType[]=$type;
		$this->contentType=$contentType;
	}
	
	function setSoapOperation($operation,$namespace="ns1") {
		$this->addDebugMessage("SET SOAP OPERATION: ".$operation);
		$this->addDebugMessage("SET SOAP NAMESPACE: ".$namespace);
		$this->SOAPOperation=$operation;
		$this->SOAPOperationNS=$namespace;
	}

	function execute($method='',$debugMode=false) {
		
		$this->addDebugMessage("EXECUTE METHOD: ".$method);
		//$this->addHttpHeader("SOAPAction",$this->SOAPAction);
		if (!$this->contentType) {
			$this->addDebugMessage("ADD HTTP HEADER: "."application/soap+xml;charset={$this->charset};action=\"{$this->SOAPOperation}\"");
			$this->addHttpHeader("Content-Type","application/soap+xml;charset={$this->charset};action=\"{$this->SOAPOperation}\"");
		} else {
//			$this->addHttpHeader("Content-Type","text/xml; charset=\"{$this->charset}\"");
			$this->addDebugMessage("ADD HTTP HEADER: "."application/soap+/xml;charset=\"{$this->charset}\";action=\"{$this->SOAPOperation}\"");
			$this->addHttpHeader("Content-Type","application/soap+/xml; charset=\"{$this->charset}\"; action=\"{$this->SOAPOperation}\"");
			//$this->addHttpHeader("Content-Type",implode("; ",$this->contentType));
		}

		$content=array();
		if ($this->user && $this->pass) {
			$nonce=md5(uniqid(rand()));
			$wsse=array();
			$wsse[]='<wsse:Security '.$this->SOAPEnv.':mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">';
			$wsse[]='<wsse:UsernameToken wsu:Id="UsernameToken-1" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">';
			$wsse[]='<wsse:Username>'.$this->user.'</wsse:Username>';
			$wsse[]='<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$this->pass.'</wsse:Password>';
			$wsse[]='<wsse:Nonce>'.$nonce.'</wsse:Nonce>';
			$wsse[]='<wsu:Created>'.date("c",time()).'</wsu:Created>';
			$wsse[]='</wsse:UsernameToken>';
			$wsse[]='</wsse:Security>';
			$this->addSoapHeader(implode("",$wsse));
		}
		$content[]='<'.$this->SOAPEnv.':Envelope xmlns:'.$this->SOAPEnv.'="http://schemas.xmlsoap.org/soap/envelope/" xmlns:'.$this->nsPrefix.'="'.$this->SOAPOperationNS.'">';
		if ($this->soapHeaders) {
			$content[]="<".$this->SOAPEnv.":Header>";
			$content[]=implode("",$this->soapHeaders);
			$content[]="</".$this->SOAPEnv.":Header>";
		} else {
			$content[]="<".$this->SOAPEnv.":Header/>";
		}
		if ($this->getContent()) {
			$content[]="<".$this->SOAPEnv.":Body>";
			if ($this->SOAPOperation) 
				$content[]="<{$this->nsPrefix}:{$this->SOAPOperation}>".($method?"<{$method}>":'').($this->contentIsCData?'<![CDATA[':'');
			else
				throw new Exception("SOAP Operation not set!");
			$content[]=$this->getContent();
			if ($this->SOAPOperation) 
				$content[]=($this->contentIsCData?']]>':'').($method?"</{$method}>":'')."</{$this->nsPrefix}:{$this->SOAPOperation}>";
			$content[]="</".$this->SOAPEnv.":Body>";
		}
		$content[]='</'.$this->SOAPEnv.':Envelope>';
		$this->addDebugMessage("SOAPMethod: ".$this->SOAPMethod);
		$this->addDebugMessage("==================\nCONTENT BUILT:\n".$this->formatXmlString(implode("\n",$content))."\n====================");
		
		$cacheFile=md5($this->getContent()).".xml.cache";
		parent::setContent(implode("",$content));
		
		if ($this->useCache) {
			$this->addDebugMessage("USING CACHE: ".$cacheFile);
			if ($this->cacheFileIsValid($cacheFile)) {
				$this->addDebugMessage("CACHE STILL VALID");
				$response=file_get_contents($this->cacheDir."/".$cacheFile);
				$this->processResponse($response);
				$this->addDebugMessage("==================\nSOAP RESPONSE:\n".$this->formatXmlString($response)."\n====================");
				$this->saveDebug();
				return $this->responseBody;
			} else {
				$this->addDebugMessage("CACHE INVALID. Saving cache content");
				@unlink($this->cacheDir."/".$cacheFile);
				$result=parent::execute($this->SOAPMethod,!$debugMode);
				file_put_contents($this->cacheDir."/".$cacheFile,implode("\r\n",$this->responseHeaders)."\r\n\r\n".$result);
				$this->addDebugMessage("==================\nSOAP RESPONSE:\n".$this->formatXmlString($result)."\n====================");
				$this->saveDebug();
				return $result;
			}
		}
		$result=parent::execute($this->SOAPMethod,!$debugMode);
		$this->addDebugMessage("==================\nSOAP RESPONSE:\n".$this->formatXmlString($result)."\n====================");
		$this->saveDebug();
		return $result;
	}

	function cacheFileIsValid($cacheFile) {
		$ttl=time()-$this->cacheTimeOut;
		if (!file_exists($this->cacheDir)) mkdir($this->cacheDir);
		if (file_exists($this->cacheDir."/".$cacheFile)) {
			if (fileatime($this->cacheDir."/".$cacheFile)>$ttl) return true;
		}
		return false;
	}

	function __set($var,$val) {
		if (!isset($this->internalVariables[$var])) throw new Exception("Attempt to set an undefined property {$var}");
		$this->internalVariables[$var]=$val;
	}

	function __get($var) {
		if (!isset($this->internalVariables[$var])) file_put_contents(dirname(__FILE__)."/object_debug.txt",print_r($this,true)); ;// throw new Exception("Attempt to read an undefined property {$var}");
		return $this->internalVariables[$var];
	}

	function processResponse($response) {
		parent::processResponse($response);

		$xmlParser=new xmlparser($this->responseBody);
		$preResponse=$xmlParser->getValue("soap:Envelope","soap:Body.ns2:{$this->SOAPOperation}Response");
		
		$xmlParser=new xmlparser($preResponse["{$this->SOAPOperation}RS"],true);
		$this->response=$xmlParser->parsed["{$this->SOAPOperation}RS"];
	}

	public function setResponse($response) {
		$this->response=$response;
	}


	public function getResponse() {
		return $this->response;
	}

	function formatXmlString($xml) {  
	  
	  // add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
	  $xml=str_replace("<![CDATA[","",$xml);
	  $xml=str_replace("]]>","",$xml);
	  $xml=str_replace("&lt;","<",$xml);
	  $xml=str_replace("&gt;",">",$xml);
	  $xml=str_replace("&quot;",'"',$xml);
	  
	  $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);
	  
	  // now indent the tags
	  $token      = strtok($xml, "\n");
	  $result     = ''; // holds formatted version as it is built
	  $pad        = 0; // initial indent
	  $matches    = array(); // returns from preg_matches()
	  
	  // scan each line and adjust indent based on opening/closing tags
	  while ($token !== false) : 
	  
	    // test for the various tag states
	    
	    // 1. open and closing tags on same line - no change
	    if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) : 
	      $indent=0;
	    // 2. closing tag - outdent now
	    elseif (preg_match('/^<\/\w/', $token, $matches)) :
	      $pad--;
	    // 3. opening tag - don't pad this one, only subsequent tags
	    elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) :
	      $indent=1;
	    // 4. no indentation needed
	    else :
	      $indent = 0; 
	    endif;
	    
	    // pad the line with the required number of leading spaces
	    $line    = str_pad($token, strlen($token)+($pad*4), ' ', STR_PAD_LEFT);
	    $result .= $line . "\n"; // add to the cumulative result, with linefeed
	    $token   = strtok("\n"); // get the next token
	    $pad    += $indent; // update the pad size for subsequent lines    
	  endwhile; 
	  
	  return $result;
	}
}