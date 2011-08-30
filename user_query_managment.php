<?php
	require_once("bin/load_config.php");
	require_once("bin/idgen.php");
	require_once("bin/query_status.php");	
	
	if(!isset($_REQUEST["query"]) || !isset($_REQUEST["func"]))
		ret_res('missing parameters!','ERROR');
	
	
	// globals
	$queryID = $_REQUEST["query"];
	if(isset($_REQUEST["username"])){
		$username = $_REQUEST["username"];
	} else {
		$username = isset($_COOKIE['username'])? $_COOKIE['username'] : $_SESSION['username'];
	}
		
	$queries = simplexml_load_file("xml\query.xml");
	$res = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/blade');
	$selected_blade = (string)$res[0];
	$blade = $Blade_Map[$selected_blade];
	$host = (string)$blade["host"];
	$port = (int)$blade["port"];
	$hostNport = (string)$blade["host"].":".(string)$blade["port"];
	$user = (string)$blade["user"];
	$pass = is_array($blade["pass"])?"":(string)$blade["pass"];
	$database = (string)$blade["db"];
	$write_db = $blade["write-db"];
	
	
	function ret_res($message, $type)
	{
		header('Content-type: application/json');
		echo json_encode(array("result"=>$message ,"type"=>$type));
		die();	
	}
	
	function deleteUser($username,$queryID){
		
		$queries = simplexml_load_file("xml\query.xml");
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
		$queries->asXML("xml\query.xml");			
	}
	
	function deleteQuery($queryID){
		
		$queries = simplexml_load_file("xml\query.xml");
		
		$res = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');							
		$oNode = dom_import_simplexml($res[0]);				
		if (!$oNode) {
		    echo 'Error while converting SimpleXMLelement to DOM';
		}		
		$oNode->parentNode->removeChild($oNode); 						
		$queries->asXML("xml\query.xml");
		
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
	  
	
	if($_REQUEST["func"]=="getRunningStatus")
	{
		// Turn off all error reporting
		error_reporting(0);
		
		$queries = simplexml_load_file("xml\query.xml");									
		$result = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');		
		
		if($result==FALSE){ // the query is not found in the queries file
			ret_res('Assertion Error - query is not found in query list','ERROR');			
		}
		
		/*
		if ($result[0]->lastKnownStatus!="running"){
			ret_res('Assertion Error - this method should only be called for running processes','ERROR');
		}
		 * 
		 */

		$idg = new idGen($queryID);
		$kml_dst_dir = 'queries/'.$idg->getDirName();
		$kml_filename = $kml_dst_dir.'/result.kmz';
		$edges_filename = $kml_dst_dir.'/edges.xml';
		$pop_filename = $kml_dst_dir.'/pop.xml';
		
		if(file_exists($kml_filename)){
			ret_res('kml file is ready','COMPLETE');
		}
		
		if(file_exists($edges_filename) || file_exists($pop_filename)){
			ret_res('fetchnig data into xml files and/or rendering kml','FETCHING-DATA');
		}
	
		// xml/kml files are not present...cheking status on mysql server
		// 0 - error , 1 - running , 2 - tables ready
		$query_status = getQueryStatus($queryID,$selected_blade);
		
		if($query_status==0){
			ret_res("query is not running and table doesnt exsist or is locked","ERROR");
		}
		 
		if($query_status==1){
			ret_res('queries are still running..','RUNNING');
		}
		
		if($query_status==2){
			ret_res("ready to fetch data from db","READY");
		}
		 
		ret_res("assertion error - ambiguous status","ERROR");
	}
		
	if($_REQUEST["func"]=="abort")
	{
		
		$queries = simplexml_load_file("xml\query.xml");									
		$result = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');		
		
		if($result!=FALSE) // the query is found in the queries file - good.
		{
					
			if ($result[0]->lastKnownStatus=="running"){
				
					
				$allUsers = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/users/user');
				if (count($allUsers)>1){					
					deleteUser($username,$queryID);					
				}else if(getQueryStatus($queryID,$selected_blade)==1){
					
					// Kill the process
					$mysqli = new mysqli($host,$user,$pass,$database,$port);
					while($mysqli->connect_error) {
						sleep(3);
						$mysqli = new mysqli($host,$user,$pass,$database,$port);
					}
					
					foreach($GLOBALS["PID_MAP"][$queryID] as $pid){
						$sql = 'kill '.$pid;
						$res = $mysqli->query($sql);
					}
					
					//$PID = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/processID');
					//$sql = 'kill '.$PID[0];						
					//$res = $mysqli->query($sql);
					$mysqli->select_db($write_db);
					
					$sql = 'drop table if exists DPV_EDGE_'.$queryID;						
					$res = $mysqli->query($sql);
					$sql = 'drop table if exists DPV_POP_'.$queryID;						
					$res = $mysqli->query($sql);
					$mysqli->close();
					
					// Erase the process from query.XML 
					deleteQuery($queryID);
					
					// Remove the query folder
					$dir = getcwd()."/queries/".$queryID; 
					if (is_dir($dir)){
						rrmdir($dir);	
					}						
						
				}
												
			}else {												
				deleteUser($username,$queryID);
			}
			
		ret_res("","GOOD");
			
		}else { ret_res("The query doesnt exists","ERROR");} //this line should never be reached		 
	}

?>