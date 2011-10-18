<?php
	//error_reporting(E_ERROR);

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
	
	function create_ok_sig()
	{
		$ourFileName = "shell/log/process_queries.ok";
		$ourFileHandle = fopen($ourFileName, 'w') or die("can't open file");
		fclose($ourFileHandle);
	}
	
	function remove_ok_sig()
	{
		$filename = "shell/log/process_queries.ok";
		if(file_exists($filename))
			unlink($filename);
	}
	
	function log_query($QID,$status){
		$ourFileName = "shell/log/queries.log";
		$ourFileHandle = fopen($ourFileName, 'a') or die("can't open file");
		fwrite($ourFileHandle,$QID." ".$status."\n");
		fclose($ourFileHandle);
	}
	
	function clean_log(){
		$filename = "shell/log/process_queries.log";
		if(file_exists($filename))
			unlink($filename);
	}
	
	function clean_query_log(){
		$filename = "shell/log/queries.log";
		if(file_exists($filename))
			unlink($filename);
	}
	
	//$args = parseArgs($argv);
	//$foo = $args['foo'];
	//echo getcwd() . "\n";
	
	remove_ok_sig();
	clean_log();
	clean_query_log();
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
		//$qm = new QueryManager($blade);
		$qm = new QueryManager($blade);
		foreach($rq_lst as $queryID) {
			
			try {
				$qs = $qm->getQueryStatus($queryID);
			} catch(DBConnectionError $e){
				die("getQueryStatus: can't connect to db!\n");
			} 
			
			echo "$queryID status: ".$qm->getStatusMsg($qs)."\n";
			// 0 - error , 1 - running , 2 - db-ready, 3 - fetching-xml,  4 - xml-ready, 5 - kml-ready
			if($qs == 2 || $qs == 3) // db-tables are ready, but no xml file..
			{
				log_query($queryID,'db_ready');
				$qm->setQueryRunningStatus($queryID,3);
				$xw = new xml_Writer($blade,$queryID);
			    if($xw->writeXML()){
			    	echo "$queryID generate-xml: success!\n";
					log_query($queryID,'xml_done');
					$qm->setQueryRunningStatus($queryID,4);
					$qs=4;
			    } else {
					$all_ok = false;
					echo "$queryID generate-xml: failure :(\n";
					log_query($queryID,'xml_fail');
				}
			}
			if($qs==4){ // xml ready, but no kml file
				//write generated KML file to disk
				echo "calling kmlWriter CTOR\n";
				$kmlWriter = new kmlWriter($queryID);
				echo "after CTOR\n";
				if($kmlWriter->writeKMZ(false))
				{
					$filename=$kmlWriter->getFileName();
					$qm->setQueryStatus($queryID,"completed");
					echo "$queryID generate-kml: success! $filename generated successfully! :)\n";
					log_query($queryID,'complete');
					$qm->setQueryRunningStatus($queryID,5);
				} else {
					$all_ok = false;
					echo "$queryID generate-kml: failure :(\n";
					log_query($queryID,'kml_fail');
				}
			} 
			if($qs==5) {
				$qm->setQueryStatus($queryID,"completed");
				log_query($queryID,'complete');
			} 
			if($qs==0) {
				$qm->setQueryStatus($queryID,"error");
				log_query($queryID,'error');
			}		
		}
	}

	if($all_ok)
		create_ok_sig();


?>