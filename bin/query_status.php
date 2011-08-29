<?php
	require_once("bin/load_config.php");
    require_once("bin/idgen.php");
	
	$PID_MAP = array();
	
	// 0 - error , 1 - running , 2 - tables ready
    function getQueryStatus($QID,$blade)
	{
		global $PID_MAP;
		global $Blade_Map;
		
		$queryID = $QID;
		
		//$queries = simplexml_load_file("xml\query.xml");
		//$res = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/blade');
		//$selected_blade = $res[0];
		
		$selected_blade = $blade;
		$blade = $Blade_Map[$selected_blade];
		$host = (string)$blade["host"];
		$port = (int)$blade["port"];
		$user = (string)$blade["user"];
		$pass = is_array($blade["pass"])?"":(string)$blade["pass"];
		$database = (string)$blade["db"];
		$idg = new idGen($queryID);
		
		$edge_state = 0;
		$pop_state = 0;
		
		$mysqli = new mysqli($host,$user,$pass,$database,$port);
				
		while($mysqli->connect_error) {
				sleep(3);
				$mysqli = new mysqli($host,$user,$pass,$database,$port);
 		    	//ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error,"ERROR");
		}
		
		$result = $mysqli->query('SHOW FULL PROCESSLIST;');
		$num = $result->num_rows;
		for($x = 0 ; $x < $num ; $x++){
		    $row = $result->fetch_assoc();
		    if($row['State']!=NULL && stristr($row['Info'],'create table')!=FALSE){
		    	$tbl = strstr(strstr( $row['Info'] ,'DPV_'),'`',true);
				if($idg->getPoPTblName()==$tbl){
					$PID_MAP[$queryID][] = $row['Id'];
					$pop_state = 1;
				} else if($idg->getEdgeTblName()==$tbl){
					$PID_MAP[$queryID][] = $row['Id'];
					$edge_state = 1;
				}
		    }
		}
		
		if($pop_state==1 || $edge_state==1){
			return 1;
		}
		
		if($pop_state==0){
			$result = $mysqli->query("show tables from DIMES_POPS_VISUAL like '".$idg->getPoPTblName()."'");
			$num = $result->num_rows;
			if($num>0){
				$result = $mysqli->query("show open tables from DIMES_POPS_VISUAL like '".$idg->getPoPTblName()."'");
				$num = $result->num_rows;
				if($num>0){
					$row = $result->fetch_assoc();
					if(intval($row['In_use'])==0){
						$pop_state = 2;
					}	
				} else { // table is not open / locked
					$pop_state = 2;
				}
			} 	
		}
		
		if($edge_state==0){
			$result = $mysqli->query("show tables from DIMES_POPS_VISUAL like '".$idg->getEdgeTblName()."'");
			$num = $result->num_rows;
			if($num>0){
				$result = $mysqli->query("show open tables from DIMES_POPS_VISUAL like '".$idg->getEdgeTblName()."'");
				$num = $result->num_rows;
				if($num>0){
					$row = $result->fetch_assoc();
					if(intval($row['In_use'])==0){
						$edge_state = 2;
					}	
				} else { // table is not open / locked
					$edge_state = 2;
				}
			} 	
		}
		
		$mysqli->close();
		
		if($pop_state==0 || $edge_state==0){
			return 0;
		} 
		if($pop_state==2 && $edge_state==2){
			return 2;
		}
		return 0;
	}
?>