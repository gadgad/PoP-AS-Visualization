<?php

// http://localhost/PoPVisualizer/more_info.php?src_pop=000209.1066447842&dst_pop=000209.1066564486&threshold=2
require_once("bin/load_config.php");
require_once("bin/idgen.php");
include_once("verify.php");

if(!isset($_REQUEST["src_pop"]) || 
	!isset($_REQUEST["dst_pop"]) ||
	!isset($_REQUEST["threshold"]) ||
	!isset($_REQUEST["inter_con"]) ||
	!isset($_REQUEST["intra_con"]) ||
	!isset($_REQUEST["QID"])) {
		echo "missing parameters!";
		die();
	}

define('INTER_CON',$_REQUEST["inter_con"]);
define('INTRA_CON',$_REQUEST["intra_con"]);
define('STDEV_THRESHOLD',$_REQUEST["threshold"]);

$src = $_REQUEST["src_pop"];
$dst = $_REQUEST["dst_pop"];

$queryID = $_REQUEST["QID"];
$idg = new idGen($queryID);
$baseDir='queries/'.$idg->getDirName();
$as_info_dir = 'xml/ASN_info.xml';

$pop_xml = simplexml_load_file($baseDir."/pop.xml");
$EDGES_xml = simplexml_load_file($baseDir."/edges.xml");
$asn_info_xml = simplexml_load_file($as_info_dir);

$ASN_LIST=array();
$EDGES=array();
$POP_2_LOC_MAP=array();
$LOC_2_POP_MAP=array();

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

$edge_str = $src.$dst;
$link = $EDGES[$edge_str];
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

$numOfEdges = $link["numOfEdges"];
//$edge_lst_str = "<P>Source PoP: ".$srcPOP." Dest PoP: ".$dstPOP."</P></BR>";
//$edge_lst_str.="<P>#Edges: ".$numOfEdges."</BR>";
$median_lst = array();
for($i=0;$i<$numOfEdges; $i++)
{
	$median_lst[] = array("median"=>$link["median_lst"][$i],"index"=>$i);
}
uasort($median_lst,'cmp');

$rs = new RunningStat();
$cluster_map = array();
$cluster_map[0] = array();
$cluster_id = 0;
foreach($median_lst as $elem)
{
	$index = $elem["index"];
	$x = $elem["median"];
	$rs->push($x); 
	if(abs($rs->mean() - $x) > STDEV_THRESHOLD*($rs->standardDeviation()))
	{
		// new physical link detected 
		$cluster_map[++$cluster_id] = array($index);
		$rs->clear();
		$rs->push($x);
	} else {
		$cluster_map[$cluster_id][] = $index;
	}
}
	
?>


<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Edge Info</title>
        <link rel="stylesheet" href="css/tablesort/blue/style.css" type="text/css" media="print, projection, screen" /> 
        <script src="http://code.jquery.com/jquery-latest.js"></script>
		<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
		<script type="text/javascript">
			$(document).ready(function() 
			    { 
			        $("#myTable3").tablesorter();   
			    } 
			); 
		</script>
    </head>
    <body>
    	<h3>Edge Details:</h3>
    	<table id="myTable1" class="tablesorter"> 
		<thead> 
		<tr> 
		    <th>Src/Dest</th> 
		    <th>ASN</th> 
		    <th>ISP Name</th> 
		    <th>Country</th> 
		    <th>PoPID</th> 
		</tr> 
		</thead> 
		<tbody>
		<tr> 
		    <td>Source</td> 
		    <td><?php echo $srcAS; ?></td> 
		    <td><?php echo $srcAS_info["ISPName"] ?></td> 
		    <td><?php echo $srcAS_info["Country"]?></td> 
		    <td><?php echo $srcPOP ?></td> 
		</tr> 
		<tr> 
		    <td>Destination</td> 
		    <td><?php echo $dstAS; ?></td> 
		    <td><?php echo $dstAS_info["ISPName"] ?></td> 
		    <td><?php echo $dstAS_info["Country"]?></td> 
		    <td><?php echo $dstPOP ?></td> 
		</tr>
		</tbody> 
		</table>
		<h3>Links Associated With This Edge:</h3>
		<table id="myTable2" class="tablesorter"> 
		<thead> 
		<tr> 
		    <th>Parameter</th> 
		    <th>Value</th> 
		</tr> 
		</thead> 
		<tbody>
		<tr> 
		    <td># of Logical Links</td> 
		    <td><?php echo $numOfEdges; ?></td> 
		</tr> 
		<tr> 
		    <td># of Physical Links (Estimation)</td> 
		    <td><?php echo $cluster_id+1; ?></td> 
		</tr>
		</tbody> 
		</table>
		<table id="myTable3" class="tablesorter"> 
		<thead> 
		<tr> 
			<th>#</th>
			<th>EdgeID</th>
		    <th>SrcIP</th> 
		    <th>DstIP</th> 
		    <th>Count</th> 
		    <th>Step</th> 
		    <th>Median</th>
		    <th>Est' ClusterID</th> 
		</tr> 
		</thead> 
		<tbody>
		<tr>
		<?php
		$param_lst = array(0=>"edgeID_lst",1=>"src_ip_lst",2=>"dest_ip_lst",3=>"count_lst",4=>"step_lst",5=>"median_lst");
		$id = 1;
		for($i=0;$i<=$cluster_id;$i++){
			foreach($cluster_map[$i] as $index){
				echo "<td>".$id++."</td>\n";
				foreach($param_lst as $param){
					echo "<td>".$link[$param][$index]."</td>\n";	
				}
				echo "<td>".($i+1)."</td>\n";
				echo "</tr>\n";
			}
			echo "</tr>\n";
		}
	    ?> 
		</tbody> 
		</table>
    </body>
</html>