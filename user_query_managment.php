<?php

/*
 *  this file handles a query deletion by the user
 *  if there is only one user that generated this query - it is deleted. 
 * 	if there several users that view this query - only the username is being removed from the users list.
 */
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
	
	// setting connection variables		
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
	
	// deleting the user from the users list
	function deleteUser($username,$queryID){
		
		$queries = simplexml_load_file("xml/query.xml");
		$res = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/users/user');
		
		foreach ($res as $key => $user){
			if ($user == $username){			  
				$theNodeToBeDeleted = $res[$key];								
				$oNode = dom_import_simplexml($theNodeToBeDeleted);				
				if (!$oNode) {
				    //echo 'Error while converting SimpleXMLelement to DOM';
				    return false;
				}		
				$oNode->parentNode->removeChild($oNode); 				
			}
		}		
		$queries->asXML("xml/query.xml");
		
		return true;			
	}
	
	// deleting the query from query.xml
	function deleteQuery($queryID){
		
		$queries = simplexml_load_file("xml/query.xml");
		
		$res = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');							
		$oNode = dom_import_simplexml($res[0]);				
		if (!$oNode) {
		    //echo 'Error while converting SimpleXMLelement to DOM';
		    return false;
		}		
		$oNode->parentNode->removeChild($oNode); 						
		$queries->asXML("xml/query.xml");
		
		return true;
		
	}
	
	// a function that recursivly removes a directory
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
	
	// handling a query removal	
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
				if(!deleteUser($username,$queryID)){
					ret_res("error deleting user...","ERROR");
				}				
			} else { // only one user has this query in his query list...
			
				if($lastKnownStatus!="running"){ // query status is either 'complete' or 'error'
					
					// Remove the query folder & files
					$dir = getcwd()."/queries/".$queryID; 
					if (is_dir($dir)){
						rrmdir($dir);	
					}
					
					// Erase the process from query.XML 
					if(!deleteQuery($queryID))
						ret_res("error deleting query","ERROR");
					
				} else { // query is running
					$qm = new QueryManager($selected_blade);
					
					try {
						$query_status = $qm->getQueryStatus($queryID);
					} catch(DBConnectionError $e){
						ret_res("can't connect to db!","ERROR");
					}
					
					if($query_status==1){ // query is still running on the DB
						
						if (count($allQIDs)>1){ // there are more queries relying on current tableID
							// remove this query only...
							if(!deleteQuery($queryID)) 
								ret_res("error deleting query","ERROR"); 
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
							if(!deleteQuery($queryID))
								ret_res("error deleting query","ERROR");
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
						if(!deleteQuery($queryID))
								ret_res("error deleting query","ERROR");
	
					}
				} 
			}

			ret_res("abort func finished successfully","GOOD");	
		}
	}

?>