<?php 
 
function AddQuery($queryID,$processID,$usertoadd,$EdgeTbl, $PopTbl,$ASnum,$allAS,$blade) 
{
	 $nameXML = "xml/query.xml";	 
	// Load XML file
	 $xml = simplexml_load_file($nameXML);
	
	 $query = $xml->addChild('QUERY');
	 $query->addChild('queryID', $queryID);
	 $query->addChild('processID', $processID);
	 $query->addChild('lastKnownStatus',"running");
	 $query->addChild('blade', $blade);
	 $query->addChild('EdgeTbl', $EdgeTbl);
	 $query->addChild('PopTbl', $PopTbl);
	 $query->addChild('ASnum', $ASnum);
	 $query->addChild('allAS', $allAS);
	 $users = $query->addChild('users');
	 $users->addChild('user', $usertoadd);
	 
	 $xml->asXML($nameXML);

} 

?>