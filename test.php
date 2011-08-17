<?php
	include("bin/load_config.php");
	include("verify.php");	
?>


<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Pop/AS Visualizer</title>
        <!-- <script src="http://code.jquery.com/jquery-latest.js"></script> -->
        <script src="js/jquery-1.6.2.min.js" type="text/javascript"></script>
        <script type="text/javascript" src="js/loadData.js"></script>
        <link rel="stylesheet" href="css/jquery.multiselect2side.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="css/visual.css" type="text/css" media="screen" />
		<script type="text/javascript" src="js/jquery.multiselect2side.js" ></script>
          
          
          <script type="text/javascript">
			 // Test connection to blade ---------------------------------------------------          
             function testConnection() {             	                
                $.post("query_backend_keren.php", { func: "testConnection", blade: $("#mySelect").val() },
                    function(data){
                             var result = data.result;
                             if (data.type =="ERROR")
                             	{alert(result);}                             
                    }, "json");	
             }
             
             function getTables(){
             	if ($("#mySelect").val()!="" && $("#year").val()!="" && $("#week").val()!=""){                                   
                        $.post("query_backend_keren.php", {func: "showTables", blade: $("#mySelect").val(),
                         year: $("#year").val(),week: $("#week").val()},
                        function(data){                        			
	                         
	                         var allEdges = data.edge;	                      
	                         if (allEdges!= false){
	                         	var edges = allEdges.split(" ");
	                         	for(i = 0; i < edges.length; i++){								
									$("#Edge").append("<option>" + edges[i] + "</option> "); 									
								 }
	                         }else {$("#Edge").append("<option>No tables available</option> ");}	                         	                        
	                         
	                         var allPops = data.pop;
	                         if (allPops!=false){
	                         	var pops = allPops.split(" ");
	                         	for(i = 0; i < pops.length; i++){								
									$("#PoP").append("<option>" + pops[i] + "</option> ");
								 }
	                         }else {$("#PoP").append("<option>No tables available</option> ");}
	                         
                        }, "json");	
                    }
             } 
             
             function updateTable(queryID){
             	$("#My_queries").append("<p text-align:center>Query" + queryID + "is now running</p> ");
             	// add a table line ?             		
             }
                                  
            $(document).ready(function() {                   
                    $("#mySelect").change(function() {
                    	if ($("#mySelect").val() != "Select blade"){
                       		testConnection();
                       	}
                    });
            });
                                                          
            //get possible tables ----------------------------------------------------------------------
            $(document).ready(function() {              	 
                $("#week").change(function() {
                	getTables();                    
                });              
            });
            
            $(document).ready(function() {              	 
                $("#year").change(function() {
                	getTables();                    
                });              
            });
            
            
            // get all relevant AS by parameters TODO: change click
            $(document).ready(function() {
                    $("#getAS").click(function() {                                                           
                        $.post("query_backend_keren.php", {func: "getASlist", blade: $("#mySelect").val(), edge: $("#Edge").val() , pop: $("#PoP").val()},
                        function(data){
                        	                        			
	                         var allAS = data.result;
							 var AS = allAS.split("*");	
							 							 	                         	                         
	                         for(i = 0; i < AS.length; i++){
	                         	var tmp = AS[i].split(" ");								
								$("#searchable").append('<option value="' + tmp + '">' + AS[i] + "</option> "); 								
							 }
	                         
                        }, "json");	           
                    });                    
            });
            
            
            $().ready(function() {
				$('#searchable').multiselect2side({'search': 'Search: '});
			});
           
            
            // send the query to server
            $(document).ready(function() {
                    $("#sendQuery").click(function() {                                                           
                        $.post("query_backend_keren.php", {func: "sendQuery", blade: $("#mySelect").val() ,
                         edge: $("#Edge").val() , pop: $("#PoP").val(), username: <?php echo '"'.$username.'"'?>, as: $("#searchable").val() },
                         function(data){                        			
	                         updateTable(data.queryID);
	                         // TODO: update table?	                         	                         
                        }
                         ,"json");                                                    
                    });
            });
            
                                 
     		// Completed - open map page
     		$(document).ready(function() {
                    $("#QstatusC").click(function() {                                                           
                        $.post("visual_frontend.php", {func: "showMap", QID: $("#QstatusC").val()},"json");                                                                             
                    });
            });
  
     		       
            // cancels the query
            $(document).ready(function() {
                    $("#abort").click(function() {                                                           
                        $.post("user_query_managment.php", {func: "abort", query: $("#abort").val(), username: <?php echo '"'.$username.'"'?> },"json");
                        // -->> delete row from table;                                      	
                    });
            });
                        
            </script>                  
    </head>

    
    <body>        
        
        <div id="container">

            <div id="header">
            	<p>
	                <h5 style="text-align: left; margin-left: 5px">Welcome, <?php echo $username; ?>                	
	                	<a href="logout.php"> <u>Logout</u></a>	
	                </h5>
                	<h1 id="main-title">PoP/AS Visualizer</h1>
                </p>                
            </div>
						                       
            <div id="user-select">          
            <h3 style="text-decoration: underline;text-align: center; size: 4; color: teal">Make a new query</h3>
                
                <form style="font-size:14px;">
                	<p class="selection-header">Select blade</p>                    
                    <p class="selection-text">Blade:</p>
                    <select id="mySelect">
                    	<option value="">Select blade</option>
                            <?php                                                          
                            foreach($Blades as $blade)
                                    {
                                        $name = $blade["@attributes"]["name"];
										var_dump($name);
                                        if($name!="" && $Blade_Map[$name]["db"]=="DIMES_DISTANCES")
                                                echo "<option>$name</option>";
                                    }
                            ?>
                    </select>          
                </form>                               
                
                <form id="AS" name="get AS list" style="font-size:14px;">                               
                    
                    <p class="selection-header">Select date</p>       
                    <div align="left" class="selection-text">Year  :                       
                        <select id="year" >
                            <option value="">Select year</option>
                            <?php
                            	$currentYear = date("Y");
	                            for($i = 2004; $i <= $currentYear; $i++){
	                            	echo "<option>".$i."</option>";																 								 
								 }	
                            ?>                            
                        </select>
                    </div>

                     <div align="left" class="selection-text">Week:
                        <select id="week">                               
                            <option selected="selected" value="">Select week</option>
                            <?php                            	
	                            for($i = 1; $i <= 52; $i++){
	                            	echo "<option>".$i."</option>";																 								 
								 }	
                            ?>                             
                        </select>
                    </div>
                    
                    <p class="selection-header">Select table</p>                                       
               		<div align="left" class="selection-text">PoP  :                       
                        <select id="PoP" >
                            <option value="">Select PoP table</option>                            
                        </select>
                    </div>

                     <div align="left" class="selection-text">Edge:
                        <select id="Edge">                               
                            <option selected="selected" value="">Select edge table</option>                            
                        </select>
                    </div>
               		
               		
                    <input id="getAS" type="button" value="Get AS list!" style="margin-left: 20px; margin-top: 10px"/>
                    <br></br>
                    
                    <div>
                    	<select multiple='multiple' id='searchable' name="searchable[]">
                    		
                    	</select>                    	
                    
                    </div>
                    
                    <input id="sendQuery" type="submit" value="Send query!" style="margin-left: 20px; margin-top: 10px"/>    
                    
                </form>
              
            </div>
            
            <div id="My_queries" style="margin-right: 3%;background-color:#EEEFEE;width:55%;height:85%;float: right; clear:right; text-align:center">
                <h3>My queries</h3>                	
                <br></br>                

				<table class="imagetable" style="alignment-baseline: central">				
				
				<?php
					$queries = simplexml_load_file("xml\query.xml");
					$result = $queries->xpath('/DATA/QUERY[users/user="'.$username.'"]');					
					if($result!=FALSE)
					{
						echo "<tr>";
						echo "<th>Edge table</th><th>PoP table</th><th>Status</th><th>Delete</th>";
						echo "</tr>";
						foreach ($result as $i => $value) {												
							echo "<tr>";							
							echo "<td>".$result[$i]->EdgeTbl."</td>" . "<td>".$result[$i]->PopTbl."</td>" . "<td>";
							if ($result[$i]->lastKnownStatus=="running"){
								echo "running";
								
								//add code to check query status
								
							}elseif ($result[$i]->lastKnownStatus=="completed"){
								echo '<button type="button" id=QstatusC value="'.$result[$i]->queryID.'">completed</button>';	
							}else {
								echo 'ambigues status';
							}
							echo "</td>" . '<td> <button type="submit" id="abort" value="'.$result[$i]->queryID.'">X</button></td>';
							// --->>>> change id to unique value ?
							echo "</tr>";
						} 
					}else echo "you have no queries yet... ";
				?>																
				<!-- enable adding a new row when a query is sent-->
				</table>                
            </div>
            
            <div id="footer">
                Copyright © 2011 DIMES
            </div>
            
         </div>
    </body>
</html>