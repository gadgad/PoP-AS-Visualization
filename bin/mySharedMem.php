<?php
//require_once("bin/shared_disk.php");
require_once("bin/shared_mem.php");

// TODO: make it cross platrform!
class MySharedMem {
	// status: 0 = started , 1 = working , 2 = finish , -1 = error
	// phase (current running stage)
	// progress (for current stage in precentage)
	
	private $smem;
	private $queryID;

	public function __construct(){
		$this->smem = new SharedMemory();
	}
	
	public function init_qid($qid){
		$var = $this->smem->Get();
		$init_value = array('status'=>0,'phase'=>0,'progress'=>0.0);
		if(!is_array($var)){
			$var = array($qid=>$init_value);
		} else {
			$var[$qid] = $init_value;
		}
		$this->smem->Set($var);
	}
	
	public function child_init($queryID){
		$this->queryID = $queryID;
		$limit = 5;
		$retries = 0;
		$shared_var = $this->smem->Get();
		while(!isset($shared_var[$this->queryID]) && $retries<$limit){
			$retries++;
			echo "sleeping...";
			sleep(1);
			$shared_var = $this->smem->Get();
		}
		if($retries==$limit){
			return false;	
		}
		return true;
	}
	
	public function test_init($queryID){
		$this->queryID = $queryID;
		$shared_var = $this->smem->Get();
		if(isset($shared_var[$htis->queryID])){
			return true;
		}
		return false;
	}
	
	// status: 0 = started , 1 = working , 2 = finish , -1 = error
	public function setStatus($status_id){
		$var = $this->smem->Get();
		$var[$this->queryID]['status'] = $status_id;
		$this->smem->Set($var);
	}
	
	public function getStatus(){
		$var = $this->smem->Get();
		return $var[$this->queryID]['status'];
	}
	
	public function setPhase($state_id){
		$var = $this->smem->Get();
		$var[$this->queryID]['pahse'] = QueryManager::getStatusMsg($state_id);
		$this->smem->Set($var);
	}
	
	public function getPhase(){
		$var = $this->smem->Get();
		return $vat[$this->queryID]['phase'];
	}
	
	public function setProgress($progress){
		$var = $this->smem->Get();
		$var[$this->queryID]['progress'] = $progress;
		$this->smem->Set($var);
	}
	
	public function getProgress(){
		$var = $this->smem->Get();
		return $vat[$this->queryID]['progress'];
	} 
}
?>