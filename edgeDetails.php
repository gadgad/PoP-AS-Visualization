<?php

// http://localhost/PoPVisualizer/edgeDetails.php?src_pop=000209.1066447842&dst_pop=000209.1066564486&QID=0b6f948d14f516e52dbe6f469a8dbbaf
// http://10.0.0.14/edgeDetails.php?src_pop=003356.0068768294&dst_pop=003356.0068768294&QID=0b6f948d14f516e52dbe6f469a8dbbaf&currPage=0
	require_once("verify.php");
	require_once("bin/load_config.php");
	require_once("bin/idgen.php");
	require_once("bin/DBConnection.php");
		
	if(!isset($_REQUEST["src_pop"]) || 
		!isset($_REQUEST["dst_pop"]) ||
		!isset($_REQUEST["currPage"]) ||
		!isset($_REQUEST["QID"])) {
			echo "missing parameters!";
			die();
		}
	
	$src = $_REQUEST["src_pop"];
	$dst = $_REQUEST["dst_pop"];
	$queryID = $_REQUEST["QID"];
	$currPage = $_REQUEST["currPage"];
	
	$idg = new idGen($queryID);
	$queries = simplexml_load_file('xml/query.xml');
	$res = $queries->xpath('/DATA/QUERY[queryID="'.$queryID.'"]');
	       
	if ($res!=FALSE){
		$queryBlade = (string)$res[0]->blade;
		//$tableID = (string)$res[0]->tableID;		
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
		
	// connecting to the DB						
	$mysqli = new DBConnection($host,$user,$pass,$database,$port,5);
	if ($mysqli->connect_error) {
	   echo 'Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error;
	   die();
	}
	
	$query = "select count(*) from `".$database."`.`".$idg->getEdgeTblName()."` where Source_PoPID=".$src." and Dest_PoPID=".$dst;
	if ($resulti = $mysqli->query($query)){
		// getting the number of edges. 	 
    	$row = $resulti->fetch_assoc(); 
        foreach($row as $key => $value){
			$numOfEdges = $value;	
		}   
		$resulti->close();	   
    }else echo 'bad query result';
    
    $pageSize = 100; // TODO : get page size from globals
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

			$.preLoadImages("images/ajax-loader.gif");
			
			$(document).ready(function() 
			    { 	
			    	$('#myTable3').load('edgeDetails.php?loadTable=true&src_pop=<?php echo $src?>&dst_pop=<?php echo $dst?>&QID=<?php echo $queryID ?>&currPage=0 #myTable3').fadeIn("fast");
				    $("#myTable3").tablesorter();   
			    } 
			); 
			
			function prevPage(currPage){
				var page = parseInt(currPage);
				if (page>0){
					page-=1;
					$('#navigate').html('<button type="submit" onclick="prevPage(this.value)" value="'+page+'">prev</button> '+page+' <button type="submit" onclick="nextPage(this.value)" value="'+page+'">next</button>');
					$('#myTable3').html('<p><img src="images/ajax-loader.gif"/></p>');
				    $('#myTable3').load('edgeDetails.php?loadTable=true&src_pop=<?php echo $src?>&dst_pop=<?php echo $dst?>&QID=<?php echo $queryID ?>&currPage='+page+' #myTable3').fadeIn("slow");	
				}
			}
			
			function nextPage(currPage){
				var page = parseInt(currPage);
				if (page<<?php echo $numOfPages?>){
					page+=1;
					$('#navigate').html('<button type="submit" onclick="prevPage(this.value)" value="'+page+'">prev</button> '+page+' <button type="submit" onclick="nextPage(this.value)" value="'+page+'">next</button>');
					$('#myTable3').html('<p><img src="images/ajax-loader.gif"/></p>');
				    $('#myTable3').load('edgeDetails.php?loadTable=true&src_pop=<?php echo $src?>&dst_pop=<?php echo $dst?>&QID=<?php echo $queryID ?>&currPage='+page+' #myTable3').fadeIn("slow");	
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
		    <th>ISP Name</th> 
		    <th>Country</th> 
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

	<table id="myTable3" class="tablesorter"> 
		<thead> 
			<tr> 
				<?php
					// getting the attributes of the table
					$sql = "select COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where table_name='".$idg->getEdgeTblName()."' and table_schema='".$database."'";
					if ($result = $mysqli->query($sql)){		
					// processing the query result 
			    	while ($row = $result->fetch_assoc()) {	
				        foreach($row as $key => $value){
							echo '<th>'.$value.'</th>';	
						}	
				    }	     		     		     		    
					$result->close();	   
			    }else echo 'bad query result';				
				?>
			</tr> 
			</thead> 
			<tbody> 
		<?php
		   	if(isset($_REQUEST["loadTable"])){
				// when the next/prev button is pressed, this code generates the table content again.
				$pageSize = 100; // TODO : get page size from globals
				$numOfPages = ceil($numOfEdges/$pageSize);
				$sql = "select * from `".$database."`.`".$idg->getEdgeTblName()."` where Source_PoPID=".$src." and Dest_PoPID=".$dst;
					
				// parse 'pageSize' records from DB
				$pageOffset = $currPage*$pageSize;
				$query = $sql." limit $pageOffset,$pageSize";
				$result = $mysqli->query($query) or die("SQL Query Failed.");
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
			}
	    ?>
	    </tbody>
	</table>
    </body>
</html>