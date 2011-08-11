<?php 
 
function AddQuery($queryID,$processID,$usertoadd,$EdgeTbl, $PopTbl) 
{
	$nameXML = "xml/query.xml";	 
	// Load XML file
	$xml = simplexml_load_file($nameXML);
	echo "loading xml file";
	 
	// Find all instances of <album>
	 $seg = $xml->DATA;
	 //foreach($xml->DATA as $seg)
	 {
	 		echo "trying to add query";
		// Find album title you want to add to
		 // if($seg['title'] == $fgallery) 
		  {
		 
		// Create QUERY element
		   //$QUERY = $seg->addChild('QUERY');
		 
		// Add attributes to element
		   $QUERY->addAttribute('queryID', $queryID);
		   $QUERY->addAttribute('processID', $processID);
		   $QUERY->addAttribute('usertoadd', $usertoadd);
		   $QUERY->addAttribute('EdgeTbl', $EdgeTbl);
		   $QUERY->addAttribute('PopTbl', $PopTbl);
		 
		// Save XML file
		   $xml->asXML($nameXML);
		}
	}
} 
 
AddQuery("1111", "2222", "keren", "edge1", "pop1");
 

?>