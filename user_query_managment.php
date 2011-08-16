<?php
	include_once("bin/load_config.php");
	include_once("bin/idgen.php");
	include_once("writeToXML.php");	
	
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
						// TODO: stop the query - kill the process and erase it from XML	
					}								
			}else {												
				deleteUser($queries,$username,$queryID);
			}
			$queries->asXML();
			
		}else { echo "ERROR - this query doesnt exists"; }		 
	}
?>