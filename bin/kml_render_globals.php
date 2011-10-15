<?php
	$username = isset($_COOKIE['username'])? $_COOKIE['username'] : $_SESSION['username'];
	$session = new userData($username,$queryID);
	
	$session->setGlobal('MIN_LINE_WIDTH',3,'default');
	$session->setGlobal('MAX_LINE_WIDTH',5,'default');
	$session->setGlobal('TRANSPARENCY',150,'default');
	$session->setGlobal('INITIAL_ALTITUDE',10,'default');
	$session->setGlobal('ALTITUDE_DELTA',1000,'default');
	$session->setGlobal('STDEV_THRESHOLD',2,'default');
	$session->setGlobal('DRAW_CIRCLES',true,'default');
	$session->setGlobal('INTER_CON',true,'default');
	$session->setGlobal('INTRA_CON',true,'default');
	$session->setGlobal('CONNECTED_POPS_ONLY',false,'default');
	$session->setGlobal('USE_COLOR_PICKER',false,'default');
	$session->setGlobal('BLACK_BACKGROUND',false,'default');
	$session->setGlobal('ASN_EMBEDDED_IN_PLACEMARK',true,'default');
	
	$session->setGlobal('EDGES_COLORING_SCHEME','bySrcAS','edges');
	$session->setGlobal('EDGES_INTER_COLOR','#FF0101','edges');
	$session->setGlobal('EDGES_INTRA_COLOR','#3CFF01','edges');
	
	$session->save_data();
	
?>