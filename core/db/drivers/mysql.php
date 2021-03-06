<?php
Application::Uses("sys.exceptions.Handler"); 
class TDB_Mysql {

	private $handler=null;
	
	public function __construct($host,$user,$pass,$name) {
		
		$this->handler=@mysql_connect($host,$user,$pass);
		if (!$this->handler) {
			throw new EDatabaseError("Imposible conectar a la base de datos");
		}
		if (!mysql_select_db($name)) {
			throw new EDatabaseError("La base de datos solicitada no existe");
		}
	}
	
	public function version() {
		return $this->handler->client_version;
	}
	public function table_exists($tableName) {
		$sql="SHOW TABLES LIKE '{$tableName}';";
		$res=$this->execute($sql);
		$row=$res->fetch_assoc();
		return ($row?true:false);
	}
	
	public function getTables() {
		$sql="SHOW TABLES;";
		$res=$this->execute($sql);
		$tables=array();
		while ($tbl=$this->getNextRecord($res)) {
			$key="Tables_in_".Application::get("DB_NAME");
			$tables[]=$tbl[$key];
		}
		return $tables;
	}
		
	public function execute($sql) {
		$retVal=$this->handler->query($sql);
		if (!$retVal) {
			error_log($sql);
			throw new EQueryError("Error al ejecutar SQL: ".mysqli_error($this->handler));
		}
		return $retVal;
	}
	
	public function getRow($sql) {
		$res=$this->execute($sql);
		$rec=$res->fetch_assoc();
		if ($this->handler->error) {
			throw new EQueryError($this->handler->error);
		}
		return $rec;
	}
	
	public function getNextRecord($res) {
		return $res->fetch_assoc();
	}
	
	public function escape($str) {
		return $this->handler->real_escape_string($str);
	}
	
	public function close() {
		$this->handler->close();
	}
	
	public function lastInsertId() {
		return $this->handler->insert_id;
	}

	public function insert($table, $data) {
		if (!is_array($data)) {
			throw new EDatasetError("No se ha indicado un set de campos y valores");
		}
		$fields=array();
		foreach($data as $field => $value) {
			$escapedValue=Application::escape($value);
			if (!is_null($value))
				$fields[]="{$field}='{$escapedValue}'";
			else
				$fields[]="{$field}=NULL";
		}
		$sql="INSERT INTO {$table} SET ".implode(", ",$fields);
		$this->execute($sql);
		return $this->lastInsertId();
	}

	public function update($table, $data,$condition=false) {
		if (!is_array($data)) {
			throw new EDatasetError("No se ha indicado un set de campos y valores");
		}

		if (!$condition) {
			$physPath=Application::Get('PHYS_PATH');
			$configFolder=$physPath."/config";
			if (file_exists($configFolder."/db.php")) {
				include($configFolder."/db.php");
				if (isset($database[$table])) {
					$_data=array();
					foreach($data as $field => $value) {
						if (isset($database[$table]["fields"][$field]))
							$_data[$field]=$value;
					}
					$data=$_data;
					foreach($database[$table]["keys"] as $key) {
						if ($key["primary"]) {
							$fields=explode(",",$key["fields"]);
							$condition=array();
							foreach($fields as &$field) {
								$field=trim($field);
								$field=stringUtils::replace_all("`","",$field);
								$field="{$field}";
								if (isset($data[$field])) {
									$val=Application::escape($data[$field]);
									$condition[]="`{$field}`='{$val}'";
								}
							}
							break;
						}
					}
				}
			}
		}
		

		if (is_array($condition)) $where="WHERE ".implode(" AND ",$condition);
		elseif ($condition) $where="WHERE ".$condition;
		else $where="";
		
		$fields=array();
		foreach($data as $field => $value) {
			$escapedValue=Application::escape($value);
			if (!is_null($value))
				$fields[]="{$field}='{$escapedValue}'";
			else
				$fields[]="{$field}=NULL";
		}
		$sql="UPDATE {$table} SET ".implode(", ",$fields)." ".$where;
		$this->execute($sql);
	}

	public function delete($table, $condition) {
		if (is_array($condition)) $where="WHERE ".implode(" AND ",$condition);
		elseif ($condition) $where="WHERE ".$condition;
		else $where="";
		$sql="DELETE FROM {$table} ".$where;
		$this->execute($sql);
	}
	
	public function getRecords($table,$condition="",$order="",$start=-1,$number=-1,$debug=false) {
		if (is_array($condition)) $where="WHERE ".implode(" AND ",$condition);
		elseif ($condition) $where="WHERE ".$condition;
		else $where="";
		
		if (trim($where)=="WHERE") $where="";
		if ($start!=-1 || $number!=-1) {
			$limits=array();
			if ($start!=-1) $limits[]=$start;
			if ($number!=-1) $limits[]=$number;
			$limit="LIMIT ".implode(", ",$limits);
		}
		else $limit="";
		
		if ($order) $order="ORDER BY {$order}";
		else $order="";

		$distinct="";
		if (substr($table,0,9)=="distinct ") {
			$distinct="DISTINCT ";
			$table=substr($table,9);
		}
		$query="select {$DISTINCT}SQL_CALC_FOUND_ROWS * from {$table} {$where} {$order} {$limit}";
		if ($debug) {
			$content="";
			if (file_exists("/tmp/salefutbol_sql.log"))
				$content=file_get_contents("/tmp/salefutbol_sql.log");
			file_put_contents("/tmp/salefutbol_sql.log",$content.date("Y-m-d H:i:s").": {$query}\n");
		}
		$res=$this->execute($query);
		$records=array();
		while ($rec=$this->getNextRecord($res)) {
			$records[]=$rec;
		}
		$total=$this->getRow("select found_rows() as total");
		$retVal=array(
			"data"=>$records,
			"records"=>$total["total"]
		);
		return $retVal;
	}
	
	public function getRecord($table,$condition="") {
		$retVal=$this->getRecords($table,$condition,"",0,1);
		return $retVal["data"][0];
	}
	
	public function performTransaction(array $sqls) {
		
	}
}

