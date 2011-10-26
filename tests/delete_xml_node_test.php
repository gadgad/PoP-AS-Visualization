<?php
$email = "bla@bla.com";
$invites = simplexml_load_file('xml/invited_users.xml');
$res = $invites->xpath('/DATA/email');
foreach ($res as $key => $value){
	if (strcmp($value,$email)==0){			  
		$theNodeToBeDeleted = $res[$key];								
		$oNode = dom_import_simplexml($theNodeToBeDeleted);				
		if (!$oNode) {
		    echo 'Error while converting SimpleXMLelement to DOM';
		}		
		$oNode->parentNode->removeChild($oNode); 				
	}
}					 							
$invites->asXML('xml/invited_users.xml');
?>