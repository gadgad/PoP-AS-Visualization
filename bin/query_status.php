<?php
    
    function getQueryStatus()
	{
		// 0 - error , 1 - running , 2 - tables ready
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
					$pop_state = 1;
				} else if($idg->getEdgeTblName()==$tbl){
					$edge_state = 1;
				}
		    }
		}
		
		if($pop_state==1 || $edge_state==1){
			ret_res('queries are still running..','RUNNING');
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
			ret_res("query is not running and table doesnt exsist or is locked","ERROR");
		} else if($pop_state==2 && $edge_state==2){
			ret_res("ready to fetch data from db","RUNNING");
		} else {
			ret_res("assertion error - ambiguous status","ERROR");
		}
	}
?>