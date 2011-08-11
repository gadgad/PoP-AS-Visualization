<?php 
 
function AddQuery($queryID,$processID,$usertoadd,$EdgeTbl, $PopTbl) 
{
	 $nameXML = "xml/query.xml";	 
	// Load XML file
	 $xml = simplexml_load_file($nameXML);
	
	 $query = $xml->addChild('QUERY');
	 $query->addChild('queryID', $queryID);
	 $query->addChild('processID', $processID);
	 $query->addChild('lastKnownStatus',"running");
	 $query->addChild('EdgeTbl', $EdgeTbl);
	 $query->addChild('PopTbl', $PopTbl);
	 $users = $query->addChild('users');
	 $users->addChild('user', $usertoadd);
	 
	 $xml->asXML($nameXML);

} 

?>