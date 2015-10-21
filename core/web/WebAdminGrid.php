<?php 
Application::Uses("sys.web.WebAdmin");

class WebAdminGrid extends WebAdmin {

	protected $table="";  
	protected $idx_field="";			

	public $currentRecord=array();
	
	private $defaultValues=array(
		'start' 		=> 0,       // Row start
        'count' 		=> 50,      // Row per page
    	's_index' 		=> 0,       // Sort column index
        's_order' 		=> 0,       // Sort order
        'search_column' => 0,       // Query col index
        'search_mode' 	=> 0,       // Query mode
        'query' 		=> ''
	);

	public function create() {
		parent::create();
	}
	
	public function run() {
    	$this->authenticate();
    	$retVal=$this->init();
		
    	if(isset($_GET["xreq"]) && $retVal) $retVal=$this->processRequest();
		
    	if(isset($_GET["upd"]) && $retVal) $retVal=$this->prepareUpdate();
		
    	if(isset($_GET["add"]) && $retVal) $retVal=$this->prepareInsert();
		
    	if($retVal) $retVal=$this->select();

    	if ($retVal)
        	$this->output();
	}

    public function init() { return true; }

	/**
	 * 
	 * 
	 * 
	 */
	
	/**
	 * 
	 * Metodos de control de flujo del formulario de edicion
	 * 
	 */
	
	public function showGrid() {
		return !isset($_GET["upd"]) && !isset($_GET["add"]);
	}

	public function showForm() {
		return isset($_GET["upd"]) || isset($_GET["add"]);
	}

	/**
	 * 
	 * Metodos de control de flujo de la grilla
	 * 
	 */

	public function select() {return true;}
	
	public function buildSelectQuery() {
		$pageSize=$_POST["count"];
		$firstRecord=intval($_POST["start"]);
		
		if (!$_POST && isset($_GET["upd"])) {
			$pageSize="1";
			$firstRecord="0";
		}
		
		$retVal=array("start"=>$firstRecord,"count"=>$pageSize, "sort_by"=>"", "order"=>"","query"=>"");
		
		if ($_POST["sort_index"]) {
			$gridParameters=Application::getWidgetParameters("grid");
			$si=intVal($_POST["sort_index"]);
			$so=intVal($_POST["sort_order"]);
			if ($gridParameters!=null) {
				$columns=$gridParameters["columns"];
				$sortField=$columns[$si]["field"];
				$sortOrder=($so==0?'ASC':'DESC');
				
				$retVal["sort_by"]=$sortField;
				$retVal["order"]=$sortOrder;
			}
		}

		$where=$this->getFilter();
		$sql="SELECT * from {$this->defaultValues["table"]} ";
		if ($where) $sql.='WHERE '.$where." ";
		
		if ($retVal["sort_by"])
			$sql.="ORDER BY ".$retVal["sort_by"]." ".$retVal["order"]." ";

		
		$sql.="LIMIT {$retVal['start']},{$retVal['count']}";
		$retVal["query"]=$sql;

		return $retVal;
	}
	
	public function getFilter() {
		if (!$_POST && isset($_GET["upd"])) return $this->defaultValues["id_record"]."='{$_GET["id"]}'";
		return "";
	}
	
	public function processRequest() {
		switch($_GET["xreq"]) {
			case "rows":
				$xreq=$this->buildSelectQuery();
				
				$query=trim($xreq["query"]);
				if (!preg_match("/ SQL_CALC_FOUND_ROWS /i",$query)) {
					$sqlExploded=explode(" ",$query);
					$select=array_shift($sqlExploded);
					
					$query="SELECT SQL_CALC_FOUND_ROWS ".implode(" ",$sqlExploded);
				}

				$db=Application::getDatabase();
				$res=$db->execute($query);

				$resTotal=$db->getRow("select found_rows() as total");
				
				$retVal=array(
					"total_records"=>$resTotal["total"]
				);
				$rows=array();
				while($row=$db->getNextRecord($res)) {
					$rows[]=$row;
				}
				$retVal["start"]=$xreq["start"];
				$retVal["count"]=$xreq["count"];
				$retVal["recordset"]=$rows;
				header("Content-Type: text/json");
				die(json_encode($retVal));
				break;
		}
		return true;
	}
	public function prepareUpdate() {
		
		$db=Application::getDatabase();
		if (!$_POST) {
			$retVal=$this->buildSelectQuery();
			
			$sql=$retVal["query"];
			
			$this->currentRecord=$db->getRow($sql);
		} else {
			$this->updateRecord();
		}
		
		//Application::dumpConfig();
		return true;
	}
	
	protected function updateRecord() {
		$sql="UPDATE {$this->defaultValues["table"]} ";
		$campos=array();
		foreach($_POST as $field => $value) {
			$campos[]="{$field} = '".Application::escape($value)."'";
		}
		$sql.=" SET ".implode(", ",$campos)." WHERE ".$this->defaultValues["id_record"]."='{$_GET["id"]}'";
		$db=Application::getDatabase();
		$db->execute($sql);
		Application::redirect(Application::Get('SELF'));
	}
	
	public function prepareInsert() {
		$db=Application::getDatabase();
		if (!$_POST) {
			$this->currentRecord=array();
		} else {
			$this->insertRecord();
		}
		
		//Application::dumpConfig();
		return true;
	}

	protected function insertRecord() {
		$sql="INSERT INTO {$this->defaultValues["table"]} ";
		$campos=array();
		foreach($_POST as $field => $value) {
			$campos[]="{$field} = '".Application::escape($value)."'";
		}
		$sql.=" SET ".implode(", ",$campos);
		$db=Application::getDatabase();
		$db->execute($sql);
		Application::redirect(Application::Get('SELF'));
	}
	
	/**
	 * 
	 * Metodos para Interface con las grillas hijas
	 * 
	 */

	
	public function getDefaults() {
		return $this->defaultValues;
	}
	
	public function setMainTable($table) {
		$this->defaultValues["table"]=$table;
	}
	
	public function setIndexField($table) {
		$this->defaultValues["id_record"]=$table;
	}
	
	public function setFirstRow($row) {
		$this->defaultValues["start"]=$row;
	} 
	
	public function setRows($rows) {
		$this->defaultValues["count"]=$rows;
	} 
	
	public function setIndexColumn($column) {
		$this->defaultValues["s_index"]=$column;
	} 
	
	public function setIndexOrder($order) {
		$this->defaultValues["s_order"]=$order;
	} 
	
	public function setSearchColumn($column) {
		$this->defaultValues["search_column"]=$column;
	} 
	
	public function setSearchMode($mode) {
		$this->defaultValues["search_mode"]=$mode;
	} 
	
}