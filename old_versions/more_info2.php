<?php

if(!isset($_GET["src_pop"]) || !isset($_GET["dst_pop"]) || !isset($_GET["threshold"]))
{
	echo "missing parameters!";
	die();
}

$src_pop = $_GET["src_pop"];
$dst_pop = $_GET["dst_pop"];

$pop_xml = simplexml_load_file("xml\pop.xml");
$EDGES_xml = simplexml_load_file("xml\edges.xml");
$asn_info_xml = simplexml_load_file("xml\ASN_info.xml");

$ASN_LIST=array();
$EDGES=array();
$POP_2_LOC_MAP=array();
$LOC_2_POP_MAP=array();

define('INTER_CON',true);
define('INTRA_CON',true);

define('STDEV_THRESHOLD',$_GET["threshold"]);

//////////////////////////////////////////////////////////////////////////////
function cmp($a,$b)
{
	if($a["median"]==$b["median"])
		return 0;
	return ($a["median"]<$b["median"])? -1 : 1;
}

class RunningStat
{
	private $n, $oldM, $newM, $oldS, $newS;
	
	public function __construct() { $this->n = 0; }
	public function clear(){ $this->n = 0; }
	
	function push($x)
	{
		$this->n++;
		if($this->n==1)
		{
			$this->oldM = $this->newM = $x;
			$this->oldS = 0.0;
		} else {
			$this->newM = $this->oldM + ($x-$this->oldM)/$this->n;
			$this->newS = $this->oldS + ($x - $this->newM);
			
			$this->oldM = $this->newM;
			$this->oldS = $this->newS;
		}
	}
	
