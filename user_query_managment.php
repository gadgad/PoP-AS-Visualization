<?php
	include_once("bin/load_config.php");	
	
	// globals
	$queryID = $_POST["query"];
	$username = $_POST["username"];
		
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
		
	if($_POST["func"]=="abort")
	{
		
		$queries = simplexml_load_file("xml\query.xml");									
		$result = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');		
		
		if($result!=FALSE) // the query is found in the queries file - good.
		{
					
			if ($result[0]->lastKnownStatus=="running"){
					
				$allUsers = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/users/user');
				if (count($allUsers)>1){					
					deleteUser($username,$queryID);					
				}else{
					// Kill the process
					$mysqli = new mysqli($host,$user,$pass,$database,$port);
					if ($mysqli->connect_error) {
			 		   ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error,"ERROR");
					}
					$PID = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/processID');
					$sql = 'kill '.$PID[0];						
					$res = $mysqli->query($sql);
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
			
		}else { echo "ERROR - this query doesnt exists"; } //this line should never be reached		 
	}

?>