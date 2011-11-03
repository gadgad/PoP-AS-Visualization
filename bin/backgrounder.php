<?php

class Backgrounder
{
	private $isWin;
	private $cmd;
	private $id;
	
	private $basedir;
	private $shelldir;
	private $log_filename;
	private $bat_filename;
	
	private $pid_filename;
	private $pid;
	private $lastRunTime;
	
	private $exe;
	
	private $lastLogLine;
	
	public function __construct($cmd,$id,$qid = null)
	{
		$this->id = $id;
		
		chdir( dirname ( __FILE__ ) );
	    chdir ('../');
		if (stristr(PHP_OS, 'WIN')) {
			$this->isWin = true; 
			$this->basedir = str_replace('\\','/',getcwd());
		} else {
			$this->isWin = false; 
			$this->basedir = getcwd();
		}
		$this->shelldir = $this->basedir."/shell";
		$this->log_filename = $this->shelldir."/log/".$id.(($qid!=null)?('-'.$qid):'').'.log';
		$this->pid_filename = $this->shelldir."/log/".$id.(($qid!=null)?('-'.$qid):'').'.pid';
		$this->cmd = "php ".$this->shelldir."/".$cmd;
		
		$this->pid = -10;
		$this->lastRunTime = -1;
		$this->lastLogLine = '';
		
		if(file_exists($this->pid_filename)){
			$this->extract_pid();
		}
	}
	
	public function run(){
	
		if($this->isRunning()){
			return -1;
		} else {
			if(file_exists($this->pid_filename))
				unlink($this->pid_filename);
		}
		
		if ($this->isWin) { 
			$this->win_backgrounder();
		} else {
			$this->linux_backgrounder();
		}
		 
		$status = pclose(popen($this->exe, 'r'));
		$this->extract_pid();
		return $status;
	}
	
	private function extract_lastLogLines($lines){
		if($this->isWin){
			$line = '';
			$f = fopen($this->log_filename, 'r');
			$cursor = -1;
			fseek($f, $cursor, SEEK_END);
			$char = fgetc($f);
			/**
			 * Trim trailing newline chars of the file
			 */
			while ($char === "\n" || $char === "\r") {
			    fseek($f, $cursor--, SEEK_END);
			    $char = fgetc($f);
			}
			/**
			 * Read until the start of file or first newline char
			 */
			$counter = $lines;
			while ($char !== false && $counter>=0) {
				if($char == "\n" || $char == "\r")
					$counter--;
			    /**
			     * Prepend the new char
			     */
			    $line = $char . $line;
			    fseek($f, $cursor--, SEEK_END);
			    $char = fgetc($f);
			}
			$this->lastLogLine = $line;
		} else {
			$file = escapeshellarg($this->log_filename);
			$line = `tail -n $lines $file`;
			$this->lastLogLine = $line;
		}
	}
	
	private function extract_pid(){
		static $counter = 0;
		while(!file_exists($this->pid_filename) && $counter < 3){
			$counter++;
			sleep(2);
		}
		$file_handle = fopen($this->pid_filename, "r") or die("can't open ".$this->pid_filename."\n");
		$str = fgets($file_handle);
		list($pid, $time) = explode(' ',$str,2);
		$this->pid = intval($pid);
		$this->lastRunTime = intval($time);
	}
	
	private function win_backgrounder()
	{
		$this->bat_filename = $this->shelldir."/".$this->id."_run.bat";
		$bat_file = fopen($this->bat_filename, "w");
		$title = md5($this->cmd);
		if($bat_file) {
		    fwrite($bat_file, "@echo off"."\n");
			fwrite($bat_file, 'FOR /F "tokens=2 delims= " %%A IN (\'TASKLIST /v ^| find /I "'.$title.'"\') DO SET PID=%%A'."\n");
			fwrite($bat_file, "echo %PID% ".time()." > ".$this->pid_filename."\n");	
		    fwrite($bat_file, "echo Starting proces >> ".$this->log_filename."\n");
		    fwrite($bat_file, $this->cmd." >> ".$this->log_filename."\n");
		    //fwrite($bat_file, "echo End proces >> ".$this->log_filename."\n");
		    fwrite($bat_file, "EXIT"."\n");
		    fclose($bat_file);
		}
		
		$this->exe = "start \"".$title."\" /MIN ".$this->bat_filename;	
	}

	private function linux_backgrounder()
	{
		$this->exe = sprintf("%s > %s 2>&1 & echo $! %d > %s", $this->cmd, $this->log_filename, time(), $this->pid_filename);
	}
	
	public function getPID(){
		if($this->pid!=-10)
			return $this->pid;
		return -1;
	}
	
	public function getLastRunTime(){
		return  (time()-$this->lastRunTime);
	}
	
	public function getLastLogLines($lines){
		$this->extract_lastLogLines($lines);
		return $this->lastLogLine;
	}
	
	public function isRunning(){
		$pid = (func_num_args()==1)? func_get_arg(0) : $this->pid;
	    try{
	    	$linux_cmd = sprintf("ps %d", $pid);
			$win_cmd = sprintf('tasklist /FI "PID eq %d"', $pid);
			$cmd = ($this->isWin)? $win_cmd : $linux_cmd ;
	        $result = shell_exec($cmd);
	        if( count(preg_split("/\n/", $result)) > 2){
	            return true;
	        }
	    } catch(Exception $e){}
	    return false;
	}
	
	public function posix_isRunning(){
		$pid = (func_num_args()==1)? func_get_arg(0) : $this->pid;
		$running=posix_kill($pid, 0);
  		if(posix_get_last_error()==1) /* EPERM */
    		$running=true;
    	return $running;		
	}
	
	// optional parameters: [sleepInterval], [pid]
	public function waitpid(){
		$sleepTime = 5; // sec
		$pid = $this->pid;
		
		if(func_num_args() == 1){
			$sleepTime = func_get_arg(0);
		}
		
		if(func_num_args() == 2){
			$sleepTime = func_get_arg(0);
			$pid = func_get_arg(1);
		}
		
		while($this->isRunning($pid)){
			sleep($sleepTime);
		}
		
	}
}

/*
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
 * 
 */


?>