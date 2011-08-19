<?php
	include_once("bin/load_config.php");
	include_once("bin/idgen.php");
	include_once("writeToXML.php");	
	
	// globals
	$selected_blade = $_POST["blade"];
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
	
	
	function deleteUser($queries,$username,$queryID){
		//unset($queries->xpath('/DATA/QUERY/users/user[user="'.$username.'"]'));
		//unset($result[0]->xpath('/QUERY/users/user'));
		//unset($queries->DATA->QUERY->users->user[$username]);
		
		
		$res = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/users/user');
		foreach ($res as $key => $name){
			if ($name == $username){
				$theNodeToBeDeleted = $res[$key];
				var_dump($theNodeToBeDeleted);
				//list($theNodeToBeDeleted) = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/users/user["'.$username.'"]');
				$oNode = dom_import_simplexml($theNodeToBeDeleted);
				if (!$oNode) {
				    echo 'Error while converting XML';
				}
				var_dump($oNode);
				$oNode->parentNode->removeChild($oNode); 				
			}
		}		
		$queries->asXML();		
	}
		
	if($_POST["func"]=="abort")
	{
		$queryID = $_POST["query"];
		$username = $_POST["username"];
		
		$queries = simplexml_load_file("xml\query.xml");									
		$result = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');		
		
		if($result!=FALSE) // the query is found in the queries file
		{
					
			if ($result[0]->lastKnownStatus=="running"){
					
				$allUsers = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/users/user');
				$numOfUsers = 0;
				foreach ($allUsers as $i => $value) {												
					$numOfUsers++;							
				}
				
				if ($numOfUsers>1){					
					deleteUser($queries,$username,$queryID);					
				}else{
						$mysqli = new mysqli($host,$user,$pass,$database,$port);
						$PID = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/processID');
						$sql = 'kill '.$PID[0];
						if ($mysqli->connect_error) {
				 		   ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error,"ERROR");
						}
						$res = $mysqli->query($sql);
						$mysqli->close();
						// TODO: stop the query - kill the process and erase it from XML	
					}								
			}else {												
				deleteUser($queries,$username,$queryID);
			}
			$queries->asXML();
			
		}else { echo "ERROR - this query doesnt exists"; }		 
	}
?>