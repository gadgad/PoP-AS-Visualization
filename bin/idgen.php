<?php
    class idGen
    {
    	private $edgeTbl;
    	private $popTbl;
    	private $asList;
    	private $queryID;
    	
    	public function __construct()
    	{
    		if(func_num_args()==3)
    		{
    			$this->edgeTbl = func_get_arg(0);
    			$this->popTbl = func_get_arg(1);
    			$this->asList = func_get_arg(2);
    			$this->queryID = md5($this->edgeTbl."_".$this->popTbl.join('_',$this->asList));
			} else if(func_num_args()==1) {
				$this->queryID = func_get_arg(0);
			}
    	}
    	
    	public function getEdgeTblName() { return "DPV_EDGE_".$this->queryID; }
    	public function getPoPTblName() { return "DPV_POP_".$this->queryID; }
    	public function getDirName() { return $this->queryID; }	
    }
?>