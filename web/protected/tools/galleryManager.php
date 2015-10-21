<?php
Application::Uses("com.tools.usersUtilities");
class galleryManager {

	static public function unlink($rec) {
		$db=Application::getDatabase();
	  	$physPath=$_SERVER["DOCUMENT_ROOT"].Application::getPath("/resources/users-uploads/galleries/{$rec["gal_grupo"]}/{$rec["gal_archivo"]}");
	  	@unlink($physPath);
	    $db->execute("DELETE FROM tbl_galeria WHERE gal_codigo = '{$rec["gal_codigo"]}'");
	}

  	static public function getElement($group,$related,$index=1) {
  		$elements=self::getElements($group,$related,$index-1,1);
  		return $elements[0];  	
  	}
  	
  	static public function getElements($group,$related,$index=0,$quantity=1) {
  		$db=Application::getDatabase();
  		$elements=$db->getRecords("tbl_galeria","gal_grupo='{$group}' and gal_relacionado='{$related}'","gal_fecha asc",$index,$quantity);
  		return $elements["data"];
  	}
  	 
  	static public function save($codres,$grupos=array(),$tmpid='') {
	    $grupos = is_array($grupos) ? $grupos : array($grupos);
	    $db=Application::getDatabase();
	    $db->execute("UPDATE tbl_galeria SET ".
                   "gal_temp_id = '',".
                   "gal_sesion = '',".
                   "gal_relacionado = '".$codres."'".
                 "WHERE ".
                   "gal_relacionado = '0' AND ".
                   "gal_temp_id = '".$db->escape($tmpid)."' AND ".
                   "gal_sesion = '".$db->escape(session_id())."' AND ".
                   "gal_grupo IN ('".implode("', '", $grupos)."')");
	}

  	static public function delete($codres,$grupos=array()) {
	    $db = Application::GetDatabase();
	    $grupos = is_array($grupos) ? $grupos : array($grupos);
	    $res = $db->execute("SELECT * ".
                         "FROM tbl_galeria ".
                         "WHERE ".
                         "gal_relacionado = '{$codres}' AND ".
                         "gal_grupo IN ('".implode("', '", $grupos)."')");
    	while ($rec=$db->getNextRecord($res)) {
      		self::unlink($rec);
    	}
  	}

  	static public function clear() {
	    $db = Application::GetDatabase();
    	$recs = $db->execute("SELECT * FROM tbl_galeria ".
                         "WHERE ".
                         "gal_relacionado = '0' AND ".
                         "(".
                           "gal_fecha < '".date('Y-m-d', mktime(0,0,0, date('m'), intval(date('d'))-2, date('Y')))."' OR ".
                           "gal_sesion = '".$db->escape(session_id())."'".
                         ")");
    
    	while ($rec=$db->getNextRecord($recs)) {
      		self::unlink($rec);
    	}
  	}
}
