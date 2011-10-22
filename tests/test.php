<?php
	//require_once 'bin/load_config.php';
	//echo var_export($arr)."\n";
	//echo $arr["ge-api-keys"];
	//echo fetchXMLRecordsPagingBufferSize;
	
	/*
	$str = "SELECT SourceAS,DestAS,Source_PoPID,Dest_PoPID,count(edgeid) as NumOfEdges FROM bla bla";
	
	//$pattern = '/(\w+) (\d+), (\d+)/i';
	$pattern = '/(^select) (.*) (from)(.*)/i';
	$replacement = '$1 COUNT(*) $3$4';
	echo preg_replace($pattern, $replacement, $str);
	*/
	
	/*
	$stamp = array(-1=>"started", 0=>"error", // db-error
									  1=>"running", // db-running
									  2=>"db-ready",
									  3=>"fetching-xml",
									  4=>"xml-ready",
									  5=>"kml-ready");
									  
	$stateID = array_search('started', $stamp);
	echo $stateID;
	*/
	
	echo var_dump($_SERVER);
?>