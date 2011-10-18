<?php

// http://localhost/PoPVisualizer/edgeDetails.php?src_pop=000209.1066447842&dst_pop=000209.1066564486&QID=0b6f948d14f516e52dbe6f469a8dbbaf
// http://10.0.0.14/edgeDetails.php?src_pop=003356.0068768294&dst_pop=003356.0068768294&QID=0b6f948d14f516e52dbe6f469a8dbbaf&numOfEdges=60712
	require_once("verify.php");
	require_once("bin/load_config.php");
	require_once("bin/idgen.php");
	require_once("bin/DBConnection.php");
		
	if(!isset($_REQUEST["src_pop"]) || 
		!isset($_REQUEST["dst_pop"]) ||
		!isset($_REQUEST['numOfEdges']) ||
		!isset($_REQUEST["QID"])) {
			echo "missing parameters!";
			die();
		}
	
	$src = $_REQUEST["src_pop"];
	$dst = $_REQUEST["dst_pop"];
	$queryID = $_REQUEST["QID"];
	$numOfEdges = $_REQUEST['numOfEdges'];
	if (isset($_REQUEST["currPage"])){$currPage = $_REQUEST["currPage"]; }else $currPage = 0;
	
	$idg = new idGen($queryID);
	$queries = simplexml_load_file('xml/query.xml');
	$res = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');
	       
	if ($res!=FALSE){
		$queryBlade = (string)$res[0]->blade;		
	}else echo 'cant find the query in query.xml';
	
	// getting parameters for connection
	$bladei = $GLOBALS["Blade_Map"][$queryBlade];
	$host = (string)$bladei["host"];
	$port = (int)$bladei["port"];
	$user = (string)$bladei["user"];
	$database = (string)$bladei["write-db"];
	$pass = is_array($bladei["pass"])?"":(string)$bladei["pass"];
	
	// getting the AS of the src pop.
	$srcAS = intval(substr($src, 0,strrpos($src, '.')));
	$dstAS = intval(substr($dst, 0,strrpos($dst, '.')));
		
    $pageSize = EdgeDetailsNumOfRecords;
	$numOfPages = ceil($numOfEdges/$pageSize); 
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Edge Info</title>
        <link rel="stylesheet" href="css/tablesort/blue/style.css" type="text/css" media="print, projection, screen" /> 
        <script src="http://code.jquery.com/jquery-latest.js"></script>
		<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
		<script type="text/javascript" src="js/jquery.blockUI.js"></script>
		<script type="text/javascript">
	
			$(document).ready(function() 
			    {  
				    $("#myTable3").tablesorter();			   
			    } 
			); 
		</script>
    </head>
    <body>

	<table id="myTable3" class="tablesorter"> 
			<thead> 
				<tr> 
					<?php
						// connecting to the DB					
						$mysqli = new DBConnection($host,$user,$pass,$database,$port,5);
						if ($mysqli->connect_error) {
						   echo 'Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error;
						   die();
						}
							
						// parse 'pageSize' records from DB
						$numOfPages = ceil($numOfEdges/$pageSize);
						$sql = "select AutoIndex,edgeid,SourceIP,DestIP,step,count,Median from `".$database."`.`".$idg->getEdgeTblName()."` where Source_PoPID=".$src." and Dest_PoPID=".$dst;
						$pageOffset = $currPage*$pageSize;
						$query = $sql." limit $pageOffset,$pageSize";
						$result = $mysqli->query($query) or die("SQL Query Failed.");
						
						// getting the attributes of the table
						$finfo = $result->fetch_fields();
						 foreach ($finfo as $val) {
						 	echo '<th>'.$val->name.'</th>';	
        				}
        						
					?>
				</tr> 
				</thead> 
				<tbody> 
			<?php
					// render table content
					$numOfRecords = $result->num_rows;
					for($x = 0 ; $x < $numOfRecords ; $x++){
					    $row = $result->fetch_assoc();
					    echo '<tr>';
				        foreach($row as $key => $value){
							echo '<td>'.$value.'</td>';	
						}
						echo '</tr>';
					}	
				    $mysqli->close();
		    ?>
		    </tbody>
		</table>
	</body>
</html>	