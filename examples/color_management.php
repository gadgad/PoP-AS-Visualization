<?php
    require_once("verify.php");
	require_once('bin/userData.php');
	require_once('bin/color.php');
	require_once('bin/colorManager.php');
	
	if(!isset($_REQUEST["queryID"]) || !isset($_REQUEST["func"]))
		ret_res('missing parameters!','ERROR');
	
	
	// globals
	$queryID = $_REQUEST["queryID"];
	$cm = new colorManager($username,$queryID);			
	
	function ret_res($message, $bol)
	{
		header('Content-type: application/json');
		echo json_encode(array("msg"=>$message ,"success"=>$bol));
		die();	
	}
	
	// Turn off all error reporting
	error_reporting(E_ERROR);
	
	
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
		ret_res('kml file rendered successfully',true);
	}
	
?>