<?php
	require_once("bin/load_config.php");
	require_once("bin/idgen.php");
	require_once("bin/query_status.php");
	require_once("bin/backgrounder.php");
	require_once("bin/DBConnection.php");
	include_once("verify.php");	
	
	if(!isset($_REQUEST["query"]) || !isset($_REQUEST["func"]))
		ret_res('missing parameters!','ERROR');
	
	
	// globals
	$queryID = $_REQUEST["query"];
	if(isset($_REQUEST["username"])){
		$username = $_REQUEST["username"];
	}
		
	$queries = simplexml_load_file("xml/query.xml");
	$res = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/blade');
	$selected_blade = (string)$res[0];
	$blade = $Blade_Map[$selected_blade];
	$host = (string)$blade["host"];
	$port = (int)$blade["port"];
	$hostNport = (string)$blade["host"].":".(string)$blade["port"];
	$user = (string)$blade["user"];
	$pass = is_array($blade["pass"])?"":(string)$blade["pass"];
	$database = (string)$blade["db"];
	$write_db = (string)$blade["write-db"];
	
	$idg = new idGen($queryID);
	$popTbl = $idg->getPoPTblName();
	$edgeTbl = $idg->getEdgeTblName();
	
	function ret_res($message, $type)
	{
		header('Content-type: application/json');
		echo json_encode(array("result"=>$message ,"type"=>$type));
		die();	
	}
	
	function deleteUser($username,$queryID){
		
		$queries = simplexml_load_file("xml/query.xml");
		$res = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/users/user');
		
		foreach ($res as $key => $user){
			if ($user == $username){			  
				$theNodeToBeDeleted = $res[$key];								
				$oNode = dom_import_simplexml($theNodeToBeDeleted);				
				if (!$oNode) {
				    echo 'Error while converting SimpleXMLelement to DOM';
				}		
				$oNode->parentNode->removeChild($oNode); 				
			}
		}		
		$queries->asXML("xml/query.xml");			
	}
	
	function deleteQuery($queryID){
		
		$queries = simplexml_load_file("xml/query.xml");
		
		$res = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');							
		$oNode = dom_import_simplexml($res[0]);				
		if (!$oNode) {
		    echo 'Error while converting SimpleXMLelement to DOM';
		}		
		$oNode->parentNode->removeChild($oNode); 						
		$queries->asXML("xml/query.xml");
		
	}
	
	 function rrmdir($dir) { 
	   if (is_dir($dir)) { 
	     $objects = scandir($dir); 
	     foreach ($objects as $object) { 
	       if ($object != "." && $object != "..") { 
	         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
	       } 
	     } 
	     reset($objects); 
	     rmdir($dir); 
	   } 
	 }
	
	/*
	if($_REQUEST["func"]=="getRunningStatus")
	{
		// Turn off all error reporting
		error_reporting(E_ERROR);
		
		$queries = simplexml_load_file("xml/query.xml");									
		$result = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');		
		
		if($result==FALSE){ // the query is not found in the queries file
			ret_res('Assertion Error - query is not found in query list','ERROR');			
		}
		

		if ($result[0]->lastKnownStatus!="running"){
			ret_res('Assertion Error - this method should only be called for running processes','ERROR');
		}

	
		$qm = new QueryManager($selected_blade);
		$query_status = $qm->getQueryStatus($queryID);
		$query_status_msg = $qm->getStatusMsg($query_status);
		
		if($query_status==0){
			ret_res("query is not running and table doesnt exsist or is locked",$query_status_msg);
		}
		 
		if($query_status==1){
			ret_res('queries are still running..',$query_status_msg);
		}
		
		if($query_status==2){
			ret_res("ready to fetch data from db",$query_status_msg);
		}
		
		if($query_status==3 || $query_status == 4){
			ret_res("preparing xml files..",$query_status_msg);
		}
		
		if($query_status==5){
			ret_res('kml file is ready',$query_status_msg);
		}
		 
		ret_res("assertion error - ambiguous status","ERROR");
	}
*/
		
	if($_REQUEST["func"]=="abort")
	{
		
		$queries = simplexml_load_file("xml/query.xml");									
		$result = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');		
		
		if(!$result || empty($result)) // the query is not in the queries file.
		{
			ret_res("The query doesnt exists","ERROR");
		} else {
			$tableID = (string)$result[0]->tableID;
			$lastKnownStatus = (string)$result[0]->lastKnownStatus;
			$allUsers = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/users/user');
			$allQIDs = $queries->xpath('/DATA/QUERY[tableID="'.$tableID.'"]');
			if (count($allUsers)>1){					
				deleteUser($username,$queryID);				
			} else { // only one user has this query in his query list...
			
				if($lastKnownStatus!="runnning"){ // query status is either 'complete' or 'error'
					
					// Remove the query folder & files
					$dir = getcwd()."/queries/".$queryID; 
					if (is_dir($dir)){
						rrmdir($dir);	
					}
					
					// Erase the process from query.XML 
					deleteQuery($queryID);
					
				} else { // query is running
					//$qm = new QueryManager($selected_blade);
					$qm = QueryManager::load($selected_blade);
					if($qm==null)
						ret_res("can't connect to db!","ERROR");
					
					$query_status = $qm->getQueryStatus($queryID);
					
					if($query_status==1){ // query is still running on the DB
						
						if (count($allQIDs)>1){ // there are more queries relying on current tableID
							deleteQuery($queryID); // remove this query only...
						} else {	// Kill the process that is ccreating the tables on the DB-side
							$mysqli = new DBConnection($host,$user,$pass,$database,$port,5);
							if($mysqli->connect_error) {
								ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error,"ERROR");
							}
							
							foreach($qm->getPIDS($queryID) as $pid){
								$sql = 'kill '.$pid;
								$res = $mysqli->query($sql);
							}
							
							$mysqli->select_db($write_db);
							$sql = 'drop table if exists '.$popTbl;						
							$res = $mysqli->query($sql);
							$sql = 'drop table if exists '.$edgeTbl;						
							$res = $mysqli->query($sql);
							$mysqli->close();
							
							// Erase the process from query.XML 
							deleteQuery($queryID);
						}					
					} elseif($query_status>=3) { // files & folders have been created for this query..we need to delete them
					
						if($query_status==3){ // query is in fetching-xml stage..we also need to kill `fetching-xml-thread`
							// TODO: kill fetching xml thread!
						}
						
						// Remove the query folder & files
						$dir = getcwd()."/queries/".$queryID; 
						if (is_dir($dir)){
							rrmdir($dir);	
						}
						
						// Erase the process from query.XML 
						deleteQuery($queryID);
	
					}
				} 
			}

			ret_res("","GOOD");	
		}
	}

?>