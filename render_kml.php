<?php
	function ret_res($message, $bol)
	{
		header('Content-type: application/json');
		echo json_encode(array("msg"=>$message ,"success"=>$bol));
		die();	
	}
	
	
	if(!isset($_POST['MIN_LINE_WIDTH']) ||
	!isset($_POST['MAX_LINE_WIDTH']) ||
	!isset($_POST['INITIAL_ALTITUDE']) ||
	!isset($_POST['ALTITUDE_DELTA']) ||
	!isset($_POST['STDEV_THRESHOLD']) ||
	!isset($_POST['queryID']))
	{
		ret_res('missing parameters!',false);
	}
	
	require_once("bin/kml_render_globals.php");	
	require_once("bin/kml_writer.php");
	
	// Turn off all error reporting
	error_reporting(0);
    
    $queryID = $_POST['queryID'];
    $kmlWriter = new kmlWriter($queryID);
	if($kmlWriter->writeKMZ())
	{
		//$filename=$kmlWriter->getFileName();
		ret_res('kml file rendered successfully',true);
	}
	ret_res('problem rendering kml file...',false);
    
?>