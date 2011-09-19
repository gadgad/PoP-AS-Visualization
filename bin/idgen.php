<?php
    class idGen
    {
    	private $edgeTbl;
    	private $popTbl;		
    	private $asList;
    	private $queryID;
		private $tableID;
		private $popIPTbl;
		private $blade;
		private $year;
		private $week;
    	
		// (edgeTbl, popTbl, asList, popIPTbl, blade)
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
				$this->tableID = md5($this->blade."_".$this->edgeTbl."_".$this->popTbl."_".$this->popIPTbl);
			} else if(func_num_args()==1) {
				$this->queryID = func_get_arg(0);
			} else if(func_num_args()==2) {
				$this->queryID = func_get_arg(0);
				$this->tableID = func_get_arg(1);
			}
			$sx = simplexml_load_file("xml\query.xml");
			$res = $sx->xpath('/DATA/QUERY[queryID="'.$this->queryID.'"]');
			 if(func_num_args()==1)
				$this->tableID = (string)$res[0]->tableID;
			 $this->year = (string)$res[0]->year;
			 $this->week = (string)$res[0]->week;
			
    	}
    	
    	public function getEdgeTblName() { return "DPV_EDGE_".$this->year."_".$this->week."_".$this->tableID; }
    	public function getPoPTblName() { return "DPV_POP_".$this->year."_".$this->week."_".$this->tableID; }
    	public function getDirName() { return $this->queryID; }	
		public function getqueryID() { return $this->queryID; }
		public function getTableID() { return $this->tableID; }
    }
?>