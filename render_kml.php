<?php
/*
 * this file serves as the server-side (backend) for visual_frontend.php
 *  main functionality is to invoke re-rendering of kml files when the user 
 *  choose the "save changes" option.
 */
	require_once("verify.php");
	require_once('bin/colorManager.php');
	require_once("bin/kml_writer.php");
	
	// used to send data back to the client as json formatted string
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
	
	// initialize colorManager - interface is used for saving & restoring 
	// user color preferences between sessions  
	$cm = new colorManager($username,$queryID);	
	
	// invoke re-rendering of kml file according to
	// user preferences when 'save changes' button is clicked
	if($_REQUEST["func"]=="renderKML"){
		
		// check validity of request
		if(!isset($_POST["panel"])) ret_res('missing parameters!',false);
		
		if($_POST["panel"] == 'edges'){
			if(!isset($_POST['EDGES_COLORING_SCHEME']) ||
			!isset($_POST['EDGES_INTER_COLOR']) ||
			!isset($_POST['EDGES_INTRA_COLOR']))
			{
				ret_res('missing parameters!',false);
			}
		} else {
			if(!isset($_POST['MIN_LINE_WIDTH']) ||
			!isset($_POST['MAX_LINE_WIDTH']) ||
			!isset($_POST['INITIAL_ALTITUDE']) ||
			!isset($_POST['ALTITUDE_DELTA']) ||
			!isset($_POST['STDEV_THRESHOLD']))
			{
				ret_res('missing parameters!',false);
			}
		}
		
		// re-write kml file (user updated prefrences are directly referenced
		// from inner-implementation).
	    $kmlWriter = new kmlWriter($queryID);
		if($kmlWriter->writeKMZ(true))
		{
			ret_res('kml file rendered successfully',true);
		}
		ret_res('problem rendering kml file...',false);
	}
	
	// returns a list of ASN's and their current assocciated colors
	// (as was saved on the server from the last 'save changes' event)
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
	
	// store the user asn-coloring preferences
	// on the server for later retrieval (by the above function)
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