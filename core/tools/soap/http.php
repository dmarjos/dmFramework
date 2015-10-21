<?php
class Http {
	
	private $url='';
	
	private $scheme='http';
	private $host='';
	private $hostIP='';
	private $port=80;
	private $path='';
	private $query='';

	private $response_timeout=30;
	
	private $httpVars=array();
	private $headers=array();
	private $protocolVersion="1.1";
	
	private $keepAlive=false;
	private $content="";

	protected $responseHeaders=array();
	public $responseBody="";

	public $debugString='';

	private $followRedirect=false;
	private $lastMethod='';
	private $lastActuallyExecute='';
	
	private $cookies=array();

	function addDebugMsg($msg) {
		$this->debugString.=$msg."\n";
	}
	
	function saveLog() {
		$this->addDebugMsg("DEBUG FILE ".dirname(__FILE__)."/log/http.log");
		file_put_contents(dirname(__FILE__)."/log/http.log",$this->debugString);
	}
	
	function init($url,$port=80) {
		$this->url=$url;
		$u=parse_url($url);

		foreach($u as $key=>$val) $this->{$key}=$val;
//		$this->hostIP=gethostbyname($this->host);
		if ($u["port"]) 
			$this->port=$u["port"];
		else if ($u["scheme"]=="https")
			$this->port=443;
		else
			$this->port=$port;
	//	if (($this->path!="/") && (substr($this->path,-1)!="/")) $this->path.="/";
	}
	
	function setProtocolVersion($version) {
		$this->$protocolVersion=$version;
		$this->addDebugMsg("Set protocol version to {$version}");
	}
	
	function addHttpHeader($var,$val) {
		$this->headers[$var]=$val;
		$this->addDebugMsg("New HTTP Header {$var}: {$val}");
	}

	function followRedirect() {
		$this->addDebugMsg("Following redirects");
		$this->followRedirect=true;
	}

	function addHttpVar($var,$val) {
		$this->httpVars[$var]="{$var}=".urlencode($val);
		$this->addDebugMsg("New HTTP Var {$var}=".urlencode($val));
	}
	
	function setResponseTimeout($timeout) {
		set_time_limit($timeout);
		$this->response_timeout=$timeout;
		$this->addDebugMsg("Set response timeout to {$timeout}");
	}
	
	function setContent($content="") {
		$this->content=str_replace('$','\$',$content);
		$this->addDebugMsg("==============\nContent set to\n{$content}\n==============");
	}
	
	function getContent() {
		return $this->content;
	}
	
	function debug($method="GET") {
		die("<pre>".$this->execute($method,false));
	}
	
	function execute($method="GET",$actuallyExecute=true) {
		$this->lastMethod=$method;
		$this->lastActuallyExecute=$actuallyExecute;
		$errNo=0;
		$errStr='';
		
		
		$output="{$method} {$this->scheme}://{$this->host}:{$this->port}{$this->path} HTTP/{$this->protocolVersion}\r\n";
//		$output="{$method} {$this->scheme}://{$this->host}:{$this->port}{$this->path}".($this->query?"?".$this->query:"")." HTTP/{$this->protocolVersion}\r\n";
		$this->addHttpHeader("User-Agent","SOAP Client - Ellecktra Imagen Global");
		$this->addHttpHeader("Host","{$this->host}:{$this->port}");
		$this->addHttpHeader("Connection",($this->keepAlive?'Keep-Alive':'Close'));

		if (($method=="POST") && $this->httpVars) {
			$this->addHttpHeader("Content-Type","application/x-www-form-urlencoded");
			$this->addHttpHeader("Content-Length",strlen(implode("&",$this->httpVars)));
		} 
		
		if (!$this->httpVars && $this->content) {
			$this->addHttpHeader("Content-Length",strlen($this->content));
		}

		foreach($this->headers as $header=>$val) {
			$output.="{$header}: {$val}\r\n";
		}
		foreach($this->cookies as $cookie=>$val) {
			$output.="Cookie: {$val}\r\n";
		}


		$output.="\r\n";
		
		if ($method=="POST") {
			if ($this->httpVars) {
				$output.=implode("&",$this->httpVars)."\r\n";
			}
		} 
		
		if ($this->content && !$this->httpVars) {
			$output.=$this->content;
		}
		
		$this->addDebugMsg("==============\nData to send to server\n{$output}\n==============");
		if ($actuallyExecute) {
			set_time_limit(0);
			$start=time();
			$fp=fsockopen(($this->scheme=='https'?'ssl://':'').$this->host,$this->port,$errNo,$errStr,$this->response_timeout);
			if (!$fp) {
				$this->addDebugMsg("==============\nHTTP Client Error: $errStr ($errNo)\n==============");
				throw new Exception("HTTP Client Error: $errStr ($errNo)");
			}
			fputs($fp,$output,strlen($output));
			$response="";
			while (!feof($fp)) {
				$response.=fgets($fp, 1024);
			}
			fclose($fp);
			$end=time();
			$elapsed=$end-$start;
	//		echo "Elapsed: $elapsed seconds";
			$this->addDebugMsg("==============\nGot response: $response\n==============");
			$this->processResponse($response);
			$this->saveLog();
			return $this->responseBody;
		} else {
			$this->saveLog();
			return $output;
		}
	}

