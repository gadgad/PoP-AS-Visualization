<?php
require_once("bin/shared_disk.php");
require_once("bin/shared_mem.php");

// TODO: make it cross platrform!
class MySharedMem {
	// status: 0 = started , 1 = working , 2 = finish , -1 = error
	// phase (current running stage)
	// progress (for current stage in precentage)
	
	private $smem;
	private $queryID;

	public function construct(){
		$this->smem = new SharedMemory();
	}
	
	public function init_qid($qid){
		$smem =$this->smem->$qid;
		$smem['status'] = 0;
	}
	
	public function child_init($queryID){
		$this->queryID = $queryID;
		
		$limit = 5;
		$retries = 0;
		@$smem =& $sharedMem->$queryID;
		while((!isset($smem) || !isset($smem['status'])) && $retries<$limit){
			$retries++;
			echo "sleeping...";
			sleep(1);
		}
		if($retries==$limit){
			return false;	
		}
		return true;
	}
	
	public function generic_init($queryID){
		$this->queryID = $queryID;
		
		@$smem =& $sharedMem->$queryID;
		if(isset($smem['status'])){
			return true;
		}
		return false;
	}
	
	// status: 0 = started , 1 = working , 2 = finish , -1 = error
	public function setStatus($status_id){
		$qid = $this->queryID;
		$smem =& $sharedMem->$qid;
		$smem['status'] = $status_id;
	}
	
	public function setPhase($state_id){
		$qid = $this->queryID;
		$smem =& $sharedMem->$qid;
		$smem['pahse'] = QueryManager::getStatusMsg($state_id);
	}
	
	public function setProgress($progress){
		$qid = $this->queryID;
		$smem =& $sharedMem->$qid;
		$smem['progress'] = $progress;
	} 
}
?>