	function NumOfValues(){ return $this->n; }
	function mean() { return ($this->n > 0)? $this->newM : 0.0; }	
	function variance(){ return ($this->n>1)? $this->newS/($this->n - 1) : 0.0; }
	function  standardDeviation() { return sqrt($this->variance()); }
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 foreach($EDGES_xml->children() as $edge)
 {
	if(((string)$edge->Source_PoPID != (string)$edge->Dest_PoPID))
	{
		if((INTRA_CON && (intval($edge->SourceAS)==intval($edge->DestAS))) || (INTER_CON && (intval($edge->SourceAS)!=intval($edge->DestAS))))
		{
		    $edge_str = $edge->Source_PoPID.$edge->Dest_PoPID;
		    if(!array_key_exists($edge_str, $EDGES)){
		     	$EDGES[$edge_str] = array("SourceAS"=>intval($edge->SourceAS),
		     							  "DestAS"=>intval($edge->DestAS),
		     							  "SourcePoP"=>(string)($edge->Source_PoPID),
		     							  "DestPoP"=>(string)($edge->Dest_PoPID),
		     							  "numOfEdges"=>1,
		     							  "median_lst"=>array(floatval($edge->Median)),
		     							  "step_lst"=>array(floatval($edge->step)),
		     							  "count_lst"=>array(intval($edge->count)),
		     							  "edgeID_lst"=>array($edge->edgeid),
		     							  "src_ip_lst"=>array($edge->SourceIP),
		     							  "dest_ip_lst"=>array($edge->DestIP));
		    } else {
		    	$EDGES[$edge_str]["numOfEdges"]++;
				$EDGES[$edge_str]["median_lst"][] = floatval($edge->Median);
				$EDGES[$edge_str]["step_lst"][] = floatval($edge->step);
				$EDGES[$edge_str]["count_lst"][] = intval($edge->count);
		    	$EDGES[$edge_str]["edgeID_lst"][] = $edge->edgeid;
		    	$EDGES[$edge_str]["src_ip_lst"][] = $edge->SourceIP;
		    	$EDGES[$edge_str]["dest_ip_lst"][] = $edge->DestIP;
	    	}
		}
	}
}

foreach($pop_xml->children() as $pop)
  {
  	// generate map of PoP coordinates
    if(!array_key_exists((string)$pop->PoPID, $POP_2_LOC_MAP) 
    	|| !isset($POP_2_LOC_MAP[(string)$pop->PoPID]["lat"])
		|| !isset($POP_2_LOC_MAP[(string)$pop->PoPID]["lng"])){
    	$POP_2_LOC_MAP[(string)$pop->PoPID]["lat"] = floatval($pop->LAT2);
		$POP_2_LOC_MAP[(string)$pop->PoPID]["lng"] = floatval($pop->LNG2);
	}
  }

$edge_str = $src_pop.$dst_pop;
$link = $EDGES[$edge_str];
if($link["SourcePoP"]!="NULL" && $link["DestPoP"]!="NULL" 
&& array_key_exists($link["SourcePoP"], $POP_2_LOC_MAP) 
&& array_key_exists($link["DestPoP"], $POP_2_LOC_MAP)
&& array_key_exists("lat", $POP_2_LOC_MAP[$link["SourcePoP"]])
&& array_key_exists("lat", $POP_2_LOC_MAP[$link["DestPoP"]]))
{
	$srcLAT = $POP_2_LOC_MAP[$link["SourcePoP"]]["lat"];
	$srcLNG = $POP_2_LOC_MAP[$link["SourcePoP"]]["lng"];
	$dstLAT = $POP_2_LOC_MAP[$link["DestPoP"]]["lat"];
	$dstLNG = $POP_2_LOC_MAP[$link["DestPoP"]]["lng"];
	
	if(($srcLAT == $dstLAT)&&($srcLNG == $dstLNG))
		return "";

	$srcAS = $link["SourceAS"];
	$dstAS = $link["DestAS"];
	$srcPOP = $link["SourcePoP"];
	$dstPOP = $link["DestPoP"];
	
	$result = $asn_info_xml->xpath("/DATA/ROW[ASNumber=".$srcAS."]");
	if(!empty($result))
	{
		$srcAS_info = array("Country"=>$result[0]->Country,"ISPName"=>$result[0]->ISPName);
	}
	
	$result = $asn_info_xml->xpath("/DATA/ROW[ASNumber=".$dstAS."]");
	if(!empty($result))
	{
		$dstAS_info = array("Country"=>$result[0]->Country,"ISPName"=>$result[0]->ISPName);
	}
	
	$table_str = "<table id=\"myTable\" class=\"tablesorter\">\n<thead>\n<tr><th>"; 		

	$numOfEdges = $link["numOfEdges"];
	$edge_lst_str = "<P>Source PoP: ".$srcPOP." Dest PoP: ".$dstPOP."</P></BR>";
	$edge_lst_str.="<P>#Edges: ".$numOfEdges."</BR>";
	$median_lst = array();
	for($i=0;$i<$numOfEdges; $i++)
	{
		$median_lst[] = array("median"=>$link["median_lst"][$i],"index"=>$i);
	}
	uasort($median_lst,'cmp');
	
	$rs = new RunningStat();
	foreach($median_lst as $elem)
	{
		$index = $elem["index"];
		$x = $elem["median"];
		$rs->push($x); 
		if(abs($rs->mean() - $x) > STDEV_THRESHOLD*($rs->standardDeviation()))
		{
			$edge_lst_str.= "new physical link detected: </BR>";
			$rs->clear();
			$rs->push($x);
		}
		$edge_lst_str.="EdgeID: ".$link["edgeID_lst"][$index]."\nSourceIP: ".$link["src_ip_lst"][$index]."\nDestIP: ".$link["dest_ip_lst"][$index]."\nMedian: ".$link["median_lst"][$index]."</BR>\n";
	}
	$edge_lst_str.="</P>";
	return $edge_lst_str;
}
?>


<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Edge Info</title>
        <script src="http://code.jquery.com/jquery-latest.js"></script>
		<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script> 
    </head>
    <body>
        <?php
        	echo edge_info($src_pop,$dst_pop);
        ?>
    </body>
</html>