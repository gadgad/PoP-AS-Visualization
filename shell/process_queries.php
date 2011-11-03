<?php
	//error_reporting(E_ERROR);
	
	require_once("bin/backgrounder.php");
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
	
	function clean_query_log(){
		$filename = "shell/log/queries.log";
		if(file_exists($filename))
			unlink($filename);
	}
	
	function clean_children_arr(&$children){
		foreach($children as $key=>$child){
			if(!$child->isRunning()){
				unset($children[$key]);
			}
		}
 		$children = array_values($children); // Reindex the array
	}
	
	/////////////////////////////////////////////////
	remove_ok_sig();
	clean_query_log();
	
	$queries = simplexml_load_file("xml/query.xml");
	$result = $queries->xpath('/DATA/QUERY[lastKnownStatus="running"]');
	if(empty($result)){
		sleep(3);
		create_ok_sig();
		exit("nothing to do...\n");
	}
	
	// init shared memory segment (IPC)
	$smem = new MySharedMem();
	
	// init main loop vars
	$work2do = true;
	$childrenRunning = false;
	$all_ok = true;
	$children = array();
	$max_children = 5;
	$seen_qid_map = array();
	$qid_map = array();
	$sleep_interval = 5; // seconds
	
	while($work2do || $childrenRunning){
	
		$running_blade_map = array();
		foreach($result as $rq)
		{
			$blade = (string)$rq->blade;
			$running_blade_map[$blade][] = (string)$rq->queryID;	
		}
		
		foreach($running_blade_map as $blade => $rq_lst) {
			
			$qm = new QueryManager($blade);
			foreach($rq_lst as $queryID) {
				
				if(!array_key_exists($queryID,$qid_map) || $qid_map[$queryID]['taken']==false){
					// mark current qid as 'seen'
					if(!array_key_exists($queryID,$qid_map)){
						$qid_map[$queryID] = array('seen'=>true,'taken'=>false);
					}
					
					// get gueryID updated status
					try {
						$qs = $qm->getQueryStatus($queryID);
					} catch(DBConnectionError $e){
						die("getQueryStatus: can't connect to db!\n");
					} 
					
					echo "$queryID status: ".QueryManager::getStatusMsg($qs)."\n";
					// 0 - error , 1 - running , 2 - db-ready, 3 - fetching-xml,  4 - xml-ready, 5 - kml-ready
					
					if($qs==0) {
						QueryManager::setQueryStatus($queryID,"error");
						log_query($queryID,'error');
					}	
					if($qs == 1){
						log_query($queryID,'db_build');
						QueryManager::setQueryRunningStatus($queryID,1);
					}
					if($qs == 2 || $qs == 3 || $qs == 4) // db-tables are ready, but no kml file..
					{
						if($qs == 2 || $qs == 3) log_query($queryID,'db_ready');
						if($qs == 4) log_query($queryID,'xml_done');
						
						// init a shared memory segment for this qid
						$smem->init_qid($queryID);
						
						// launch child here ...
						if(count($children)<$max_children){
							$cmd = "pq_child.php --queryID=".$queryID." --queryStatus=".$qs;
							$cmd1 = $children[] = new Backgrounder($cmd,'pq_child',substr($queryID,-5));
							$cmd1->run();
							$qid_map[$queryID]['taken'] = true;
						}
						
					} 
					if($qs==5) {
						QueryManager::setQueryStatus($queryID,"completed");
						log_query($queryID,'complete');
					}
				} // filter already 'seen' or 'taken' qids
			} // inner foreach
		} // outer foreach
	
		if($childrenRunning){
			
			sleep($sleep_interval);
			
			clean_children_arr($children);
			$childrenRunning = empty($children)? false:true;
			
			$work2do = false;
			$queries = simplexml_load_file("xml/query.xml");
			$result = $queries->xpath('/DATA/QUERY[lastKnownStatus="running"]');
			if(!empty($result)){
				foreach($result as $rq){
					$qid = (string)$rq->queryID;
					if(!array_key_exists($qid,$qid_map) || $qid_map[$qid]['taken']==false){
						$work2do = true;
						break;
					}
				}
			}
		}
		unset($running_blade_map);
	} // end of main loop

	if($all_ok){
		create_ok_sig();
	}

?>