	function rawExecute($content) {
		$fp=fsockopen(($this->scheme=='https'?'ssl://':'').$this->host,$this->port,$errNo,$errStr,$this->response_timeout);
		if (!$fp) {
			throw new Exception("HTTP Client Error: $errStr ($errNo)");
		}
		fputs($fp,$content,strlen($content));
		$response="";
		while (!feof($fp)) {
			$response.=fgets($fp, 1024);
		}
		fclose($fp);
		$this->processResponse($response);
		return $this->responseBody;
	}
	function clear() {
		$this->responseHeaders=array();
		$this->responseBody="";
		$this->httpVars=array();
	}

	function processHeaders() {
		$isMoved=false;
		foreach($this->responseHeaders as $header) {
			if (preg_match("!HTTP/".$this->protocolVersion." ([0-9]*) (.*)$!Us",$header,$matches)) {
				if ($matches[1]=="302" || $matches[1]=="301") $isMoved=true;
			} else if (preg_match("!([a-z\-]*):(.*)!si",$header,$matches)) {
				if (strtolower($matches[1])=="location") {
					$this->debugString.="Location HTTP Header found\n";
					if ($this->followRedirect && $isMoved) {
						$this->clear();
						$url=trim($matches[2]);
						$u=parse_url($url);
						$newLocation="";
						$newLocation.=(!$u["scheme"]?$this->scheme:$u["scheme"])."://";
						$newLocation.=(!$u["host"]?$this->host:$u["host"]);
						$newLocation.=(substr($u["path"],0,1)!="/"?$this->path:"").$u["path"];
						$this->init($newLocation);
						$this->execute($this->lastMethod,$this->lastActuallyExecute);
					}
				} else if (strtolower($matches[1])=="set-cookie") {
					$cookie=trim($matches[2]);
					$cookieData=explode(";",$cookie);
					list($cookieName,$cookieVal)=explode("=",$cookieData[0]);
					$this->cookies[$cookieName]=$cookie;
				}
			}
			
		}
	}

	function processResponse($response) {
		$eol="\r\n";
		$doubleEolPos=strpos($response,$eol.$eol);
		if ($doubleEolPos!==false) {
			$headers=substr($response,0,$doubleEolPos);
			$body=substr($response,$doubleEolPos+4);
			$this->responseHeaders=explode($eol,$headers);
			$this->responseBody=$body;
		} else {
			$this->responseHeaders=array();
			$this->responseBody=$response;
		}
		$this->processHeaders();
	}

	function setCookie($cookie,$val) {
		$this->cookies[$cookie]=$val;
	}
	
	function getCookie($cookie) {
		return $this->cookies[$cookie];
	}
	
	function getCookieVal($cookie) {
		$cookie=$this->cookies[$cookie];
		$cookieData=explode(";",$cookie);
		list($cookieName,$cookieVal)=explode("=",$cookieData[0]);
		return $cookieVal;		
	}
	
	function getResponseHeaders() {
		return $this->responseHeaders;
	}

	function getResponseBody() {
		return $this->responseBody;
	}
}