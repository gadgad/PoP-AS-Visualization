<?php
    class idGen
    {
    	private $edgeTbl;
    	private $popTbl;		
    	private $asList;
    	private $queryID;
		private $popIPTbl;
		private $blade;
    	
    	public function __construct()
    	{
    		if(func_num_args()==5)
    		{
    			$this->edgeTbl = func_get_arg(0);
    			$this->popTbl = func_get_arg(1);
    			$this->asList = func_get_arg(2);
				$this->popIPTbl = func_get_arg(3);
				$this->blade = func_get_arg(4);
    			$this->queryID = md5($this->blade."_".$this->edgeTbl."_".$this->popTbl."_".$this->popIPTbl.implode('_',$this->asList));
			} else if(func_num_args()==1) {
				$this->queryID = func_get_arg(0);
			}
    	}
    	
    	public function getEdgeTblName() { return "DPV_EDGE_".$this->queryID; }
    	public function getPoPTblName() { return "DPV_POP_".$this->queryID; }
    	public function getDirName() { return $this->queryID; }	
		public function getqueryID() { return $this->queryID; }
    }
?>