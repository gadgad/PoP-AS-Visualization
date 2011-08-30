<?php

class Backgrounder
{
	private $cmd;
	private $id;
	private $qid;
	
	private $basedir;
	private $shelldir;
	private $log_filename;
	
	public function __construct($cmd,$id,$qid)
	{
		$this->cmd = $cmd;
		$this->id = $id;
		$this->qid = $qid;
		
		chdir( dirname ( __FILE__ ) );
	    chdir ('../');
		if (stristr(PHP_OS, 'WIN')) { 
			$this->basedir = str_replace('\\','/',getcwd());
		} else {
			$this->basedir = getcwd();
		}
		$this->shelldir = $this->basedir."/shell";
		$this->log_filename = $this->shelldir."/".$id.'-'.$qid.'.log';
		$this->pid_filename = $this->shelldir."/".$id.'-'.$qid.'.pid';
	}
	
	public function run(){
		if (stristr(PHP_OS, 'WIN')) { 
			$this->win_backgrounder();
		} else {
			$this->linux_backgrounder();
		}
	}
	
	private function win_backgrounder()
	{
		$bat_filename = $this->shelldir."/".$this->id."_run.bat";
		$bat_file = fopen($bat_filename, "w");
		if($bat_file) {
		    fwrite($bat_file, "@echo off"."\n");
		    fwrite($bat_file, "echo Starting proces >> ".$this->log_filename."\n");
		    fwrite($bat_file, "php ".$this->shelldir."/".$this->cmd." >> ".$this->log_filename."\n");
		    fwrite($bat_file, "echo End proces >> ".$this->log_filename."\n");
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

	private function linux_backgrounder()
	{
		exec(sprintf("%s > %s 2>&1 & echo $! >> %s", $this->cmd, $this->log_filename, $this->pid_filename));
	}
	
	private function isRunning($pid){
	    try{
	        $result = shell_exec(sprintf("ps %d", $pid));
	        if( count(preg_split("/\n/", $result)) > 2){
	            return true;
	        }
	    } catch(Exception $e){}
	    return false;
	}
}


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