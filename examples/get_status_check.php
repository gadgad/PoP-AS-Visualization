<?php
	require_once("bin/query_status.php");
	
	function ret_res($message, $type)
	{
		header('Content-type: application/json');
		echo json_encode(array("result"=>$message ,"type"=>$type));
		die();	
	}
	
	$queryID = isset($_REQUEST["query"])? $_REQUEST["query"]: '0b6f948d14f516e52dbe6f469a8dbbaf';
	$queries = simplexml_load_file("xml\query.xml");
	$res = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]/blade');
	$selected_blade = (string)$res[0];
    
	// 0 - error , 1 - running , 2 - db-ready, 3 - some-xml-ready,  4 - all-xml-ready, 5 - kml-ready
		$qm = new QueryManager($selected_blade);
		$query_status = $qm->getQueryStatus($queryID);
		
		if($query_status==0){
			ret_res("query is not running and table doesnt exsist or is locked","ERROR");
		}
		 
		if($query_status==1){
			ret_res('queries are still running..','RUNNING');
		}
		
		if($query_status==2){
			ret_res("ready to fetch data from db","TABLES-READY");
		}
		
		if($query_status==3 || $query_status == 4){
			ret_res("preparing xml files..","PROCESSING-XML");
		}
		
		if($query_status==5){
			ret_res('kml file is ready','COMPLETE');
		}
		 
		ret_res("assertion error - ambiguous status","ERROR");
?>