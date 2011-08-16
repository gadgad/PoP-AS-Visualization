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
	!isset($_POST['queryID']))
	{
		ret_res('missing parameters!',false);
	}
	
   	define('MIN_LINE_WIDTH',$_POST['MIN_LINE_WIDTH']);
	define('MAX_LINE_WIDTH',$_POST['MAX_LINE_WIDTH']);
	define('INITIAL_ALTITUDE',$_POST['INITIAL_ALTITUDE']);
	define('ALTITUDE_DELTA',$_POST['ALTITUDE_DELTA']);
	define('DRAW_CIRCLES',isset($_POST['DRAW_CIRCLES']));
	define('INTER_CON',isset($_POST['INTER_CON']));
	define('INTRA_CON',isset($_POST['INTRA_CON']));
	define('CONNECTED_POPS_ONLY',isset($_POST['CONNECTED_POPS_ONLY']));
	define('USE_COLOR_PICKER',isset($_POST['USE_COLOR_PICKER']));
	define('STDEV_THRESHOLD',isset($_POST['STDEV_THRESHOLD']));
	define('KML_RENDER_GLOBALS',true);

	
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