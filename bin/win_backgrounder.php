<?php
    function win_backgrounder($cmd,$log_filename)
    {
	    chdir( dirname ( __FILE__ ) );
	    chdir ('../');
		$thisdir = str_replace('\\','/',getcwd());
		$shelldir = $thisdir."/shell";
		$filename = strstr($log_filename,'.log',true);
	    $bat_filename = $shelldir."/".$filename."_run.bat";
		$bat_log_filename = $shelldir."/".$log_filename;
		$bat_file = fopen($bat_filename, "w");
		if($bat_file) {
		    fwrite($bat_file, "@echo off"."\n");
		    fwrite($bat_file, "echo Starting proces >> ".$bat_log_filename."\n");
		    fwrite($bat_file, "php ".$shelldir."/".$cmd." >> ".$bat_log_filename."\n");
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
	}
?>