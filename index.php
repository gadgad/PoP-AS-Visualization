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
        <style>
	      .formGrayOut{
	          background: #ccc;
	       }
	       .validator {
			    display: inline;
			    height: 16px;
			    margin-left: 6px;
			    margin-top: -2px;
			    width: 16px;
			}
	    </style>
          
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
                         
                         $('#button-wrap-t').html('<input id="getTables" type="button" value="Get tables" style="margin-left: 20px; margin-top: 10px"/>');
                    }, "json");	
                }
             } 
             
             
             /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
             
             var queryID;
             var myNameSpace = new Object;
             myNameSpace.processID = [];
             
             function updateTable(){
             	$("#My_queries").append("<p text-align:center>Query " + queryID + " is now running with pid: "+myNameSpace.processID+"</p>");
             	// add a table line ?             		
             }
             
             var error_counter;
             function stageOne(){
             	error_counter = 0;
             	//$.preLoadImages("images/ajax-loader.gif");
				$("#sendQuery").after('<img id="sendQueryStatus" class="validator" src="images/ajax-loader.gif"/>');
             	$.post("query_backend.php", {func: "sendQuery", stage:1, blade: $("#blade").val() ,
                         edge: $("#Edge").val(), pop: $("#PoP").val(), popIP: $("#popIP").val(), username: <?php echo '"'.$username.'"'?>, as: $("#searchable").val() },
                         function(data){
                         	if (data==null || data.type=="ERROR"){
                         		$("#My_queries").append('<p style="color:red">ERROR - The query did not run successfuly</p>');
                         		$("#sendQueryStatus").remove();
                         		if(data!=null) $("#My_queries").append('<p style="color:red">'+data.result+'</p>');
                         	}else if(data.type=="ALL_COMPLETE"){
                         		$("#My_queries").append("<p text-align:center>"+data.result+"</p> ");
                         		$("#sendQueryStatus").remove();	
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
                         edge: $("#Edge").val(), pop: $("#PoP").val(), popIP: $("#popIP").val(), username: <?php echo '"'.$username.'"'?>, as: $("#searchable").val() },
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
             
            /*
            function stageTwo(subStage){
            	$.post("query_backend.php", {func: "sendQuery", stage:2, sub_stage: 1, blade: $("#blade").val() ,
                         edge: $("#Edge").val(), pop: $("#PoP").val(), popIP: $("#popIP").val(), username: <?php echo '"'.$username.'"'?>, as: $("#searchable").val() });
                $.post("query_backend.php", {func: "sendQuery", stage:2, sub_stage: 2, blade: $("#blade").val() ,
                         edge: $("#Edge").val(), pop: $("#PoP").val(), popIP: $("#popIP").val(), username: <?php echo '"'.$username.'"'?>, as: $("#searchable").val() });
            	stageThree();
            }
            */
             
             
             function stageThree()
             {
             	if(error_counter==3) {
             		$("#My_queries").append('<p style="color:red">ERROR - The query did not run successfuly</p>');
             		$("#sendQueryStatus").remove();
             		return;
             	}
             	$.post("query_backend.php", {func: "sendQuery", stage:3, blade: $("#blade").val() ,
                         edge: $("#Edge").val(), pop: $("#PoP").val(), popIP: $("#popIP").val(), username: <?php echo '"'.$username.'"'?>, as: $("#searchable").val() },
                         function(data){
                         	if (data==null || data.type=="ERROR"){
                         		//$("#My_queries").append('<p style="color:red">ERROR - The query did not run successfuly</p>');
                         		//if(data!=null) $("#My_queries").append('<p style="color:red">'+data.result+'</p>');
                         		error_counter++;
                         		stageThree();
                         	} else {
                         		if(data.type!="GOOD"){
                         			$("#My_queries").append('<p style="color:red">ASSERTION ERROR</p>');
                         			$("#sendQueryStatus").remove();
                         		} else {
                         			queryID=data.queryID;
                         			//myNameSpace.processID.push(data.pid1);
                         			//myNameSpace.processID.push(data.pid2);
	                     			updateTable();
	                     			$("#sendQueryStatus").remove();
									$('#queryTable').fadeOut('slow').load('index.php #queryTable').fadeIn("slow");
	                         		// TODO: update table?
                         		}
                         	}                         	                        				                         	                         	                         
                        }
                         ,"json");
             }
             
             ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            function abort(queryID){             	
             	//$('#queryTable').fadeOut('fast');
             	$.preLoadImages("images/ajax-loader.gif");
             	$('#queryTable').html('<p><img src="images/ajax-loader.gif"/></p>');  				
             	$.post("user_query_managment.php", {func: "abort", query: queryID, username: <?php echo '"'.$username.'"'?> },
             	function(data){
             		if(data.type=="GOOD"){
             			$('#queryTable').load('test.php #queryTable').fadeIn("slow");
             		}
             		if (data.type =="ERROR")
                     	{alert(data.result);}
             	}
             	,"json");
                                                  	
             }
                                  
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
							var weeks = data.weeks;		            
							if (weeks!=null){
	                         	for(i = 1; i < weeks.length; i++){								
									$("#week").append("<option>" + weeks[i] + "</option> "); 									
								 }
                         	}else {$("#week").append("<option>No weeks available</option> ");}		                        	                        			
	                         
                        }, "json");	
                	}
					                	            
                });              
            });
            
            
            // get all relevant AS by parameters TODO: change click
            $(document).ready(function() {
                    $("#getAS").click(function() {
                    	$.preLoadImages("images/ajax-loader.gif");
  						$('#button-wrap').html('<p><img src="images/ajax-loader.gif"/></p>');                                                           
                        $.post("query_backend.php", {func: "getASlist", blade: $("#blade").val(), edge: $("#Edge").val() , pop: $("#PoP").val()},
                        function(data){
									                        	                        			
							$("<br></br><select multiple='multiple' id='searchable' name='searchable[]'></select>").insertAfter('#button-wrap');
									                        	                        			
	                         var allAS = data.result;
							 var AS = allAS.split("*");	
							 							 	                         	                         
	                         for(i = 0; i < AS.length; i++){
	                         	var tmp = AS[i].split(" ");								
								$("#searchable").append('<option value="' + tmp[0] + '">' + AS[i] + "</option> "); 								
							 }
							$('#searchable').multiselect2side({'search': 'Search: '});							 
							 $('#button-wrap').html('<input id="getAS" type="button" value="Get AS list!" style="margin-left: 20px; margin-top: 10px"/>');
	                         
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
            
                                 
     		// Completed - open map page
     		$(document).ready(function() {
                    $("#QstatusC").click(function() {                                                           
                        $.post("visual_frontend.php", {func: "showMap", QID: $("#QstatusC").val()},"json");                                                                             
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
                	<div class="main-title">
                		<img src="images/logo.png">
                	</div>
                </p>                
            </div>
						                       
            <div id="user-select">          
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
                            	/*
                            	$currentYear = date("Y");
	                            for($i = 2008; $i <= $currentYear; $i++){
	                            	echo "<option>".$i."</option>";																 								 
								 }	
								 * */
								 
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
                    
                    <!--
                    	<select multiple='multiple' id='searchable' name="searchable[]"></select>                    		
                    -->
                    	                        
                </form>
                <input id="sendQuery" type="image" src="images/send-button.png" style="margin-left: 20px; margin-top: 10px"/>
                 <br></br>
              
            </div>
            
            <div id="My_queries">
                <h3 style="size:4; color: rgb(112,97,68)">My queries</h3>                	
                <br></br>                

				<table id="queryTable" class="imagetable" style="alignment-baseline: central">				
				
				<?php
					echo "<tr>";
					echo "<th>Edge table</th><th>PoP table</th><th>Status</th><th>Delete</th>";
					echo "</tr>";
					$queries = simplexml_load_file("xml\query.xml");
					$result = $queries->xpath('/DATA/QUERY[users/user="'.$username.'"]');					
					if($result!=FALSE)
					{						
						foreach ($result as $i => $value) {												
							echo "<tr>";							
							echo "<td>".$result[$i]->EdgeTbl."</td>" . "<td>".$result[$i]->PopTbl."</td>" . "<td>";
							if ($result[$i]->lastKnownStatus=="running"){
								echo "running";
							}elseif ($result[$i]->lastKnownStatus=="completed"){
								echo '<button type="button" id=QstatusC value="'.$result[$i]->queryID.'">completed</button>';	
							}else {
								echo 'ambigues status';
							}
							echo "</td>" . '<td> <button type="button" onclick="abort(this.value)" value="'.$result[$i]->queryID.'">X</button></td>';							
							// reload the page? if changing to "submit",  add: onsubmit="return false;" ?
							echo "</tr>";
						} 
					}//else echo "you have no queries yet... ";
				?>																
				
				</table>                
            </div>
            
            <div class="footer">
                Copyright © 2011 <a href="http://www.netdimes.org/new/">DIMES</a>
            </div>
            
         </div>
    </body>
</html>