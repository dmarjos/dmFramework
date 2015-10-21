<?php
Application::Uses('sys.web.WebAdminAjax');
Application::Uses("com.tools.usersUtilities");

class gallery extends WebPage {

	public $rules = '';
	public $cache = false;

	public $root = '';
	public $base = '';


	public function meetRules($rules, $rule) {
		return true;
	}

	public function init() {
		parent::init();
    	$_POST['sid'] != '' && session_id($_POST['sid']);
    	session_start();
    	if ($_SESSION["tmpid"]=="")
    		$_SESSION["tmpid"]=substr(md5(uniqid()),-32);
    		
    		
    	$_SESSION["tmpid"]="";
    	return true;
	}
  	
  	public function run() {
    	if (!$_POST && $_GET["action"]) $_POST=$_GET;
    	switch($_POST["action"]) {
    		case "upload":
    			$this->processFiles();
    			break;
    		case "update":
    			$this->updateFile();
    			break;
    		case "delete":
    			$this->deleteFile();
    			break;
    		case "getFiles":
    		default:
    			$this->getFiles();
    			break;
    	}
    	$this->json(array('status'=>'fail','reason'=>'invalid action'));
  	}

  	private function deleteFile() {
  		$db=Application::GetDatabase();
  		$sql="select * from tbl_galeria where gal_grupo='{$_POST["group"]}' and gal_codigo='{$_POST["elementId"]}'";
  		$row=$db->getRow($sql);
  		if (!$row) $this->getFiles();

  		$physPath=$_SERVER["DOCUMENT_ROOT"].Application::getPath("/resources/users-uploads/galleries/{$_POST["group"]}");
  		$file=$physPath."/".$row["gal_archivo"];
  		@unlink($file);
  		$sql="DELETE from tbl_galeria where gal_grupo='{$_POST["group"]}' and gal_codigo='{$_POST["elementId"]}'";
  		$db->execute($sql);
  		$this->getFiles();
  	}
  	
  	private function getFiles($log=false) {
  		
  		$db=Application::GetDatabase();
  		
  		$sql="select * from tbl_galeria where gal_grupo='{$_POST["group"]}' and (gal_relacionado='{$_GET["id"]}' or gal_sesion='".$db->escape(session_id())."')";
  		$files=array();
//  		if ($log)	error_log($sql);
  		
  		$res=$db->execute($sql);
  		while($row=$db->getNextRecord($res)) {
  			switch($row["gal_tipo"]) {
  				case "picture":
			  		$fileName=Application::getPath("/resources/users-uploads/galleries/{$row["gal_grupo"]}/{$row["gal_archivo"]}");
  					break;
  				case "youtube":
		  			$fileName=Application::GetPath("resources/img/gallery/youtube.png");
  					break;
  			}
  			$files[]=array(
  				'id'=>$row["gal_codigo"],
  				'type'=>$row["gal_tipo"],
  				'title'=>$row["gal_titulo"],
  				'description'=>$row["gal_descripcion"],
  				'name'=>$fileName
  			);
  		}
    	$this->json(array('status'=>'ok','files'=>$files));
  	}

  	private function json($var) {
  		header('Content-Type: text/json');
  		die(json_encode($var));
  	}

