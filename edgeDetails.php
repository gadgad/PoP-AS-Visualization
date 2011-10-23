<?php
/*
 * shows info from DB for a specific edge (src_pop+dst_pop) 
 */

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
		$tableID = (string)$res[0]->tableID;		
	}else echo 'cant find the query in query.xml';
	
	if(array_key_exists("links", $GLOBALS["DataTables"])){
		$linksTable = $GLOBALS["DataTables"]["links"]; 
		if(isset($linksTable)){
			
			if(isset($linksTable["blade"]))
				$queryBlade = $linksTable["blade"];
			
			$schema = $linksTable["schema"];
			$links_prefix = $linksTable["prefix"];
			$year = (string)$res[0]->year;
			$week = (string)$res[0]->week;
			$linksTblName = $links_prefix.'_'.$year.'_'.$week.'_'.$tableID;
			$fields = implode(',',$linksTable["field"]);
			trim($fields,",");
			$sql = $sql = "select ".$fields." from `".$schema."`.`".$linksTblName."` where Source_PoPID=".$src." and Dest_PoPID=".$dst;
		}
	}
	
	
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
        <link rel="stylesheet" href="css/visual.css" type="text/css" />
        <link rel="stylesheet" href="css/tablesort/blue/style.css" type="text/css" media="print, projection, screen" /> 
        <script src="http://code.jquery.com/jquery-latest.js"></script>
		<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
		<script type="text/javascript" src="js/jquery.blockUI.js"></script>
		<script type="text/javascript">
			
			(function($) {
				  var cache = [];
				  // Arguments are image paths relative to the current page.
					$.preLoadImages = function() {
					    var args_len = arguments.length;
					    for (var i = args_len; i--;) {
					      var cacheImage = document.createElement('img');
					      cacheImage.src = arguments[i];
					      cache.push(cacheImage);
					    }
					  }
			})(jQuery)

			$.preLoadImages("images/ajax-bar.gif");
			
			$(document).ready(function() 
			    { 	
			    	$('#tblContainer').html('<p><img class="ajaxLoader" src="images/ajax-bar.gif"/></p>');
			    	$('#tblContainer').load('edgeTable.php?loadTable=true&src_pop=<?php echo $src?>&dst_pop=<?php echo $dst?>&QID=<?php echo $queryID; ?>&numOfEdges=<?php echo $numOfEdges; ?> #myTable3').fadeIn("fast");
				    $('#tblContainer').ajaxComplete(function() {
				    	//$('#myTable3').addClass('tablesorter');
				    	$('#myTable3').tablesorter();
				    });			   
			    } 
			); 
			
			function prevPage(currPage){
				var page = parseInt(currPage);
				if (page>0){
					page--;
					$('#navigate').html('<button type="submit" onclick="prevPage(this.value)" value="'+page+'">prev</button> page '+page+' of <?php echo $numOfPages?> <button type="submit" onclick="nextPage(this.value)" value="'+page+'">next</button>');
					$('#tblContainer').html('<p><img class="ajaxLoader" src="images/ajax-bar.gif"/></p>');
			    	$('#tblContainer').load('edgeTable.php?loadTable=true&src_pop=<?php echo $src?>&dst_pop=<?php echo $dst?>&QID=<?php echo $queryID; ?>&numOfEdges=<?php echo $numOfEdges; ?>&currPage='+page+' #myTable3').fadeIn("fast");
				    $('#tblContainer').ajaxComplete(function() {
				    	//$('#myTable3').addClass('tablesorter');
				    	$('#myTable3').tablesorter();
				    });			   
					//$('#myTable3').html('<p><img class="ajaxLoader" src="images/ajax-bar.gif"/></p>');
				    //$('#myTable3').load('edgeDetails.php?loadTable=true&src_pop=<?php echo $src?>&dst_pop=<?php echo $dst?>&QID=<?php echo $queryID ?>&numOfEdges=<?php echo $numOfEdges; ?>&currPage='+page+' #myTable3').fadeIn("slow");	
				}
			}
			
			function nextPage(currPage){
				var page = parseInt(currPage);
				if (page<<?php echo $numOfPages?>){
					page++;
					$('#navigate').html('<button type="submit" onclick="prevPage(this.value)" value="'+page+'">prev</button> page '+page+' of <?php echo $numOfPages?> <button type="submit" onclick="nextPage(this.value)" value="'+page+'">next</button>');
					$('#tblContainer').html('<p><img class="ajaxLoader" src="images/ajax-bar.gif"/></p>');
			    	$('#tblContainer').load('edgeTable.php?loadTable=true&src_pop=<?php echo $src?>&dst_pop=<?php echo $dst?>&QID=<?php echo $queryID; ?>&numOfEdges=<?php echo $numOfEdges; ?>&currPage='+page+' #myTable3').fadeIn("fast");
				    $('#tblContainer').ajaxComplete(function() {
				    	//$('#myTable3').addClass('tablesorter');
				    	$('#myTable3').tablesorter();
				    });	
					//$('#myTable3').html('<p><img class="ajaxLoader" src="images/ajax-bar.gif"/></p>');
				    //$('#myTable3').load('edgeDetails.php?loadTable=true&src_pop=<?php echo $src?>&dst_pop=<?php echo $dst?>&QID=<?php echo $queryID ?>&numOfEdges=<?php echo $numOfEdges; ?>&currPage='+page+' #myTable3').fadeIn("slow");	
				}				
			}
			
		</script>
    </head>
    <body id="bodddy">
    	<h3>Edge Details:</h3>
    	<table id="myTable1" class="tablesorter"> 
		<thead> 
		<tr> 
		    <th>Src/Dest</th> 
		    <th>ASN</th> 
		    <th>Country</th> 
		    <th>ISP Name</th> 
		    <th>PoPID</th> 
		</tr> 
		</thead> 
		<tbody>
		<tr> 
		    <td>Source</td> 
		    <?php
		     
		    	$as_info_dir = 'xml/ASN_info.xml';
				$asn_info_xml = simplexml_load_file($as_info_dir);
				// writing to the table the details of the source pop
				$SAS = $asn_info_xml->xpath('/DATA/ROW[ASNumber="'.$srcAS.'"]');
				if ($SAS!=FALSE){			
					$SAS = $SAS[0];
					echo"<td>".$SAS->ASNumber."</td>";					
					echo"<td>".$SAS->Country."</td>";
					echo"<td>".$SAS->ISPName."</td>";	
					echo "<td>".$src."</td>";
				}else echo 'source ASN not found in ASN_info. ';
		    ?>
		</tr>    
		   
		<tr> 
		    <td>Destination</td> 
		    <?php
		    // writing to the table the details of the destination pop
		    	$DAS = $asn_info_xml->xpath('/DATA/ROW[ASNumber="'.$dstAS.'"]');
		    	if ($DAS!=FALSE){
		    		$DAS = $DAS[0];
					echo"<td>".$DAS->ASNumber."</td>";					
					echo"<td>".$DAS->Country."</td>";
					echo"<td>".$DAS->ISPName."</td>";
					echo "<td>".$dst."</td>";
		    	}else echo 'dest ASN not found in ASN_info. ';	
		    ?>
		</tr>
		</tbody> 
		</table>
		
		<?php
			if(isset($linksTable)){
				echo '<table id="myTable2" class="tablesorter"><thead><tr>\n';
				// connecting to the DB					
				$mysqli = new DBConnection($host,$user,$pass,$database,$port,5);
				if ($mysqli->connect_error) {
				   echo 'Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error;
				   die();
				}
					
				// execute the query
				$result = $mysqli->query($sql) or die("SQL Query Failed.");
				
				// getting the attributes of the table
				$finfo = $result->fetch_fields();
				 foreach ($finfo as $val) {
				 	echo '<th>'.$val->name.'</th>';	
				}
				echo "</tr></thead></tbody>\n";	
		
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
				echo "</tbody></table>\n";	
			    $mysqli->close();
		    }
		?>
		
		<h3>Links Associated With This Edge:</h3>
		<p># of Logical Links:<?php echo $numOfEdges; ?></p>
		
		<!-- if there is more than one page - insert navigation buttons. -->
		<div id="navigate">
			<?php
				if ($numOfPages>1){
					echo '<button type="submit" onclick="prevPage(this.value)" value="'.$currPage.'">prev</button> page '.$currPage.' of '.$numOfPages.' <button type="submit" onclick="nextPage(this.value)" value="'.$currPage.'">next</button>';
				} 			
			?>
		</div>
		
		<div id="tblContainer">
			
		</div>
	
    </body>
</html>