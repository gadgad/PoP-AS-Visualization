<?php

    require_once("bin/xml_writer.php");
    require_once("bin/kml_writer.php");
	require_once("bin/query_status.php");
    require_once("parsing_args.php");
	
    function myFlush (){
	    echo(str_repeat(' ',256));
	    // check that buffer is actually set before flushing
	    if (ob_get_length()){           
	        @ob_flush();
	        @flush();
	        @ob_end_flush();
	    }   
	    @ob_start();
	}
	
	function xml_change_status($qid,$new_status)
	{
		$queryID = $qid;
		$filename = "xml\query.xml";
		$queries = simplexml_load_file($filename);
		$result = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');
		$result[0]->lastKnownStatus=$new_status;
		$queries->asXML($filename);
		/*
		$xml = $queries->asXML();
		$filewrite = fopen($filename, "w");
		fwrite($filewrite, $xml);
		fclose($filewrite);
		 */
	}
	
	function create_ok_sig()
	{
		$ourFileName = "shell\process_queries.ok";
		$ourFileHandle = fopen($ourFileName, 'w') or die("can't open file");
		fclose($ourFileHandle);
	}
	
	function remove_ok_sig()
	{
		$filename = "shell\process_queries.ok";
		if(file_exists($filename))
			unlink($filename);
	}
	
	//$args = parseArgs($argv);
	//$foo = $args['foo'];
	//echo getcwd() . "\n";
	
	remove_ok_sig();
	$queries = simplexml_load_file("xml\query.xml");
	$result = $queries->xpath('/DATA/QUERY[lastKnownStatus="running"]');
	if(empty($result)){
		sleep(3);
		create_ok_sig();
		exit("nothing to do...\n");
	}
	
	$running_blade_map = array();
	foreach($result as $rq)
	{
		$blade = (string)$rq->blade;
		$running_blade_map[$blade][] = (string)$rq->queryID;	
	}
	
	$all_ok = true;
	foreach($running_blade_map as $blade => $rq_lst) {
		$qm = new QueryManager($blade);
		foreach($rq_lst as $queryID) {
			$qs = $qm->getQueryStatus($queryID);
			echo "$queryID status: ".$qm->getStatusMsg($qs)."\n";
			// 0 - error , 1 - running , 2 - db-ready, 3 - some-xml-ready,  4 - all-xml-ready, 5 - kml-ready
			if($qs >= 2 && $qs != 5) // db-tables are ready, but no kml file..
			{
				$xw = new xml_Writer($blade,$queryID);
			    if($xw->writeXML()){
			    	echo "$queryID generate-xml: success!\n";
			    }
				else {
					$all_ok = false;
					echo "$queryID generate-xml: failure :(\n";
				}
				
				//write generated KML file to disk
				$kmlWriter = new kmlWriter($queryID);
				if($kmlWriter->writeKMZ())
				{
					$filename=$kmlWriter->getFileName();
					xml_change_status($queryID,"completed");
					echo "$queryID generate-kml: success! $filename generated successfully! :)\n";
				} else {
					$all_ok = false;
					echo "$queryID generate-kml: failure :(\n";
				}
			} else if($qs==5) {
				xml_change_status($queryID,"completed");
			}	
		}
	}

	if($all_ok)
		create_ok_sig();


?>