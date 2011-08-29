<?php 
 
function AddQuery($queryID,$year,$week,$usertoadd,$EdgeTbl,$PopTbl,$PopLocTbl,$ASnum,$allAS,$blade) 
{
	 $nameXML = "xml/query.xml";	 
	// Load XML file
	 $xml = simplexml_load_file($nameXML);
	
	 $query = $xml->addChild('QUERY');
	 $query->addChild('queryID', $queryID);
	 $query->addChild('year', $year);
	 $query->addChild('week', $week);
	 $query->addChild('lastKnownStatus',"running");
	 $query->addChild('blade', $blade);
	 $query->addChild('EdgeTbl', $EdgeTbl);
	 $query->addChild('PopTbl', $PopTbl);
	 $query->addChild('PopLocTbl', $PopLocTbl);
	 $query->addChild('ASnum', $ASnum);
	 $query->addChild('allAS', $allAS);
	 $users = $query->addChild('users');
	 $users->addChild('user', $usertoadd);
	 
	 $xml->asXML($nameXML);

} 

?>