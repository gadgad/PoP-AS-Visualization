<?php
    //error_reporting(E_ERROR);
    
    require_once("bin/xml_writer.php");
    require_once("bin/kml_writer.php");
    require_once("bin/query_status.php");
    require_once("bin/mySharedMem.php");
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
	
	function log_query($QID,$status){
		$ourFileName = "shell/log/queries/".$QID.".log";
		$ourFileHandle = fopen($ourFileName, 'a') or die("can't open log file");
		fwrite($ourFileHandle,$QID." ".$status."\n");
		fclose($ourFileHandle);
	}
	
	function clean_query_log($QID){
		$filename = "shell/log/queries/".$QID.".log";
		if(file_exists($filename))
			unlink($filename);
	}
	
    /////////////////////////////////////////////////////////////
    $args = parseArgs($argv);
    $queryID = $args['queryID'];
    $qs = $args['queryStatus'];
	/////////////////////////////////////////////////////////////
	clean_query_log($queryID);
	echo "pq-child for qid: ".$queryID." launched with pid: ".posix_getpid()."\n";
	
	$smem = new MySharedMem();
	if(!$smem->child_init($queryID)){
		die("shared memory segment not initialized by parent process!\n");
	}
	echo "got init status from parent: ".$smem->getStatus()."\n";
	$smem->setStatus(1); // working
	
	echo "$queryID status: ".QueryManager::getStatusMsg($qs)."\n";
	// 0 - error , 1 - running , 2 - db-ready, 3 - fetching-xml,  4 - xml-ready, 5 - kml-ready
	
	if($qs == 2 || $qs == 3) // db-tables are ready, but no xml file..
	{
		log_query($queryID,'db_ready');
		
		QueryManager::setQueryRunningStatus($queryID,3);
		$xw = new xml_Writer($blade,$queryID);
	    if($xw->writeXML()){
	    	echo "$queryID generate-xml: success!\n";
			log_query($queryID,'xml_done');
			$smem->setProgress(100);
			QueryManager::setQueryRunningStatus($queryID,4);
			$qs=4;
	    } else {
			//$all_ok = false;
			$smem->setStatus(-1);
			echo "$queryID generate-xml: failure :(\n";
			log_query($queryID,'xml_fail');
		}
	}
	if($qs==4){ // xml ready, but no kml file
		//write generated KML file to disk
		$kmlWriter = new kmlWriter($queryID);
		if($kmlWriter->writeKMZ(false))
		{
			$smem->setProgress(100);
			QueryManager::setQueryStatus($queryID,"completed");
			QueryManager::setQueryRunningStatus($queryID,5);
			log_query($queryID,'complete');
			echo "$queryID generate-kml: success!\n";
		} else {
			//$all_ok = false;
			$smem->setStatus(-1); // error
			log_query($queryID,'kml_fail');
			echo "$queryID generate-kml: failure :(\n";
		}
	} 
	$smem->setStatus(2); // finish	
?>