<?php
/*
 * queryManager class
 * this interface is used for acquiring the latest running status
 * of each query, and update the status to a different value
 * if needed.
 */
	require_once("bin/load_config.php");
    require_once("bin/idgen.php");
	require_once("bin/DBConnection.php");
	
	class DBConnectionError extends Exception {}
	class MissingParametersError extends Exception {}
	
	class QueryManager
	{
		private $PID_MAP;
		private $TABLES_MAP;
		private $Status_MAP;
		private $queryXML;
		private $queryFilename;
		private $blade;
		
		public static function load($blade){
			try {
				return new QueryManager($blade);
			} catch(DBConnectionError $e) {
				return null;
			}
		}
		
		public function __construct($blade)
		{
			$this->blade = $blade;
			$this->PID_MAP = array();
			$this->TABLES_MAP = array();
			$this->Status_MAP = array(-1=>"started",
									   0=>"error", // db-error
									   1=>"running", // db-running
									   2=>"db-ready",
									   3=>"fetching-xml",
									   4=>"xml-ready",
									   5=>"kml-ready");
			
			$this->queryFilename = "xml/query.xml";
			$this->queryXML = simplexml_load_file($this->queryFilename);	
		}
		
		private function getStatusFromDB()
		{
			global $Blade_Map;
			$blade = $Blade_Map[$this->blade];
			$host = (string)$blade["host"];
			$port = (int)$blade["port"];
			$user = (string)$blade["user"];
			$pass = is_array($blade["pass"])?"":(string)$blade["pass"];
			$database = (string)$blade["db"];
			$write_db = (string)$blade["write-db"];
			
			$mysqli = new DBConnection($host,$user,$pass,$database,$port,5);
			if($mysqli->connect_error) throw new DBConnectionError();

			
			$result = $mysqli->query('SHOW FULL PROCESSLIST;');
			$num = $result->num_rows;
			for($x = 0 ; $x < $num ; $x++){
			    $row = $result->fetch_assoc();
			    if($row['State']!=NULL && stristr($row['Info'],'create table')!=FALSE){
			    	$tbl = strstr(strstr( $row['Info'] ,'DPV_'),'`',true);
					if($tbl){
						$tableID = substr($tbl, -32);
						$type = (strstr($tbl, "_POP_",true)=="DPV")? "POP" : "EDGE";
						$this->PID_MAP[$tableID][$type] = $row['Id'];
					}
				}
			}
			
			$result = $mysqli->query("show tables from ".$write_db." like 'DPV_%'");
			$num = $result->num_rows;
			for($x = 0 ; $x < $num ; $x++){
			    $row = $result->fetch_row();
				$tbl = $row[0];
				$tableID = substr($tbl, -32);
				$type = (strstr($tbl, "_POP_",true)=="DPV")? "POP" : "EDGE";
				$this->TABLES_MAP[$tableID][$type] = true;
			}
			
			$result = $mysqli->query("show open tables from ".$write_db." like 'DPV_%'");
			$num = $result->num_rows;
			for($x = 0 ; $x < $num ; $x++){
			    $row = $result->fetch_assoc();
				$tbl = $row["Table"];
				$locks = intval($row["In_use"]);
				$tableID = substr($tbl, -32);
				$type = (strstr($tbl, "_POP_",true)=="DPV")? "POP" : "EDGE";
				if($locks > 0) $this->TABLES_MAP[$tableID][$type] = false;
			}
			
			$mysqli->close();
		}

		public function setQueryStatus()
		{
			$allQIDS = false;
			$queryID = func_get_arg(0);
			$new_status = func_get_arg(1);
			if(func_num_args()==3) $allQIDS = func_get_arg(2);
			
			$this->queryXML = simplexml_load_file($this->queryFilename);	
			$result = $this->queryXML->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');
			if($allQIDS){
				$tableID = (string)$result[0]->tableID;
				$result = $this->queryXML->xpath('/DATA/QUERY[tableID="'.$tableID.'"]');
				foreach($result as $rs){
					$rs->lastKnownStatus=$new_status;
				}
			} else {
				$result[0]->lastKnownStatus=$new_status;
			}
			$this->queryXML->asXML($this->queryFilename);
		}
		
		public function setQueryRunningStatus()
		{
			$allQIDS = false;
			$queryID = func_get_arg(0);
			$new_status_id = func_get_arg(1);
			if(func_num_args()==3) $allQIDS = func_get_arg(2);
			
			$this->queryXML = simplexml_load_file($this->queryFilename);	
			$result = $this->queryXML->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');
			if($allQIDS && $new_status_id<=2){
				$tableID = (string)$result[0]->tableID;
				$result = $this->queryXML->xpath('/DATA/QUERY[tableID="'.$tableID.'"]');
				foreach($result as $rs){
					$rs->lastRunningState=$this->getStatusMsg($new_status_id);
				}
			} else {
				$result[0]->lastRunningState=$this->getStatusMsg($new_status_id);
			}
			$this->queryXML->asXML($this->queryFilename);
		}
	
		// 0 - error , 1 - running , 2 - db-ready, 3 - fetching-xml,  4 - xml-ready, 5 - kml-ready
		public function getQueryStatus()
		{
			if(func_num_args()==2){
				$QID = func_get_arg(0);
				$TID = func_get_arg(1);
				$idg = new idGen($QID,$TID);
			} else if(func_num_args()==1) {
				$QID = func_get_arg(0);
				$idg = new idGen($QID);	
			} else {
				throw new MissingParametersError();
			}
			$tableID = $idg->getTableID();
			$queryID = $QID;
			$result = $this->queryXML->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');
			$lastRunningState = (string)$result[0]->lastRunningState;
			$stateID = array_search($lastRunningState, $this->Status_MAP);
		
			if($stateID>=2){
				return $stateID;
			}
			
			try {
				$this->getStatusFromDB();
			} catch (DBConnectionError $e){
				throw $e; // rethrow exception...
			}
			
			if(array_key_exists($tableID, $this->PID_MAP))
				if(isset($this->PID_MAP[$tableID]["POP"]) || isset($this->PID_MAP[$tableID]["EDGE"])){
					if($lastRunningState == 'started') $this->setQueryRunningStatus($queryID, 1,true);
					return 1;	
				}

            if(isset($this->TABLES_MAP[$tableID]["POP"]) && 
	            $this->TABLES_MAP[$tableID]["POP"] == true &&
	            isset($this->TABLES_MAP[$tableID]["EDGE"]) && 
	            $this->TABLES_MAP[$tableID]["EDGE"] == true){
	            	if($lastRunningState == 'running') $this->setQueryRunningStatus($queryID, 2,true);
                    return 2;
				}
				
			return 0; // the 'error' state
		}
		
		public function getStatusMsg($status_code){
			if (!isset($this->Status_MAP[$status_code]))
				return "unknown status";
			return $this->Status_MAP[$status_code];
		}
		
		public function getStateIDFromRunningStatus($status){
			return array_search($status, $this->Status_MAP);
		}
		
		public function getPIDS($QID)
		{
			$idg = new idGen($QID);
			$tableID = $idg->getTableID();
			$tmp = array();
			if(isset($this->PID_MAP[$tableID]["POP"]))
				$tmp[] = $this->PID_MAP[$tableID]["POP"];
			if(isset($this->PID_MAP[$tableID]["EDGE"]))
				$tmp[] = $this->PID_MAP[$tableID]["EDGE"];
			return $tmp;
		}
		
	}
	
?>