<?php
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
		$queries = simplexml_load_file('xml\query.xml');							
		$result = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');
		if(empty($result)){
			ret_res("query doesn't exists!","ERROR");
		}
		$result = $result[0];
		$selected_blade = (string)$result->blade;       		
	} else {
		$selected_blade = isset($_POST["blade"])? $_POST["blade"] : $GLOBALS['DEFAULT_BLADE'];	
	}
	
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
	
	function getTblFromDB($mysqli,$table,$year,$week){
					
		$query1 = "'".$table."\_".$year."\_week_".$week."'";		
		$query2 = "'".$table."\_".$year."\_week_".$week."\_%'";		
		$query3 = "'".$table."\_".$year."\_".$week."'";		
		$query4 = "'".$table."\_".$year."\_".$week."\_%'";					
		
		$query = "select TABLE_NAME from INFORMATION_SCHEMA.TABLES WHERE table_schema='DIMES_DISTANCES' and (table_name like ".$query1." or table_name like ".$query2." or table_name like ".$query3." or table_name like ".$query4.")";
		$res = parse($mysqli,$query);
		return $res;        
	}
	
	function xml_change_status($qid,$new_status)
	{
		$queryID = $qid;
		$filename = "xml\query.xml";
		$queries = simplexml_load_file($filename);
		$result = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');
		$tableID = (string)$result[0]->tableID;
		$result2 = $queries->xpath('/DATA/QUERY[tableID="'.$tableID.'"]');
		foreach($result2 as $rs){
			$rs->lastKnownStatus=$new_status;
		}
		$queries->asXML($filename);
	}
	
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
    
    if($_POST["func"]=="getWeeks")
	{
		$blade = $_POST["blade"];
		$year = $_POST["year"];
		
		$weeks[] = array();
		$xml = simplexml_load_file("xml\weeks.xml");
		$result = $xml->xpath('/DATA/YEAR[year="'.$year.'"]/WEEK');					
		if($result!=FALSE)
		{
			foreach($result as $index=>$object){
				$weeks[] = (string)$result[$index];					
			}								
		}
		else{
			$mysqli = new DBConnection($host,$user,$pass,$database,$port,5);
			if ($mysqli->connect_error) {
	 		   ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error,"ERROR");
			}
			
			for($i=1;$i<53;$i++){
				
				$table = $DataTables["ip-edges"]["prefix"];        
				$edges = getTblFromDB($mysqli,$table,$year,$i);
				if ($edges!=""){
					$table = $DataTables["pop-locations"]["prefix"];
					$pops = getTblFromDB($mysqli,$table,$year,$i);
					if ($pops!=""){
						$table = $DataTables["popip"]["prefix"];
						$popsIP = getTblFromDB($mysqli,$table,$year,$i);
						if ($popsIP!=""){
							$weeks[] = $i;
						}
					}
				}				
			}
			$mysqli->close(); 			
		}   
		header('Content-type: application/json');
    	echo json_encode(array("weeks"=>$weeks,"type"=>"GOOD"));     
		
	}
			
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
		$ASinfo = simplexml_load_file("xml\ASN_info.xml");
		
		if ($result = $mysqli->query($query)){			
	    	 while ($row = $result->fetch_assoc()) {
		        foreach($row as $key => $value){
					$AS .= $value . " ";											 									
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
		$ok_sig = file_exists('shell/process_queries.ok');
		if($cmd->isRunning()) {
			ret_res("pq script still running","RUNNING");
		}
		if($ok_sig) {
			ret_res("pq script finished","FINISHED");
		}
		ret_res("update-status procedure ended unexpectedly","ERROR");
	}

	if($_REQUEST["func"]=="pq-check")
	{
		$queries = simplexml_load_file("xml\query.xml");
		$result = $queries->xpath('/DATA/QUERY[lastKnownStatus="running"]');
		if(empty($result)){
			ret_res("no running queries","EMPTY");
		}
		ret_res("there is something to do!","NOT-EMPTY");
	}

	if($_REQUEST["func"]=="processQueries")
	{
		// TODO: move this param to config file!
		$time_interval = 4; // hours
		
	 	$cmd_str = "process_queries.php";
		$cmd = new Backgrounder($cmd_str,'process_queries');
		$lrt = $cmd->getLastRunTime();
		$ok_sig = file_exists('shell/process_queries.ok'); 
		if(!$ok_sig || $lrt == -1 || ($lrt/3600) >= $time_interval)
		{
			$cmd->run();
			if(!$cmd->isRunning()) {
				ret_res("failed to launch background processing job!","ERROR");
			}
			header('Content-type: application/text');        
        	echo json_encode(array("result"=>"background processing job is now running","pid"=>$cmd->getPID(),"type"=>"GOOD"));
			die(); 
		}
		ret_res("last invokation is still valid, time interval is set to ".$time_interval." hours","GOOD");
	}
	
	if($_POST["func"]=="resendQuery")
	{
		/*
		$queryID = $_POST['query'];
		$queries = simplexml_load_file('xml\query.xml');							
		$result = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');
		if(empty($result)){
			ret_res("query doesn't exists!","ERROR");
		}
		$result = $result[0];	
		$blade = (string)$result->blade;
		*/
		     		
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
			$qm = new QueryManager($selected_blade);
			$query_status = $qm->getQueryStatus($queryID,$tableID);
			
			if($query_status == 0){
				ret_res("sql query failed to execute properly...</BR>","ERROR");
			} else {
				xml_change_status($queryID, 'running');
				header('Content-type: application/json');
				echo json_encode(array("result"=>"query is now running..." ,"type"=>"GOOD","queryID"=>$queryID));
				die();
			}
		}						
		
	}
	
	if($_POST["func"]=="sendQuery")
	{
		$blade = $_POST["blade"];       		
		$username = $_POST["username"];
		$stage = intval($_POST["stage"]);			
		
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
	
		if($stage==1)
		{
			$queries = simplexml_load_file('xml\query.xml');							
			$result = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/users');		
			if($result!=FALSE) // this query already exists
			{
				foreach($result as $rs){
					if($username==(string)$rs->user)
						ret_res("query already exists!","ALL_COMPLETE");
				}
				if($result[0]->user!=$username){
					$result[0]->addChild('user', $username);
					$queries->asXML('xml\query.xml');
					ret_res("query already assigned to a different user,adding current user to record","GOOD");
				} else {
					ret_res("query already exists!","ALL_COMPLETE");
				}
			}
			$result = $queries->xpath('/DATA/QUERY[tableID="'.$tableID.'"]');
			if(!empty($result)){
				$curr_status = (string)$result[0]->lastKnownStatus;
				AddQuery($queryID,$tableID,$year,$week,$username,$edge,$pop,$popIP,count($asp),$as,$blade);
				if($curr_status!='running') xml_change_status($queryID, $curr_status);
				header('Content-type: application/json');
				echo json_encode(array("result"=>"requested table already exsists..." ,"type"=>"GOOD","queryID"=>$queryID));
				die();
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
			$qm = new QueryManager($selected_blade);
			$query_status = $qm->getQueryStatus($queryID,$tableID);
			
			if($query_status == 0){
				ret_res("sql query failed to execute properly...</BR>","ERROR");
			} else {
				AddQuery($queryID,$tableID,$year,$week,$username,$edge,$pop,$popIP,count($asp),$as,$blade);
				header('Content-type: application/json');
				echo json_encode(array("result"=>"query is now running..." ,"type"=>"GOOD","queryID"=>$queryID));
			}
		}	
	}
	
?>
