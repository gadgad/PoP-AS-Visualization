<?php

/*
 * this file is the 'server-side' of the 'generating new query' section, 
 * and responsible of fetching the relevant data 
 */


	require_once("bin/load_config.php");
	require_once("bin/idgen.php");
	require_once("bin/writeToXML.php");
	require_once("bin/backgrounder.php");
	require_once("bin/query_status.php");
	require_once("bin/DBConnection.php");	
	require_once("verify.php");
				
	// Turn off all error reporting
	error_reporting(E_ERROR);
	
	if(!isset($_POST["func"]))
	{
		echo 'missing parameters!';
		die();
	}
	
	// globals
	if(isset($_POST['query'])){
		$queryID = $_POST['query'];
		$queries = simplexml_load_file('xml/query.xml');							
		$result = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');
		if(empty($result)){
			ret_res("query doesn't exists!","ERROR");
		}
		$result = $result[0];
		$selected_blade = (string)$result->blade;       		
	} else {
		$selected_blade = isset($_POST["blade"])? $_POST["blade"] : $GLOBALS['DEFAULT_BLADE'];	
	}
	
	// setting connection parameters
	$blade = $Blade_Map[$selected_blade];
	$host = (string)$blade["host"];
	$port = (int)$blade["port"];
	$hostNport = (string)$blade["host"].":".(string)$blade["port"];
	$user = (string)$blade["user"];
	$pass = is_array($blade["pass"])?"":(string)$blade["pass"];
	$database = (string)$blade["db"];
	$write_db = (string)$blade["write-db"];
		
	function ret_res($message, $type)
	{
		header('Content-type: application/json');
		echo json_encode(array("result"=>$message ,"type"=>$type));
		die();	
	}
	
	// executs the query and parses the result to a string with a ' ' (space) delimiter 
	function parse($mysqli,$query){
		$res = "";			
		if ($result = $mysqli->query($query)){
        	 while ($row = $result->fetch_assoc()) {
		        foreach($row as $key => $value){
					$pos = strpos($res,$value);					
					if($pos === false) {
					 	$res .= $value . " ";
					}
				}	
		     }
        }
		$result->close();
		return $res;
	}
	
	// getting the available tables from the DB that match one of the formats (by week and year) 
	function getTblFromDB($mysqli,$table,$year,$week){
					
		$query1 = "'".$table."\_".$year."\_week_".$week."'";		
		$query2 = "'".$table."\_".$year."\_week_".$week."\_%'";		
		$query3 = "'".$table."\_".$year."\_".$week."'";		
		$query4 = "'".$table."\_".$year."\_".$week."\_%'";					
		
		$query = "select TABLE_NAME from INFORMATION_SCHEMA.TABLES WHERE table_schema='".$GLOBALS["DEFAULT_SCHEMA"]."' and (table_name like ".$query1." or table_name like ".$query2." or table_name like ".$query3." or table_name like ".$query4.")";
		$res = parse($mysqli,$query);
		return $res;        
	}
	
	// tests the connection to the DB
	if($_POST["func"]=="testConnection")
	{ 
		if(isset($pass) && $pass!=""){
			$linkID = mysql_connect($hostNport, $user, $pass) or ret_res("Could not connect to host. Try again later.", "ERROR");
		} else {
			$linkID = mysql_connect($hostNport, $user) or ret_res("Could not connect to host.  Try again later.", "ERROR");
		} 
		mysql_select_db($database, $linkID) or ret_res("Could not find database. Choose another blade.", "ERROR");
		mysql_close($linkID);
		ret_res($selected_blade."", "GOOD");
	}
        
