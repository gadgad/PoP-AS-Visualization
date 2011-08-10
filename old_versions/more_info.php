<?php

if(!isset($_GET["src_pop"]) || !isset($_GET["dst_pop"]))
{
	echo "missing parameters!";
	die();
}

$src_pop = $_GET["src_pop"];
$dst_pop = $_GET["dst_pop"];

$pop_xml = simplexml_load_file("../xml/pop.xml");
$EDGES_xml = simplexml_load_file("../xml/edges.xml");
$asn_info_xml = simplexml_load_file("../xml/ASN_info.xml");

$ASN_LIST=array();
$EDGES=array();
$POP_2_LOC_MAP=array();
$LOC_2_POP_MAP=array();

define('INTER_CON',true);
define('INTRA_CON',true);

define('STDEV_THRESHOLD',2);

function cmp($a,$b)
{
	if($a["median"]==$b["median"])
		return 0;
	return ($a["median"]<$b["median"])? -1 : 1;
}

function online_variance($data)
{
	$n = 0;
	$mean = 0;
	$M2 = 0;
	foreach($data as $x)
	{
		$n+=1;
		$delta=($x-$mean);
		$mean+=($delta/$n);
		$M2+=$delta*($x-$mean);
	}
	if($M2==0 || $n==1)
		return 0;
	$variance = $M2/($n-1);
	return $variance;
}

function weighted_incremental_variance($dataWeightPairs)
{
	$mean = 0;
	$S = 0;
	$weightSum = 0;
	foreach($dataWeightPairs as $pair)
	{
		$weight = $pair["weight"];
		$x = $pair["data"];
		$temp = $weight + $weightSum;
		$Q = $x - $mean;
        $R = $Q * $weight / $temp;
        $S = $S + $weightSum * $Q * $R;
        $mean+=$R;
        $weightSum = $temp;
	}
	if($S==0 || $weightSum==1)
		return 0;
	$variance = $S / ($weightSum-1);
	return $variance;
}

function array_average($a){
  return array_sum($a)/count($a) ;
}

function array_waverage($data){
	$sum = 0;
	$weightSum = 0;
	foreach($data as $pair)
	{
		$sum+=$pair["weight"]*$pair["data"];
		$weightSum+=$pair["weight"];
	}
	return $sum / $weightSum;
}
	

function edge_info($Source_PoPID,$Dest_PoPID)
  {
  		global $EDGES;
		global $POP_2_LOC_MAP;
		global $asn_info_xml;
  		$edge_str = $Source_PoPID.$Dest_PoPID;
  		$link = $EDGES[$edge_str];
		
  		if($link["SourcePoP"]!="NULL" && $link["DestPoP"]!="NULL" 
		&& array_key_exists($link["SourcePoP"], $POP_2_LOC_MAP) 
		&& array_key_exists($link["DestPoP"], $POP_2_LOC_MAP)
		&& array_key_exists("lat", $POP_2_LOC_MAP[$link["SourcePoP"]])
		&& array_key_exists("lat", $POP_2_LOC_MAP[$link["DestPoP"]])){
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
		$data = array();
		$prev = -1;
		$delta = 0;
		//$weightSum = 0;
		foreach($median_lst as $elem)
		{
			$index = $elem["index"];
			$data_elem = array("data"=>$elem["median"],"weight"=>$link["count_lst"][$index]);
			//$weightSum+=$data_elem["weight"]; 
			$data[] = $data_elem;
			$mono_data = array_map(function($pair) { return $pair['data']; }, $data);
			$online_var = online_variance($mono_data);
			$online_wvar = weighted_incremental_variance($data);
			//$new_weighted = $data_elem["data"]*$data_elem["weight"]/$weightSum;
			if($prev!=-1)
				$delta=abs($prev-$elem["median"]);
			//if(abs(array_average($mono_data)-$elem["median"])>2*sqrt($online_var))
			if(abs(array_waverage($data)-$elem["median"])>STDEV_THRESHOLD*sqrt($online_wvar))
			//if($delta>3)
			{
				$edge_lst_str.= "new physical link detected: </BR>";
				$delta=0;
				unset($data);
				unset($mono_data);
				$data = array($data_elem);
				$mono_data = array($elem["median"]);
				//$weightSum = $data_elem["weight"];
			}
			$prev=$elem["median"];
			$edge_lst_str.="EdgeID: ".$link["edgeID_lst"][$index]."\nSourceIP: ".$link["src_ip_lst"][$index]."\nDestIP: ".$link["dest_ip_lst"][$index]."\nMedian: ".$link["median_lst"][$index]."</BR>\n";
		}
		$edge_lst_str.="</P>";
		return $edge_lst_str;
	}
  }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



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
	
?>


<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Edge Info</title>
        <script src="http://code.jquery.com/jquery-latest.js"></script>
		<script type="text/javascript" src="../js/jquery.tablesorter.min.js"></script> 
    </head>
    <body>
        <?php
        	echo edge_info($src_pop,$dst_pop);
        ?>
    </body>
</html>