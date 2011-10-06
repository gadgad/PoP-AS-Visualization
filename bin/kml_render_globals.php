<?php
	$username = isset($_COOKIE['username'])? $_COOKIE['username'] : $_SESSION['username'];
	$session = new userData($username,$queryID);
	
	$session->setGlobal('MIN_LINE_WIDTH',3);
	$session->setGlobal('MAX_LINE_WIDTH',5);
	$session->setGlobal('TRANSPARENCY',150);
	$session->setGlobal('INITIAL_ALTITUDE',10);
	$session->setGlobal('ALTITUDE_DELTA',1000);
	$session->setGlobal('STDEV_THRESHOLD',2);
	$session->setGlobal('DRAW_CIRCLES',true);
	$session->setGlobal('INTER_CON',true);
	$session->setGlobal('INTRA_CON',true);
	$session->setGlobal('CONNECTED_POPS_ONLY',true);
	$session->setGlobal('USE_COLOR_PICKER',false);
	$session->setGlobal('BLACK_BACKGROUND',false);
	$session->setGlobal('ASN_EMBEDDED_IN_PLACEMARK',true);
	
	$session->setGlobal('EDGES_COLORING_SCHEME','bySrcAS');
	$session->setGlobal('EDGES_INTER_COLOR','#FF0101');
	$session->setGlobal('EDGES_INTRA_COLOR','#3CFF01');
	
	$session->save_data();
	
?>