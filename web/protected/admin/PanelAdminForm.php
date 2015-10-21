<?php
Application::Uses("com.admin.PanelAdmin");
class PanelAdminForm extends PanelAdmin {

	public $currentRecord=array();
	public $mode="";
	public $urlBackTo="";
	public $fields=array();
	private $defaultValues=array(
	);

	public function create() {
		parent::create();
	}
	
	public function init() {
		$retVal=parent::init();
		$this->view->assign("urlBackTo",$this->urlBackTo);
		$retVal=true;
		if (isset($_GET["action"])) {
			switch($_GET["action"]) {
				case "iup":
					$this->moveUp();
					break;
				case "idn":
					$this->moveDown();
					break;
				case "add":
					$retVal=$this->prepareInsert();
					break;
				case "upd":
					$retVal=$this->prepareUpdate();
					break;
				case "del":
					$retVal=$this->prepareDelete();
					break;
			}
		}
		return true;
	}
	
	protected function moveUp() {
		
	}
	
	protected function moveDown() {
		
	}
	
	protected function prepareDelete() {
		$retVal=$this->canDeleteRecord();
		if (!$retVal["status"]) {
			$this->view->assign("template","error.tpl");
			$this->view->assign("err_message",$retVal["err_message"]);
			$this->view->assign("url_back",$retVal["url_back"]?$retVal["url_back"]:Application::GetLink("/admin"));
			return;
		} 

		$db=Application::getDatabase();
		if (!$_POST) {
			$retVal=$this->buildSelectQuery();
				
			$sql=$retVal["query"];
				
			$this->currentRecord=$db->getRow($sql);
			foreach($this->fields as $field => &$data) {
				if (isset($this->currentRecord[$data["tableField"]]))
					$data["value"]=$this->currentRecord[$data["tableField"]];
			}
				
		} else {
			$this->deleteRecord();
		}
	}
	
	protected function canDeleteRecord() {
		return array("status"=>true);
	}
	public function getFilter() {
		if (!$_POST && ($_GET["action"]=="upd" || $_GET["action"]=="del")) return $this->defaultValues["id_record"]."='{$_GET["id"]}'";
		return "";
	}
	
	public function buildSelectQuery() {
		if (!$_POST && ($_GET["action"]=="upd")) {
			$pageSize="1";
			$firstRecord="0";
		}
		
		$retVal=array("query"=>"");
		
		$where=$this->getFilter();
		$sql="SELECT * from {$this->defaultValues["table"]} ";
		if ($where) $sql.='WHERE '.$where." ";
		
		if ($retVal["sort_by"])
			$sql.="ORDER BY ".$retVal["sort_by"]." ".$retVal["order"]." ";

		
		$sql.="LIMIT 0,1";
		$retVal["query"]=$sql;

		return $retVal;
	}
	
	protected function prepareUpdate() {
	
		$db=Application::getDatabase();
		if (!$_POST) {
			$retVal=$this->buildSelectQuery();
				
			$sql=$retVal["query"];
				
			$this->currentRecord=$db->getRow($sql);
			foreach($this->fields as $field => &$data) {
				if (isset($this->currentRecord[$data["tableField"]]))
					$data["value"]=$this->currentRecord[$data["tableField"]];
			}
				
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
			if ($this->fields[$field]["tableField"])
				$campos[]="{$this->fields[$field]["tableField"]} = '".Application::escape($value)."'";
		}
		$sql.=" SET ".implode(", ",$campos)." WHERE ".$this->defaultValues["id_record"]."='{$_GET["id"]}'";
		$db=Application::getDatabase();
		$db->execute($sql);
		Application::redirect($this->urlBackTo);
	}
	
	protected function deleteRecord() {
		
		$sql="DELETE FROM {$this->defaultValues["table"]} ";
		$sql.="WHERE ".$this->defaultValues["id_record"]."='{$_GET["id"]}'";

		$db=Application::getDatabase();
		$db->execute($sql);
		Application::redirect($this->urlBackTo);
	}
	
	protected function prepareInsert() {
		$db=Application::getDatabase();
		if (!$_POST) {
			foreach($this->fields as $field => &$data) {
				$data["value"]="";
			} 
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
			if ($this->fields[$field]["tableField"])
				$campos[]="{$this->fields[$field]["tableField"]} = '".Application::escape($value)."'";
		}
		$sql.=" SET ".implode(", ",$campos);
		$db=Application::getDatabase();
		$db->execute($sql);
		Application::redirect($this->urlBackTo);
	}
	
	public function setMainTable($table) {
		$this->defaultValues["table"]=$table;
	}
	
	public function setIndexField($table) {
		$this->defaultValues["id_record"]=$table;
	}
	
} 