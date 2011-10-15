<?php 
 
function AddQuery($queryID,$tableID,$year,$week,$usertoadd,$EdgeTbl,$PopTbl,$PopLocTbl,$ASnum,$allAS,$blade) 
{
	 $nameXML = "xml/query.xml";	 
	// Load XML file
	 $xml = simplexml_load_file($nameXML);
	
	 $query = $xml->addChild('QUERY');
	 $query->addChild('queryID', $queryID);
	 $query->addChild('tableID', $tableID);
	 $query->addChild('year', $year);
	 $query->addChild('week', $week);
	 $query->addChild('lastKnownStatus',"running");
	 $query->addChild('lastRunningState',"started");
	 $query->addChild('blade', $blade);
	 $query->addChild('EdgeTbl', $EdgeTbl);
	 $query->addChild('PopTbl', $PopTbl);
	 $query->addChild('PopLocTbl', $PopLocTbl);
	 $query->addChild('ASnum', $ASnum);
	 $query->addChild('allAS', $allAS);
	 $users = $query->addChild('users');
	 $users->addChild('user', $usertoadd);
	 
	 //$xml->asXML($nameXML);
	 
	 $dom = new DOMDocument('1.0');
	 $dom->preserveWhiteSpace = false;
	 $dom->formatOutput = true;
	 $dom->loadXML($xml->asXML());
	 $dom->save($nameXML);

} 

?>