//-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------        
    
    // acording to the blade selected, retrives the available years
    if($_POST["func"]=="getYears")
	{
		$blade = $_POST["blade"];
		
		// Searching the info in weeks.xml
		$xml = simplexml_load_file("xml/weeks.xml");
		$result = $xml->xpath('/DATA/blade[@name="'.$blade.'"]/date');								
		if($result!=FALSE){
			foreach($result as $i=>$value){
				$years[] = (string)$result[$i]->attributes()->year;					
			}								
			header('Content-type: application/json');
			echo json_encode(array("years"=>$years,"type"=>"GOOD"));
		}else{ // date for the blade wasnt found
			$res = $xml->xpath('/DATA/blade[@name="'.$blade.'"]');
			if($res!=FALSE){ // the blade apears in the file and was checked
				header('Content-type: application/json');
				echo json_encode(array("years"=>"No years available","type"=>"ERROR"));
			}else { // this blade does not apeare in the file.											
				header('Content-type: application/json');
				echo json_encode(array("years"=>"Blade not defined","type"=>"ERROR"));
			}
		} 
	}
    
  
    // acording to the blade and year selected , retrives the available weeks
    if($_POST["func"]=="getWeeks")
	{
		$blade = $_POST["blade"];
		$year = $_POST["year"];
		
		// searching the info in weeks.xml
		$xml = simplexml_load_file("xml/weeks.xml");
		$result = $xml->xpath('/DATA/blade[@name="'.$blade.'"]/date[@year="'.$year.'"]/week');					
		if($result!=FALSE)
		{
			foreach($result as $index=>$object){
				$weeks[] = (string)$result[$index];					
			}	
			header('Content-type: application/json');
    		echo json_encode(array("weeks"=>$weeks,"type"=>"GOOD"));							
		}
		else{ 
			header('Content-type: application/json');
			echo json_encode(array("weeks"=>"No weeks available","type"=>"ERROR"));			
		}   	
	}
	
	// according to the blade,year and week finds the available tables (allways from DB).		
	if($_POST["func"]=="showTables")
	{
		$blade = $_POST["blade"];		 
		$mysqli = new DBConnection($host,$user,$pass,$database,$port,5);
		if ($mysqli->connect_error) {
 		   ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error,"ERROR");
		}	
		
		$year = $_POST["year"];
		$week = $_POST["week"];
		
		$table = $DataTables["ip-edges"]["prefix"];        
		$edges = getTblFromDB($mysqli,$table,$year,$week);
		
		$table = $DataTables["pop-locations"]["prefix"];
		$pops = getTblFromDB($mysqli,$table,$year,$week);
		
		$table = $DataTables["popip"]["prefix"];
		$popsIP = getTblFromDB($mysqli,$table,$year,$week);
		
		$mysqli->close();
		header('Content-type: application/json');
        echo json_encode(array("edge"=>$edges,"pop"=>$pops,"popIP"=>$popsIP,"type"=>"GOOD"));                                    		
	}
	
	// according to te tables chosen - fetching the ASN list.
	if($_POST["func"]=="getASlist")
	{		
		$blade = $_POST["blade"];
		$mysqli = new DBConnection($host,$user,$pass,$database,$port,5);
		if ($mysqli->connect_error) {
 		   ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error,"ERROR");
		}
        
		$edgeTbl = $_POST["edge"];
		$popTbl = $_POST["pop"];
		
		$query ="SELECT distinct ASN FROM `".$database."`.`".$popTbl."` order by ASN" ;		
		
		$AS = "";
		$ASinfo = simplexml_load_file("xml/ASN_info.xml");
		
		if ($result = $mysqli->query($query)){			
	    	 while ($row = $result->fetch_assoc()) {
		        foreach($row as $key => $value){
					$AS .= $value . " ";	
					// fetching additional info from ASN_info.xml										 									
					$res = $ASinfo->xpath('/DATA/ROW[ASNumber="'.$value.'"]');		
					if($res!=FALSE)
					{
						$AS.=$res[0]->Country." ".$res[0]->ISPName;
					}
					$AS.= "*";							  
				}
		     }
			 $result->close();
    	}       
			   			   
		header('Content-type: application/text');        
        echo json_encode(array("result"=>$AS,"type"=>"GOOD"));                                    
		$mysqli->close();
	}

	if($_REQUEST["func"]=="pq-status")
	{
		$cmd_str = "process_queries.php";
		$cmd = new Backgrounder($cmd_str,'process_queries');
		//$lrt = $cmd->getLastRunTime();
		$ok_sig = file_exists('shell/log/process_queries.ok');
		if($cmd->isRunning()) {
			
			$log_file = 'shell/log/queries.log';
			if(file_exists($log_file)){
				$data = file($log_file);
				$data_arr = array();
				foreach($data as $line){
					list($QID,$status) = explode(' ', $line);
					$data_arr[$QID] = $status;
				}
				ret_res(json_encode($data_arr),"RUNNING_STATUS");
			} else {
				ret_res("pq script still running","RUNNING");
			}
			
			/*
			$data_arr = array();
			$queryXML = simplexml_load_file("xml/query.xml");
			$result = $queryXML->xpath('/DATA/QUERY[lastKnownStatus="running"]');
			foreach($result as $query){
				$QID = (string)$query->queryID;
				$status = (string)$query->lastRunningState;
				$data_arr[$QID] = $status; 
			}
			ret_res(json_encode($data_arr),"RUNNING_STATUS");
			 */
			
		}
		if($ok_sig) {
			ret_res("pq script finished","FINISHED");
		}
		$lines = $cmd->getLastLogLines(intval($_POST["log_lines"]));
		ret_res($lines,"ERROR");
	}

	if($_REQUEST["func"]=="pq-check")
	{
		$queries = simplexml_load_file("xml/query.xml");
		$result = $queries->xpath('/DATA/QUERY[lastKnownStatus="running"]');
		if(empty($result)){
			ret_res("no running queries","EMPTY");
		}
		ret_res("there is something to do!","NOT-EMPTY");
	}

	if($_REQUEST["func"]=="processQueries")
	{
		$time_interval = processQueriesTimeInterval; // minutes
		
	 	$cmd_str = "process_queries.php";
		$cmd = new Backgrounder($cmd_str,'process_queries');
		$lrt = $cmd->getLastRunTime();
		$ok_sig = file_exists('shell/log/process_queries.ok'); 
		if(!$ok_sig || $lrt == -1 || ($lrt/60) >= $time_interval)
		{
			$cmd->run();
			if(!$cmd->isRunning()) {
				ret_res("failed to launch background processing job!","ERROR");
			}
			header('Content-type: application/json');        
        	echo json_encode(array("result"=>"background processing job is now running","pid"=>$cmd->getPID(),"type"=>"GOOD"));
			die(); 
		}
		ret_res("last invokation is still valid, time interval is set to ".$time_interval." minutes","GOOD");
	}
	
	/*
	if($_POST["func"]=="resendQuery")
	{	     		
		$username = $_POST["username"];
		$stage = intval($_POST["stage"]);			
		
		$pop = (string)$result->PopTbl;
		$popIP =(string)$result->PopLocTbl;
		$edge = (string)$result->EdgeTbl;
		$as = (string)$result->allAS;
		$idg = new idGen($queryID);
		$tableID = $idg->getTableID();
		
		if($stage==2)
		{
			$cmd = "send_query.php --host=".$host." --user=".$user." --pass=".$pass." --database=".$database."  --writedb=".$write_db." --port=".$port." --PoPTblName=".$idg->getPoPTblName()." --pop=".$pop." --as=".$as." --EdgeTblName=".$idg->getEdgeTblName()." --edge=".$edge." --popIP=".$popIP." --query=";
			$cmd1 = new Backgrounder($cmd."1",'query1',$queryID);
			$cmd1->run();
			$cmd2 = new Backgrounder($cmd."2",'query2',$queryID);
			$cmd2->run();
			ret_res("stage2 complete","STAGE2_COMPLETE");
		}
		
		// check if present in 'SHOW PROCESSLIST' and/or table exist
		if($stage==3)
		{
			$qm =  new QueryManager($selected_blade);
			
			try {
				$query_status = $qm->getQueryStatus($queryID,$tableID);
			} catch(DBConnectionError $e){
				ret_res("can't connect to db!","ERROR");
			}
			
			if($query_status == 0){
				ret_res("sql query failed to execute properly...</BR>","ERROR");
			} else {
				$qm->setQueryStatus($queryID, 'running');
				header('Content-type: application/json');
				echo json_encode(array("result"=>"query is now running..." ,"type"=>"GOOD","queryID"=>$queryID));
				die();
			}
		}						
		
	}
	*/
	
	if($_POST["func"]=="sendQuery")
	{       		
		$username = $_POST["username"];
		$stage = intval($_POST["stage"]);
		$blade = $selected_blade;			
		
		if(isset($_POST["resend"])){	
			$pop = (string)$result->PopTbl;
			$popIP =(string)$result->PopLocTbl;
			$edge = (string)$result->EdgeTbl;
			$as = (string)$result->allAS;
			$idg = new idGen($queryID);
			$tableID = $idg->getTableID();
			
		} else {
			$pop = $_POST["pop"];
			$popIP = $_POST["popIP"];
			$edge = $_POST["edge"];
			$as = $_POST["as"];
			$year = $_POST["year"];
			$week = $_POST["week"];
			$idg = new idGen($edge,$pop,$as,$popIP,$blade,$year,$week);
			$queryID = $idg->getqueryID();
			$tableID = $idg->getTableID();
				
			$asp = $_POST["as"];
			if(!is_numeric(end($asp))) array_pop($asp);			
			$as = "'";
			$as .= implode("','", $asp);						
			$as .= "'";
		}
		
		$qm = new QueryManager($blade);
		
		if($stage==1)
		{
			$queries = simplexml_load_file('xml/query.xml');							
			$result = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/users');
					
			if($result!=FALSE) // this query already exists
			{
				/*
				foreach($result as $rs){
					if($username==(string)$rs->user)
						ret_res("query already exists!","ALL_COMPLETE");
				}
				 * 
				 */
				if($result[0]->user!=$username){
					$result[0]->addChild('user', $username);
					$queries->asXML('xml/query.xml');
					ret_res("query already assigned to a different user,adding current user to record","GOOD");
				} else {
					ret_res("query already exists!","ALL_COMPLETE");
				}
			}
			
			AddQuery($queryID,$tableID,$year,$week,$username,$edge,$pop,$popIP,count($asp),$as,$blade);
			$qm->setQueryStatus($queryID,'unknown');
			
			$result = $queries->xpath('/DATA/QUERY[tableID="'.$tableID.'"]');
			if(!empty($result)){ // a query with the same tableID exists
				$qm->setQueryStatus($queryID,'running');
				$max_stateID = -1; // -1 == init state
				foreach($result as $query){
					//$curr_status = (string)$query->lastKnownStatus;
			    	$curr_running_status = (string)$query->lastRunningState;
					$curr_stateID = $qm->getStateIDFromRunningStatus($curr_running_status);
					$max_stateID = ($max_stateID < $curr_stateID) ? $curr_stateID : $max_stateID;
				}
				if($max_stateID!=0){ // 0 == 'error' state...
					//AddQuery($queryID,$tableID,$year,$week,$username,$edge,$pop,$popIP,count($asp),$as,$blade);
					if($max_stateID>=2){ // tables are ready!
						$qm->setQueryRunningStatus($queryID,2);
					}
					header('Content-type: application/json');
					echo json_encode(array("result"=>"requested table already exsists..." ,"type"=>"GOOD","queryID"=>$queryID));
					die();
				}
			}
			ret_res("stage1 complete","STAGE1_COMPLETE");
		}
		
		// execute query on mysql server
		if($stage==2)
		{
			$cmd = "send_query.php --host=".$host." --user=".$user." --pass=".$pass." --database=".$database."  --writedb=".$write_db." --port=".$port." --PoPTblName=".$idg->getPoPTblName()." --pop=".$pop." --as=".$as." --EdgeTblName=".$idg->getEdgeTblName()." --edge=".$edge." --popIP=".$popIP." --query=";
			$cmd1 = new Backgrounder($cmd."1",'query1',$queryID);
			$cmd1->run();
			$cmd2 = new Backgrounder($cmd."2",'query2',$queryID);
			$cmd2->run();
			ret_res("stage2 complete","STAGE2_COMPLETE");
		}
		
		// check if present in 'SHOW PROCESSLIST' and/or table exist, if so add to query.xml 
		if($stage==3)
		{
			try {
				$query_status = $qm->getQueryStatus($queryID,$tableID);
			} catch(DBConnectionError $e){
				ret_res("can't connect to db!","ERROR");
			}
			
			if($query_status == 0){
				ret_res("sql query failed to execute properly...</BR>","ERROR");
			} else {
				//AddQuery($queryID,$tableID,$year,$week,$username,$edge,$pop,$popIP,count($asp),$as,$blade);
				$qm->setQueryStatus($queryID,'running');
				header('Content-type: application/json');
				echo json_encode(array("result"=>"query is now running..." ,"type"=>"GOOD","queryID"=>$queryID));
			}
		}	
	}
	
?>
