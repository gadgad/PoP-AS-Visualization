<?php
    function win_backgrounder($cmd,$id,$qid)
    {
	    chdir( dirname ( __FILE__ ) );
	    chdir ('../');
		$thisdir = str_replace('\\','/',getcwd());
		$shelldir = $thisdir."/shell";
		
	    $bat_filename = $shelldir."/".$id."_run.bat";
		$bat_log_filename = $shelldir."/".$id.'-'.$qid.'.log';
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
			unlink($bat_filename);
		    return true;
		}
		return false;
	}
?>