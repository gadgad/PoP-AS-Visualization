<?php
    class idGen
    {
    	private $edgeTbl;
    	private $popTbl;
    	private $asList;
    	private $queryID;
    	
    	public function __construct($edgeTbl,$popTbl,$asList)
    	{
    		$this->edgeTbl = $edgeTbl;
    		$this->popTbl = $popTbl;
    		$this->asList = $asList;
    		$this->queryID = md5($edgeTbl."_".$popTbl.join('_',$asList));
    	}
    	
    	public function getEdgeTblName() { return "DPV_EDGE_".$this->queryID; }
    	public function getPoPTblName() { return "DPV_POP_".$this->queryID; }
    	public function getDirName() { return $this->queryID; }	
    }
?>