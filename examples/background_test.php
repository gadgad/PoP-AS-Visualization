<?php
	require_once("bin/backgrounder.php");
	
    $cmd = "test.php --foo=bar";
	$cmd1 = new Backgrounder($cmd,'test','1');
	$cmd1->run();
	if($cmd1->isRunning()) {
		echo "process ".$cmd1->getPID()." is running!\n";
	} else {
		echo "error\n";
	}
	echo "last run time, before: ".$cmd1->getLastRunTime()." seconds\n";
	
?>