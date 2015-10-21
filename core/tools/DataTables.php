<?php
Application::Uses("sys.tools.datesUtils");

class DataTables {
	
	private $db;
	private $parameters;
	private $columns=array();
	private $columnsParameters=array();
	public function __construct($parameters) {
		$this->db=Application::GetDatabase();
		$this->parameters=$parameters;
	}
	
	public function setColumns($columns,$columnsParameters=array()) {
		$this->columns=$columns;
		$this->columnsParameters=$columnsParameters;
	}

	public function getData() {
		$sLimit = "";
		if ( isset( $this->parameters['iDisplayStart'] ) && $this->parameters['iDisplayLength'] != '-1' ) {
			$sLimit = "LIMIT ".intval( $this->parameters['iDisplayStart'] ).", ".intval( $this->parameters['iDisplayLength'] );
		}
		
		
		/*
		 * Ordering
		*/
		$sOrder = "";
		if ( isset( $this->parameters['iSortCol_0'] ) ) {
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $this->parameters['iSortingCols'] ) ; $i++ ) {
				if ( $this->parameters[ 'bSortable_'.intval($this->parameters['iSortCol_'.$i]) ] == "true" ) {
					$sOrder .= "`".$this->columns[ intval( $this->parameters['iSortCol_'.$i] ) ]."` ".($this->parameters['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
				}
			}
		
			$sOrder = substr_replace( $sOrder, "", -2 );
			if ( $sOrder == "ORDER BY" ) {
				$sOrder = "";
			}
		}
		
		if (isset($this->parameters["order_by"])) {
			if ($sOrder=="") {
				$sOrder="ORDER BY ";
			} else {
				$sOrder.=", ";
			}
			$sOrder.=$this->parameters["order_by"];
		}
		/*
		 * Filtering
		* NOTE this does not match the built-in DataTables filtering which does it
		* word by word on any field. It's possible to do here, but concerned about efficiency
		* on very large tables, and MySQL's regex functionality is very limited
		*/
		$sWhere = "";
		if ( isset($this->parameters['sSearch']) && $this->parameters['sSearch'] != "" ) {
			$sWhere = "WHERE (";
			for ( $i=0 ; $i<count($this->columns) ; $i++ ) {
				$sWhere .= "`".$this->columns[$i]."` LIKE '%".mysql_real_escape_string( $this->parameters['sSearch'] )."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}
		
		for ( $i=0 ; $i<count($this->columns) ; $i++ ) {
			if ( isset($this->parameters['bSearchable_'.$i]) && $this->parameters['bSearchable_'.$i] == "true" && $this->parameters['sSearch_'.$i] != '' ) {
				if ( $sWhere == "" ) {
					$sWhere = "WHERE ";
				} else {
					$sWhere .= " AND ";
				}
				$sWhere .= "`".$this->columns[$i]."` LIKE '%".$this->db->escape($this->parameters['sSearch_'.$i])."%' ";
			}
		}
		
		if ($this->parameters["where"]) {
			if (!empty($sWhere)) $sWhere.=" and "; else $sWhere=" WHERE ";
			if (is_array($this->parameters["where"]))
				$sWhere.=implode(" AND ",$this->parameters["where"]);
			else
				$sWhere.=$this->parameters["where"];
		}
		/*
		 * SQL queries
		* Get data to display
		*/
		$sQuery = "SELECT SQL_CALC_FOUND_ROWS ";
		if (!$this->parameters["fields_tables"]) {
			if ($this->parameters["id_field"]) $sQuery.=$this->parameters["id_field"].", ";
			$sQuery .="`".str_replace(" , ", " ", implode("`, `", $this->columns))."` FROM {$this->parameters["table"]}";
		} else {
			$sQuery.=$this->parameters["fields_tables"]." ";
		}
		
		$sQuery.=" {$sWhere} {$sOrder} {$sLimit}";
		
		if ($this->parameters["debug_sql"]==true)
			error_log($sQuery);
		
		if ($this->parameters["die_sql"]==true)
			die($sQuery);
		
		$res=$this->db->execute($sQuery);
		
		$total=$this->db->getRow("SELECT FOUND_ROWS() as total");
		$iFilteredTotal = $total["total"];
		
		$total=$this->db->getRow("SELECT count({$this->columns[0]}) as total from {$this->parameters["table"]}");
		$iTotal = $total["total"];
		
		$output=array(
				"sEcho" => intval($this->parameters['sEcho']),
				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iFilteredTotal,
				"aaData" => array()
		);		/* Individual column filtering */
		if ($this->parameters["debug_sql"]==true)
			$output["sql"]=$sQuery;
      	while ($rec=$this->db->getNextRecord($res)) {
			$row = array();
			for ( $i=0 ; $i<count($this->columns) ; $i++ ) {
				$columnName=$this->columns[$i];
				if ( $columnName != ' ' ) {
					/* General output */
					$value=$rec[$columnName];
					if ($this->columnsParameters[$columnName]) {
						if (isset($this->columnsParameters[$columnName]["encoder"])) {
							$function=$this->columnsParameters[$columnName]["encoder"];
							$value=$function($value);
						}
						if (isset($this->columnsParameters[$columnName]["formatAmount"])) {
							$value=sprintf("%01.2f",$value);
						}
						if (isset($this->columnsParameters[$columnName]["maxlength"])) {
							if(strlen($value)>intval($this->columnsParameters[$columnName]["maxlength"])) {
								$value=substr($value,0,intval($this->columnsParameters[$columnName]["maxlength"])-3)."...";
							} else if(strlen($value)<intval($this->columnsParameters[$columnName]["maxlength"])) {
								for ($x=strlen($value)+1; $x<=intval($this->columnsParameters[$columnName]["maxlength"]); $x++) {
									$value.="&nbsp;";
								}
							}
						}
						if (isset($this->columnsParameters[$columnName]["formatDate"])) {
							$fromMask=$this->columnsParameters[$columnName]["formatDate"]["fromFormat"];
							$toMask=$this->columnsParameters[$columnName]["formatDate"]["toFormat"];
							$value=datesUtils::formatDateTime($value,$fromMask,$toMask);
						}
					}
					$row[] = $value;
				}
			}
			$row[]=$rec[$this->parameters['id_field']];
				
			$output['aaData'][] = $row;
		}
		return $output;		
	}
}