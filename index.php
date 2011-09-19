<?php
	include_once("bin/load_config.php");
	include_once("verify.php");	
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
		<script type="text/javascript" src="js/jquery-blink.js" ></script>
          
          <script type="text/javascript">
			 // Test connection to blade 
             function testConnection() {
             	$.preLoadImages("images/ajax-loader.gif");
             	$.preLoadImages("images/icon_OK.png");
             	$("#testStatus").remove();
				$('#blade').after('<img id="testStatus" class="validator" src="images/ajax-loader.gif"/>');
             	$('#AS input').addClass('formGrayOut').attr('disabled','disabled');           	                
                $.post("query_backend.php", { func: "testConnection", blade: $("#blade").val() },
                    function(data){
                             var result = data.result;
                             if(data!=null){
	                             if (data.type =="ERROR"){
	                             	$('#AS input#blade').removeClass('formGrayOut').removeAttr('disabled');
	                             	alert(result);
	                             } else {
	                             	$("#testStatus").replaceWith('<img id="testStatus" class="validator" src="images/icon_OK.png"/>');
	                             	$('#AS input').removeClass('formGrayOut').removeAttr('disabled');
	                             }
	                          }                            
                    }, "json");	
             }
             
             function getTables(){
             	if ($("#blade").val()!="" && $("#year").val()!="" && $("#week").val()!=""){
             		
             		$.preLoadImages("images/ajax-loader.gif");
					$('#button-wrap-t').html('<p><img src="images/ajax-loader.gif"/></p>');                                   
					
                    $.post("query_backend.php", {func: "showTables", blade: $("#blade").val(),
                     year: $("#year").val(),week: $("#week").val()},
                    function(data){                        			
                         
                         if (data.type=="GOOD"){                    	
                         
	                         if (data.edge!= ""){
	                         	var allEdges = data.edge;	                                               
	                         	var edges = allEdges.split(" ");
	                         	for(i = 0; i < edges.length; i++){								
									$("#Edge").append("<option>" + edges[i] + "</option> "); 									
								 }
	                         }else {$("#Edge").append("<option>No tables available</option> ");}	                         	                                                 
	                         
	                         if (data.pop!=""){
	                         	var allPops = data.pop;
	                         	var pops = allPops.split(" ");
	                         	for(i = 0; i < pops.length; i++){								
									$("#PoP").append("<option>" + pops[i] + "</option> ");
								 }
	                         }else {$("#PoP").append("<option>No tables available</option> ");}
	                                                  
	                         if (data.popIP!=""){
	                         	var allPops2 = data.popIP;
	                         	var pops2 = allPops2.split(" ");
	                         	for(i = 0; i < pops2.length; i++){								
									$("#popIP").append("<option>" + pops2[i] + "</option> ");
								 }
	                         }else {$("#popIP").append("<option>No tables available</option> ");}	                        
	                    	
	                    }else{
	                    	$("#Edge").html("<option>Connection error</option> ");
	                    	$("#PoP").html("<option>Connection error</option> ");
	                    	$("#popIP").html("<option>Connection error</option> ");
	                    }
	                    $('#button-wrap-t').html('<input id="getTables" type="button" value="Get tables" style="margin-left: 20px; margin-top: 10px"/>');
	                    }, "json");	
                }
             } 
             
             
             /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
             
             var queryID;    
             function updateTable(){
             	//$("#My_queries").append("<p text-align:center>Query " + queryID + " is now running with pid: "+myNameSpace.processID+"</p>");
             	$('#queryTable').load('index.php #queryTable').fadeIn("slow");           		
             }
             
             var error_counter;
             function stageOne(){
             	error_counter = 0;
             	//$.preLoadImages("images/ajax-loader.gif");
             	$("#sendQueryStatus").remove();
             	if(!$("#searchable").val()){
             		$("#sendQuery").after('<p id="sendQueryStatus" class="sendQueryValidator" style="color:red">complete the form first!</p>');
             		return;
             	}
				$("#sendQuery").after('<img id="sendQueryStatus" class="validator" src="images/ajax-loader.gif"/>');
             	$.post("query_backend.php", {func: "sendQuery", stage:1, blade: $("#blade").val() ,
                         edge: $("#Edge").val(), pop: $("#PoP").val(), popIP: $("#popIP").val(), year: $("#year").val(), week: $("#week").val(), username: <?php echo '"'.$username.'"'?>, as: $("#searchable").val() },
                         function(data){
                         	if (data==null || data.type=="ERROR"){
                         		$("#My_queries").append('<p style="color:red">ERROR - The query did not run successfuly</p>');
                         		$("#sendQueryStatus").remove();
                         		if(data!=null) $("#My_queries").append('<p style="color:red">'+data.result+'</p>');
                         	}else if(data.type=="ALL_COMPLETE"){
                         		$("#My_queries").append("<p text-align:center>"+data.result+"</p> ");
                         		$("#sendQueryStatus").remove();
                         	}else if(data.type=="GOOD"){
                         		queryID=data.queryID;
	                     		$("#sendQueryStatus").remove();
	                     		updateTable();
                         	} else {
                         		if(data.type!="STAGE1_COMPLETE"){
                         			$("#My_queries").append('<p style="color:red">ASSERTION ERROR</p>');
                         			$("#sendQueryStatus").remove();
                         		} else {
                         			stageTwo();
                         		}
                         	}                         	                        				                         	                         	                         
                        }
                         ,"json");     
             }
                 
             function stageTwo(){
             	$.post("query_backend.php", {func: "sendQuery", stage:2, blade: $("#blade").val() ,
                         edge: $("#Edge").val(), pop: $("#PoP").val(), popIP: $("#popIP").val(), year: $("#year").val(),week: $("#week").val(), username: <?php echo '"'.$username.'"'?>, as: $("#searchable").val() },
                         function(data){
                         	if (data==null || data.type=="ERROR"){
                         		$("#My_queries").append('<p style="color:red">ERROR - The query did not run successfuly</p>');
                         		if(data!=null) $("#My_queries").append('<p style="color:red">'+data.result+'</p>');
                         		$("#sendQueryStatus").remove();
                         	} else {
                         		if(data.type!="STAGE2_COMPLETE"){
                         			$("#My_queries").append('<p style="color:red">ASSERTION ERROR</p>');
                         			$("#sendQueryStatus").remove();
                         		} else {
	                     			stageThree();
                         		}
                         	}                         	                        				                         	                         	                         
                        }
                         ,"json");
             }
             
             function stageThree(resendQuery,queryID)
             {
             	if(error_counter>=3) {
             		$("#My_queries").append('<p style="color:red">ERROR - The query did not run successfuly</p>');
             		$("#sendQueryStatus").remove();
             		if(resendQuery) $('#queryTable').html('');
             		return;
             	}
             	var properties1 =  {func: "resendQuery", query: queryID, stage:3,  username: <?php echo '"'.$username.'"'?> };
             	var properties2 = {func: "sendQuery", stage:3, blade: $("#blade").val() ,
                         edge: $("#Edge").val(), pop: $("#PoP").val(), popIP: $("#popIP").val(), year: $("#year").val(),week: $("#week").val(), username: <?php echo '"'.$username.'"'?>, as: $("#searchable").val() };
             	$.post("query_backend.php", ((resendQuery)? properties1 : properties2) ,
                         function(data){
                         	if (data==null || data.type=="ERROR"){
                         		//$("#My_queries").append('<p style="color:red">ERROR - The query did not run successfuly</p>');
                         		//if(data!=null) $("#My_queries").append('<p style="color:red">'+data.result+'</p>');
                         		error_counter++;
                         		stageThree(resendQuery,queryID);
                         	} else {
                         		if(data.type!="GOOD"){
                         			$("#My_queries").append('<p style="color:red">ASSERTION ERROR</p>');
                         			$("#sendQueryStatus").remove();
                         		} else {
                         			queryID=data.queryID;
	                     			$("#sendQueryStatus").remove();
	                     			updateTable();
                         		}
                         	}                         	                        				                         	                         	                         
                        }
                         ,"json");
             }
             
             ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			function resendQuery(queryID){
				error_counter = 0;
				$.preLoadImages("images/ajax-loader.gif");
             	$('#queryTable').html('<p><img src="images/ajax-loader.gif"/></p>');  				
             	$.post("query_backend.php", {func: "resendQuery", query: queryID, stage:2,  username: <?php echo '"'.$username.'"'?> },
             	function(data){
		     		if (data==null || data.type=="ERROR"){
                 		$("#My_queries").append('<p style="color:red">ERROR - The query did not run successfuly</p>');
                 		if(data!=null) $("#My_queries").append('<p style="color:red">'+data.result+'</p>');
                 		$("#sendQueryStatus").remove();
                 	} else {
                 		if(data.type!="STAGE2_COMPLETE"){
                 			$("#My_queries").append('<p style="color:red">ASSERTION ERROR</p>');
                 			$("#sendQueryStatus").remove();
                 		} else {
                 			stageThree(true,queryID);
                 		}
                 	}                         
             	}
             	,"json");
			}
			
			
            function abort(queryID){             	
             	//$('#queryTable').fadeOut('fast');
             	$.preLoadImages("images/ajax-loader.gif");
             	$('#queryTable').html('<p><img src="images/ajax-loader.gif"/></p>');  				
             	$.post("user_query_managment.php", {func: "abort", query: queryID, username: <?php echo '"'.$username.'"'?> },
             	function(data){
             		if(data.type=="GOOD"){
             			updateTable();
             		}
             		if (data.type =="ERROR")
                     	{alert(data.result);}
             	}
             	,"json");
                                                  	
             }
            
            function pool_pq_status(pid){
            	if(globalData.pq_running==true){
            		$.post("query_backend.php", { func: "pq-status", blade: globalData.blade },
					function(data,textStatus){
						if(data!=null) {
							if(data.type == "ERROR"){
								globalData.pq_running==false;
								clearInterval(globalData.interval);
								//alert(data.result);
								$("#My_queries").append('<p style="color:red">ERROR - '+data.result+'</p>');
								$(".checkStatus").each(function(index) {
			         				var queryID = $(this).attr('id');
			         				$("#"+queryID).html('error');
			         			});
								return;
							}
							if(data.type == "FINISHED"){
								globalData.pq_running==false;
								clearInterval(globalData.interval);
								updateTable();
								return;
							}
						}
					}, "json");						
            	} 	
            }
            
            $(document).ready(function() {
            	$("#My_queries").bind('update', function(e, data) {
             		$(".checkStatus").each(function(index) {
         				var queryID = $(this).attr('id');
         				$("#"+queryID).html('<p class="blink">checking...</p>');
         			});
            		globalData.interval = setInterval( "pool_pq_status("+data.pid+")" , 5000 );  // pool with 5-sec intervals
            	});        
			});
			                      
            $(document).ready(function() {                   
                    $("#blade").change(function() {
                    	if ($("#blade").val() != "Select blade"){
                       		//testConnection();
                       	}
                    });
            });
                                                          
            //get possible tables 
            $(document).ready(function() {              	 
                $("#getTables").click(function() {
                	getTables();                    
                });              
            });
            
          
            $(document).ready(function() {              	 
                $("#year").change(function() {                	
                	if ($("#year").val()!=""){
                		$.post("query_backend.php", {func: "getWeeks", blade: $("#blade").val(),year: $("#year").val()},
                        function(data){
                        	if (data.type=="GOOD"){	
								var weeks = data.weeks;		            
								if (weeks!=null){
		                         	for(i = 1; i < weeks.length; i++){								
										$("#week").append("<option>" + weeks[i] + "</option> "); 									
									 }
	                         	}else {$("#week").append("<option>No weeks available</option> ");}
                         	}else {$("#week").html("<option>Connection error</option> ");}		                        	                        			
	                         
                        }, "json");	
                	}					                	            
                });              
            });
                     
                                    
            $(document).ready(function() {
                    $("#getAS").click(function() {
                    	$.preLoadImages("images/ajax-loader.gif");
  						$('#button-wrap').html('<p><img src="images/ajax-loader.gif"/></p>');                                                           
                        $.post("query_backend.php", {func: "getASlist", blade: $("#blade").val(), edge: $("#Edge").val() , pop: $("#PoP").val()},
                        function(data){
							
							if (data.type=="GOOD"){
								if (data.result==""){
									$('#button-wrap').html('<input id="getAS" type="button" value="Get AS list!" style="margin-left: 20px; margin-top: 10px"/>');
							 		$('#button-wrap').append('<p style="font-size: 12px; color: black">No AS to show for your query.</p>');
								}else{
															
									$("<br></br><select multiple='multiple' id='searchable' name='searchable[]'></select>").insertAfter('#button-wrap');
											                        	                        			
			                        var allAS = data.result;
									var AS = allAS.split("*");	
									 							 	                         	                         
			                        for(i = 0; i < AS.length; i++){
			                         	var tmp = AS[i].split(" ");								
										$("#searchable").append('<option value="' + tmp[0] + '">' + AS[i] + "</option> "); 								
									}
									
									$('#searchable').multiselect2side({'search': 'Search: '});
									$('#button-wrap').html('<input id="getAS" type="button" value="Get AS list!" style="margin-left: 20px; margin-top: 10px"/>');
								}
								
							}else{ 		                        	                        																	 
								 $('#button-wrap').html('<input id="getAS" type="button" value="Get AS list!" style="margin-left: 20px; margin-top: 10px"/>');
								 $('#button-wrap').append('<p style="font-size: 12px; color: red">Connection error - can not reach server.</p>');
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
                        stageOne();                                               
                    });
            });
            
            
            /*
            // check status of running queries
            $(document).ready(function() {
            	$(".checkStatus").ready(function() {
             		$(".checkStatus").each(function(index) {
         				var queryID = $(this).attr('id');
         				$("#"+queryID).html('<p class="blink">checking status...</p>');
         				$("#"+queryID+" .blink").blink();
	            		$.post("user_query_managment.php", {func: "getRunningStatus", query: queryID, username: <?php echo '"'.$username.'"'?> },
		             	function(data){
		             		// COMPLETE , PROCESSING , RUNNING , READY , ERROR
		             		if(data==null || data.type=="ERROR"){
		             			$("#"+queryID).html('<p title="'+data.result+'" style="color:red">ERROR</p>');
		             		} else  {
		             			$("#"+queryID).html('<p title="'+data.result+'">'+data.type.toLowerCase()+'</p>');
		             		}
		             	}
		             	,"json");	
             		});
            	});
            });
            */
                                 
            </script>                  
    </head>
    
    <body>        
        
        <div id="container">
			
			<?php include("header.php") ?>
			<!--
            <div id="header">
            	<p>
	                <h5 style="text-align: left; margin-left: 5px">Welcome <?php echo $username; ?>,                	
	                	<a href="logout.php"> <u>Logout</u></a>	<?php if ($username=="admin"){echo 'or <a href="admin.php"> <u>Go to admin page</u></a>';}?> 
	                </h5>
                	<div class="main-title">
                		<img src="images/logo.png">
                	</div>
                </p>                
            </div>
           -->
						                       
            <div id="user-select" class="user-select">          
            <h3 style="text-align:center; size:4; color:rgb(112,97,68); font-family: verdana,arial,sans-serif">Make a new query</h3>
                                                               
                <form id="AS" name="get AS list" style="font-size:14px;">                               
                    
                    <p class="selection-header">Select blade</p>
                	<div align="left" class="selection-text">Blade:                    
	                    <select id="blade">
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
                   </div>
                   
                    <p class="selection-header">Select date</p>       
                    <div align="left" class="selection-text">Year  :                       
                        <select id="year" >
                            <option value="">Select year</option>
                            <?php
                            
                            	$xml = simplexml_load_file("xml\weeks.xml");
								$result = $xml->xpath('/DATA/YEAR/year');					
								if($result!=FALSE)
								{
									var_dump($result);
									foreach($result as $i=>$value){
										echo "<option>".$value."</option>";
									}					
								}                                                   
                            ?>                            
                        </select>
                    </div>

                     <div align="left" class="selection-text">Week:
                        <select id="week">                               
                            <option selected="selected" value="">Select week</option>                                                  
                        </select>
                    </div>
                    <div id="button-wrap-t">
                    	<input id="getTables" type="button" value="Get tables" style="margin-left: 20px; margin-top: 10px"/>
                    </div>
                                                           
                    <p class="selection-header">Select table</p>                                       
               		
               		<div align="left" class="selection-text">PoP IP:                       
                        <select id="popIP" >
                            <option value="">Select PoP IP table</option>                            
                        </select>
                    </div>

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
               		
               		<div id="button-wrap">
                    	<input id="getAS" type="button" value="Get AS list!" style="margin-left: 20px; margin-top: 10px"/>
                    	<p style="font-size: 10px; color: gray">After clicking the list will apear.</p>
                    </div>
                                                           
                </form>
                <input id="sendQuery" class="sendQuery" type="image" src="images/send-button.png"/>
                 <br></br>
              
            </div>
            
            <div id="My_queries">
                <h3 style="size:4; color: rgb(112,97,68)">My queries</h3>                	
                <br></br>                

				<table id="queryTable" class="imagetable" style="alignment-baseline: central">				
				
				<?php
					echo "<tr>";
					echo "<th>QID</th><th>Year</th><th>Week</th><th>Tables</th><th>AS Count</th><th>Status</th><th>Delete</th>";
					echo "</tr>";
					$queries = simplexml_load_file("xml\query.xml");
					$result = $queries->xpath('/DATA/QUERY[users/user="'.$username.'"]');					
					if($result!=FALSE)
					{						
						foreach ($result as $i => $value) {												
							echo "<tr>";							
							echo "<td>".substr($result[$i]->queryID,-4)."</td>";
							echo"<td>".$result[$i]->year."</td>";
							echo"<td>".$result[$i]->week."</td>";
							echo"<td>".$result[$i]->EdgeTbl."</BR>".$result[$i]->PopTbl."</BR>".$result[$i]->PopLocTbl."</td>";
							echo"<td>".$result[$i]->ASnum."</td>";
							echo "<td>";
							if ($result[$i]->lastKnownStatus=="running"){
								echo '<div id="'.$result[$i]->queryID.'" class="checkStatus">running</div>';
							}elseif ($result[$i]->lastKnownStatus=="completed"){
								echo '<form method="get" action="visual_frontend.php" target="_blank"><input name="QID" type="hidden" value="'.$result[$i]->queryID.'"/><input type="submit" id=QstatusC value="Complete"/></form>';
							}elseif ($result[$i]->lastKnownStatus=="error"){
								echo '<button type="submit" onclick="resendQuery(this.value)" value="'.$result[$i]->queryID.'">RUN</button>';
								//echo 'error';													
							}else {
								echo 'unknown status';
							}
							echo "</td>" . '<td> <button type="submit" onclick="abort(this.value)" value="'.$result[$i]->queryID.'">X</button></td>';							
							// reload the page? if changing to "submit",  add: onsubmit="return false;" ?
							echo "</tr>";
						} 
					}
				?>																
				
				</table>                
            </div>
            
            <div class="footer">
                Copyright © 2011 <a href="http://www.netdimes.org/new/">DIMES</a>
            </div>
            
         </div>
    </body>
</html>