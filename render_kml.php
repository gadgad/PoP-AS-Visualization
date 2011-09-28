<?php
	require_once("verify.php");
	require_once('bin/colorManager.php');
	require_once("bin/kml_writer.php");
	
	function ret_res($message, $bol)
	{
		header('Content-type: application/json');
		echo json_encode(array("msg"=>$message ,"success"=>$bol));
		die();	
	}
	
	// Turn off all error reporting
	error_reporting(E_ERROR);
	
	if(!isset($_REQUEST["queryID"]) || !isset($_REQUEST["func"]))
		ret_res('missing parameters!','ERROR');
	
	$queryID = $_REQUEST['queryID'];
	
	if($_REQUEST["func"]=="renderKML"){
		if(!isset($_POST['MIN_LINE_WIDTH']) ||
		!isset($_POST['MAX_LINE_WIDTH']) ||
		!isset($_POST['INITIAL_ALTITUDE']) ||
		!isset($_POST['ALTITUDE_DELTA']) ||
		!isset($_POST['STDEV_THRESHOLD']))
		{
			ret_res('missing parameters!',false);
		}
		
	    $kmlWriter = new kmlWriter($queryID);
		if($kmlWriter->writeKMZ())
		{
			//$filename=$kmlWriter->getFileName();
			ret_res('kml file rendered successfully',true);
		}
		ret_res('problem rendering kml file...',false);
	}
	
	if($_REQUEST["func"]=="getASNColorList")
	{
		$COLOR_LIST = $cm->getColorList();
		$result = array();
		$result['totalCount'] = count($COLOR_LIST['asn']);
		foreach($COLOR_LIST['asn'] as $asn=>$color){
			$result['asn-colors'][] = array('asn'=>$asn, 'color'=>('#'.$color->web_format()));
		}
		
		header('Content-type: application/json');
		echo json_encode($result);
		die();
	}
	
	if($_REQUEST["func"]=="submitASNColorList")
	{
		$color_string = $_REQUEST["color_string"];
		$saveToGlobal = $_REQUEST["global"];
		$color_prefs = json_decode($color_string);
		
		
		//TODO: svae color prefs to colorManager!
		ret_res('color prefs saved successfully',true);
	}
    
?>