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
	$cm = new colorManager($username,$queryID);	
	
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
		if($kmlWriter->writeKMZ(true))
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
	
	if($_REQUEST["func"]=="submitColorPrefs")
	{
		$color_string = $_REQUEST["color_string"];
		$saveToGlobal = $_REQUEST["global"];
		
		$target =& $cm->USER_QID_COLOR_LIST;
		if($saveToGlobal){
			$target =& $cm->USER_GLOBAL_COLOR_LIST;
		}
		$color_prefs = json_decode($color_string);
		foreach($color_prefs as $arr){
			$asn = $arr[0];
			$webf = trim($arr[1],'#');
			$color = new Color($webf);
			$target['asn'][$asn] = $color;
			$target['color'][$color->web_format()] = $asn;
		}
		$cm->save_user_color_list();
		ret_res('color prefs saved successfully',true);
	}
    
?>