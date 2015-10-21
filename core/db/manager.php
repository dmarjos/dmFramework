<?php
class manager {
	private static $db;
	private static $buildRequired;
	private static $database;
	public static function checkDatabase() {
		self::$db=Application::getDatabase();
		$physPath=Application::Get('PHYS_PATH');
		$configFolder=$physPath."/config";
		if (!file_exists($configFolder."/db.php")) return false;

		if (!is_writable($configFolder)) {
			throw new Exception("Folder {$configFolder} should be writable");
		}
		if (!file_exists($configFolder."/appSetup.php")) 
			self::$buildRequired=true;
		else {
			require_once($configFolder."/appSetup.php");
			self::$buildRequired=(Application::Get("DB_VERSION")>$lastDBVersion);			
		}
		
		if (!self::$buildRequired) self::$buildRequired=Application::Get("DB_FORCE_REBUILD");
		
		require_once($configFolder."/db.php");
		
		self::$database=$database;
		
		$res=self::$db->execute("show tables");
		$tables=array();
		while ($rec=self::$db->getNextRecord($res)) {
			$key="Tables_in_".Application::get("DB_NAME");
			$tableName=$rec[$key];
			$tables[$tableName]=1;
			if (!isset(self::$database[$tableName]) && Application::Get("DB_REMOVE_UNDEFINED_TABLES"))
				self::$db->execute("DROP TABLE {$tableName}");
		}
		foreach(self::$database as $tbl_name=>$definition) {
			if (!isset($tables[$tbl_name])){
				if ($definition["view"]) {
					$createTable="CREATE VIEW {$tbl_name} as {$definition["view"]}";
				} else {
					$columns=array();
					foreach($definition["fields"] as $fieldName=>$column) {
						$columnDef="`{$fieldName}` {$column["type"]}";
						if ($column["length"]) $columnDef.="({$column["length"]})";
						if ($column["extra"]) $columnDef.=" ".$column["extra"];
						$columns[]=$columnDef;
					}
					$keys=array();
					foreach($definition["keys"] as $keyDef) {
						if ($keyDef["primary"])
							$keys[]="PRIMARY KEY ({$keyDef["fields"]})";
						else if (strtolower($keyDef["key_type"])!="fulltext")
							$keys[]="KEY `{$keyDef["key_name"]}` ({$keyDef["fields"]})";
					}
					$createTable="CREATE TABLE {$tbl_name} (".implode(", ",$columns).($keys?", ".implode(",",$keys):'').") ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_spanish_ci;";
				}
				//die($createTable);
				try {
				self::$db->execute($createTable);
				} catch (Exception $e) {
					die("Error en SQL: ".$createTable."<br/>".$e->getMessage());
				}
				if ($definition["initial_records"]) {
					foreach($definition["initial_records"] as $data)
						self::$db->insert($tbl_name,$data);
				}			
			} else {
				if (!isset($definition["view"])) 
					self::checkTable($tbl_name);
			}
		}
		file_put_contents($configFolder."/appSetup.php","<?php\n\$lastDBVersion='".Application::get("DB_VERSION")."';\n ?>");
		return true;
	}
	
	private static function checkTable($tblName) {
		if (!self::$buildRequired) return true;
		
		$rec=self::$db->getRow("show create table {$tblName}");
		
		$sql=$rec["Create Table"];
		preg_match_all("/\n[\s]*([^\s]+)\s([\w]+)(\([^\)]+\))?(\n\))?/s", $sql, $matches);
		
		if (!preg_match("/DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci/s",$sql))
			self::$db->execute("ALTER TABLE {$tblName} CONVERT TO CHARACTER SET latin1 COLLATE latin1_spanish_ci");
		
		$fields=$matches[1];
		$types=$matches[2];
		$sizes=$matches[3];
		$fieldsInTable=array();
		foreach($fields as $idx=>$field){
			if (substr($field,0,1)=="`" && substr($field,-1)=="`") {
				$fieldName=substr($field,1,-1);
				$size=$sizes[$idx];
				$size=str_replace("(","",$size);
				$size=str_replace(")","",$size);
				$fieldsInTable[$fieldName]=array("type"=>$types[$idx],"length"=>$size);
			}
		}
		foreach(self::$database[$tblName]["fields"] as $fieldName=>$field) {
			if (!$fieldsInTable[$fieldName]) {
				$columnDef="`{$fieldName}` {$field["type"]}";
				if ($field["length"]) $columnDef.="({$field["length"]})";
				if ($field["extra"]) $columnDef.=" ".$field["extra"];
				self::$db->execute("ALTER TABLE {$tblName} ADD {$columnDef}");
			} else {
				if ($field["type"]!=$fieldsInTable[$fieldName]["type"] || $field["length"]!=$fieldsInTable[$fieldName]["length"]) {
					$columnDef="`{$fieldName}` {$field["type"]}";
					if ($field["length"]) $columnDef.="({$field["length"]})";
					if ($field["extra"]) $columnDef.=" {$field["extra"]}";
//					die("ALTER TABLE {$tblName} CHANGE `{$fieldName}` {$columnDef}");
					self::$db->execute("ALTER TABLE {$tblName} CHANGE `{$fieldName}` {$columnDef}");
				}
			}
		}
		foreach($fieldsInTable as $fieldName=>$fieldDef) {
			if (!isset(self::$database[$tblName]["fields"][$fieldName]))
				self::$db->execute("ALTER TABLE {$tblName} DROP `{$fieldName}`");
		}
		foreach(self::$database[$tblName]["keys"] as $key) {
			if ($key["primary"]) continue;
			$fields=explode(",",$key["fields"]);
			foreach($fields as &$field) {
				$field=trim($field);
				$field=stringUtils::replace_all("`","",$field);
				$field="`{$field}`";
			}
			$key["fields"]=implode(",",$fields);
			$regExp="/KEY `{$key["key_name"]}` \({$key["fields"]}\)/sim";
			if (!preg_match_all($regExp,$rec["Create Table"])) {
				if (preg_match("/KEY `{$key["key_name"]}`/s",$rec["Create Table"])) {
					self::$db->execute("DROP INDEX `{$key["key_name"]}` on `{$tblName}`");
					self::$db->execute("CREATE ".(strtoupper($key["key_type"])=="FULLTEXT"?"FULLTEXT ":"")."INDEX `{$key["key_name"]}` on `{$tblName}` ({$key["fields"]})");
				} else {
					self::$db->execute("CREATE ".(strtoupper($key["key_type"])=="FULLTEXT"?"FULLTEXT ":"")."INDEX `{$key["key_name"]}` on `{$tblName}` ({$key["fields"]})");
				}
			}
		}
		return true;		
	}
}