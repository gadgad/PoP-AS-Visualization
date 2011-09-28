<?php
    require_once('bin/colorManager.php');
	
	$user = 'gadi';
	$qid = '0b6f948d14f516e52dbe6f469a8dbbaf';
	$cm = new colorManager($user,$qid);
	//print_r($cm->getColorList());
	
	print_r($cm->getASList());
?>