  	private function updateFile() {
		$db=Application::GetDatabase();
		$_POST["title"]=$db->escape($_POST["title"]);
		$_POST["description"]=$db->escape($_POST["description"]);
		$campos=array(
        	"gal_titulo='{$_POST["title"]}'",
        	"gal_descripcion='{$_POST["description"]}'"
        );
        $sql="update tbl_galeria set ".implode(", ",$campos)." where gal_codigo='{$_POST["file_id"]}'";
        $db->execute($sql);
        $this->getFiles(true);
        
  	}
  	private function processFiles() {
		$db=Application::GetDatabase();
		$_POST["title"]=$db->escape($_POST["title"]);
		$_POST["description"]=$db->escape($_POST["description"]);
		switch ($_POST["uploadType"]) {
			case "picture":
		  		foreach ($_FILES["images"]["error"] as $key => $error) {
					if ($error == 0) {
						
						$physPath=$_SERVER["DOCUMENT_ROOT"].Application::getPath("/resources/users-uploads/galleries/{$_POST["group"]}");
						if (!file_exists($physPath)) {
							@mkdir($physPath,0777,true);
						}
						
						$name = $_FILES["images"]["name"][$key];
				        $name = $this->safeName($name,$physPath);
						
				        move_uploaded_file( $_FILES["images"]["tmp_name"][$key], $physPath."/".$name) or error_log(error_get_last());
				        $campos=array(
				        	"gal_grupo='{$_POST["group"]}'",
				        	"gal_tipo='{$_POST["uploadType"]}'",
				        	"gal_relacionado='0'",
				        	"gal_temp_id='{$_SESSION["tmpid"]}'",
				        	"gal_titulo='{$_POST["title"]}'",
				        	"gal_descripcion='{$_POST["description"]}'",
				        	"gal_fecha='".date("Y-m-d H:i:s",time())."'",
				        	"gal_sesion='".$db->escape(session_id())."'",
				        	"gal_archivo='{$name}'",
				        	"gal_mime='{$_FILES['images']['type'][$key]}'"
				        );
				        $sql="INSERT INTO tbl_galeria set ".implode(", ",$campos);
				        $db->execute($sql);
				        $this->getFiles(true);
				    }
				}
				break;
			case "youtube":
		        $campos=array(
		        	"gal_grupo='{$_POST["group"]}'",
		        	"gal_tipo='{$_POST["uploadType"]}'",
		        	"gal_relacionado='0'",
		        	"gal_temp_id='{$_SESSION["tmpid"]}'",
		        	"gal_titulo='{$_POST["title"]}'",
		        	"gal_descripcion='{$_POST["description"]}'",
		        	"gal_fecha='".date("Y-m-d H:i:s",time())."'",
		        	"gal_sesion='".$db->escape(session_id())."'",
		        	"gal_archivo=''",
		        	"gal_mime='video/youtube'"
		        );
		        $sql="INSERT INTO tbl_galeria set ".implode(", ",$campos);
		        $db->execute($sql);
		        $this->getFiles(true);
				break;
		}
  		
  	}
	private function safeName($name, $path, $cut=75) {
		
		$extension=substr($name,strrpos($name,".")+1);
		$name=basename($name,".".$extension);
		
	    $name = strtr($name,'áéíóúàèìòùâêîôûäëïöüãõñÁÉÍÓÚÀÈÌÒÙÂÊÎÔÛÄËÏÖÜÑçÇ','aeiouaeiouaeiouaeiouaonaeiouaeiouaeiouaeiouncc');
		$name = preg_replace('/\n|\r/',' ',trim(strtolower($name)));
	    $name = preg_replace('/\.+/',' ',$name);
	    $name = preg_replace('/ +/','_',$name);
	    $name = preg_replace('/([^a-z0-9\._-])/','', $name);
	    $name = substr($name, 0, $cut);

	    $name=trim($name);
	    error_log("{$path}/{$name}");
	    if (file_exists($path."/".$name.".".$extension)) {
	    	$idx=1;
	    	while (file_exists($path."/".$name."_".$idx.".".$extension))
	    		$idx++;
	    	$name.="_".$idx;
	    }
	    return (trim($name) == '' ? 'unknown' : trim($name)).".".$extension;
	}

	public function output() {
		if ($_POST["type"]=="json" && (is_array($this->data) || is_object($this->data))) {
			header("Content-Type: text/json");
			die(json_encode($this->data));
		} else if ($_POST["type"]=="json_text" && (is_array($this->data) || is_object($this->data))) {
			die(json_encode($this->data));
		} else {
			die($this->data);
		}
	}
	
}
  