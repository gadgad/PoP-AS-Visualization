<?php
	require_once("bin/load_config.php");
    require_once("bin/idgen.php");
	
	class QueryManager
	{
		private $PID_MAP;
		private $TABLES_MAP;
		
		public function __construct($blade)
		{
			global $Blade_Map;
			$this->PID_MAP = array();
			$this->TABLES_MAP = array();
			
			$blade = $Blade_Map[$blade];
			$host = (string)$blade["host"];
			$port = (int)$blade["port"];
			$user = (string)$blade["user"];
			$pass = is_array($blade["pass"])?"":(string)$blade["pass"];
			$database = (string)$blade["db"];
			$write_db = $blade["write-db"];
			
			$mysqli = new mysqli($host,$user,$pass,$database,$port);
				
			while($mysqli->connect_error) {
					sleep(3);
					$mysqli = new mysqli($host,$user,$pass,$database,$port);
			}
			
			$result = $mysqli->query('SHOW FULL PROCESSLIST;');
			$num = $result->num_rows;
			for($x = 0 ; $x < $num ; $x++){
			    $row = $result->fetch_assoc();
			    if($row['State']!=NULL && stristr($row['Info'],'create table')!=FALSE){
			    	$tbl = strstr(strstr( $row['Info'] ,'DPV_'),'`',true);
					if($tbl){
						$queryID = substr($tbl, -32);
						$type = (strstr($tbl, "_POP_",true)=="DPV")? "POP" : "EDGE";
						$this->PID_MAP[$queryID][$type] = $row['Id'];
					}
				}
			}
			
			$result = $mysqli->query("show tables from ".$write_db." like 'DPV_%'");
			$num = $result->num_rows;
			for($x = 0 ; $x < $num ; $x++){
			    $row = $result->fetch_row();
				$tbl = $row[0];
				$queryID = substr($tbl, -32);
				$type = (strstr($tbl, "_POP_",true)=="DPV")? "POP" : "EDGE";
				$this->TABLES_MAP[$queryID][$type] = true;
			}
			
			$result = $mysqli->query("show open tables from ".$write_db." like 'DPV_%'");
			$num = $result->num_rows;
			for($x = 0 ; $x < $num ; $x++){
			    $row = $result->fetch_assoc();
				$tbl = $row["Table"];
				$locks = intval($row["In_use"]);
				$queryID = substr($tbl, -32);
				$type = (strstr($tbl, "_POP_",true)=="DPV")? "POP" : "EDGE";
				if($locks > 0) $this->TABLES_MAP[$queryID][$type] = false;
			}
			
			$mysqli->close();
				
		}
	
		// 0 - error , 1 - running , 2 - tables ready
		public function getQueryStatus($QID)
		{
			if(isset($this->PID_MAP[$QID]["POP"]) || isset($this->PID_MAP[$QID]["EDGE"]))
				return 1;
			
			if(isset($this->TABLES_MAP[$QID]["POP"]) && 
			$this->TABLES_MAP[$QID]["POP"] == true &&
			isset($this->TABLES_MAP[$QID]["EDGE"]) && 
			$this->TABLES_MAP[$QID]["EDGE"] == true)
				return 2;
				
			return 0;
		}
		
		public function getPIDS($QID)
		{
			$tmp = array();
			if(isset($this->PID_MAP[$QID]["POP"]))
				$tmp[] = $this->PID_MAP[$QID]["POP"];
			if(isset($this->PID_MAP[$QID]["EDGE"]))
				$tmp[] = $this->PID_MAP[$QID]["EDGE"];
			return $tmp;
		}
		
	}
	
?>