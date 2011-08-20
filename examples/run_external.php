<?php

	chdir( dirname ( __FILE__ ) );
	$thisdir = str_replace('\\','/',getcwd());
	$shelldir = $thisdir."/shell";
	
    $bat_filename = $shelldir."/run.bat";
	$bat_log_filename = $shelldir."/send_query.log";
	$bat_file = fopen($bat_filename, "w");
	if($bat_file) {
	    fwrite($bat_file, "@echo off"."\n");
	    fwrite($bat_file, "echo Starting proces >> ".$bat_log_filename."\n");
	    fwrite($bat_file, "php ".$shelldir."/send_query.php --foo=bar >> ".$bat_log_filename."\n");
	    fwrite($bat_file, "echo End proces >> ".$bat_log_filename."\n");
	    fwrite($bat_file, "EXIT"."\n");
	    fclose($bat_file);
	}
	           
	//
	// Start the process in the background
	//
	$exe = "start /b ".$bat_filename;
	if( pclose(popen($exe, 'r')) ) {
	    return true;
	}
	return false;
?>