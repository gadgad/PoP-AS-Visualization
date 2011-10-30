<?php

/*
 * the admin page. 
 */
	include_once("bin/load_config.php");

	session_start();
	$username = isset($_COOKIE['username'])? $_COOKIE['username'] : $_SESSION['username'];
	if($username!="admin"){
		header('Location: welcome.php');
		die;
	}

?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Admin page</title>        
        <script src="js/jquery-1.6.2.min.js" type="text/javascript"></script>
        <link rel="stylesheet" href="css/visual.css" type="text/css" media="screen" />
        <script type="text/javascript">
        
///////////-JQuery Plugins-////////////////////////////////////////////////
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
//////////////////////////////////////////////////////////////////////////
        	$.preLoadImages("images/ajax-loader.gif");
        	
        	var queryID;
        	var option = 0;    
             function updateTable(){
             	//$("#My_queries").append("<p text-align:center>Query " + queryID + " is now running with pid: "+myNameSpace.processID+"</p>");
             	//$('#queryTable').load('admin.php #queryTable').fadeIn("slow");
             	if(option ==3){
             		showQueries();
             	}
              }
        	
        	function updateWeeks(){
        		option = 1;
        		$('#My_queries').html('<h3><b>Update weeks.xml</b></h3><p>The weeks.xml file holds the information of the possible years&weeks to display when generating a new query.</p><p>A week will only apear if all the three tables: edge,pop location and pop-IP exists for that week.</p><p>If any changes where made to the DB(e.g. new tables where added) select the blade to update and click the UPDATE button to updated the file.</p></br><select id="bld"><?php
        		foreach ($Blades as $blade) {							
					echo "<option>".$blade["@attributes"]["name"]."</option>";
				}
        		?></select>      <input type="submit" onclick="updateWeeksB()" value="Update"/>').fadeIn("slow");        		
        	}
        	
        	function updateWeeksB(){
        		$.post("adminFunc.php", {func: "updateWeeks", blade: $("#bld").val(), user: <?php echo '"'.$username.'"'?>},
        		function(data){
        			if (data!=null){
        				if (data.type=="ERROR"){
                 			alert("Error while generating weeks.XML: " + data.result);
                 		}
        			}else alert("data is null");
                }
        		,"json");
        		$('#My_queries').append('<p style="color:navy">The file is now being updated.</p>')
        	}
        	
        	
        	function updateAS(){
        		option = 2;
        		$('#My_queries').html('<h3><b>Update AS_info.xml</b></h3><p>The AS_info.xml file holds the information about the AS - ASN,country and ISP.</BR>If new ASs were added, select a table to update from and click the update button to update the file. pay attention - the new file will include all table attributes as is.</BR></BR><select id="tbl"><option>ASInfoTbl</option><option>ASInfoTbl_16bit_2009</option><option>ASInfoTbl_16bit_March_2009</option></select>  <input type="button" onclick="updateASB()" value="Update"/></br></br>Or enter your own table:</BR></BR>Blade: <input type="text" name="blade" id="blade" size="18"/> Schema: <input type="text" name="schema" id="schema" size="18"/> table: <input type="text" name="freetable" id="freetable" size="18"/> <input type="button" onclick="updateASBfree()" value="Update"/></p>');
        	}
        	
        	function updateASB(){
        		$.post("adminFunc.php", {func: "updateAS", user: <?php echo '"'.$username.'"'?>,table:$("#tbl").val()},"json");
        		$('#My_queries').append('<p style="color:navy">The file is now being updated.</p>')
        	}
        	
        	function updateASBfree(){
        		$.post("adminFunc.php", {func: "updateASfree", user: <?php echo '"'.$username.'"'?>,table:$("#freetable").val(),schema:$("#schema").val(),blade:$("#blade").val()},"json");
        		$('#My_queries').append('<p style="color:navy">The file is now being updated.</p>')
        	}
        	
        	
        	function showQueries(){
        		option = 3;
        		$('#My_queries').html('</BR><table id="queryTable" class="imagetable" style="alignment-baseline: central"></table>');
             	$('#queryTable').html('<p><img src="images/ajax-loader.gif"/></p>');
             	$('#queryTable').load('admin.php?viewRunningQueries #queryTable').fadeIn("slow");     
        	}        	
        	
        	 function abort(queryID){             	
             	//$('#queryTable').fadeOut('fast');
             	//$.preLoadImages("images/ajax-loader.gif");
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
             
             function viewUsers(){             	
             	$('#My_queries').html('</BR><table id="queryTable" class="imagetable" style="alignment-baseline: central"></table>');
             	$('#queryTable').html('<p><img src="images/ajax-loader.gif"/></p>');
             	$('#queryTable').load('admin.php?viewUsers #queryTable').fadeIn("slow");         		
        	 }        	             	        
             
             function handleRequests(){
             	$('#My_queries').html('</BR><table id="queryTable" class="imagetable" style="alignment-baseline: central"></table>');
             	$('#queryTable').html('<p><img src="images/ajax-loader.gif"/></p>');
             	$('#queryTable').load('admin.php?viewPendingUsers #queryTable').fadeIn("slow"); 	
             }
             
             function accept(userFile){             	             	
             	//$.preLoadImages("images/ajax-loader.gif");
             	$('#queryTable').html('<p><img src="images/ajax-loader.gif"/></p>');  				
             	$.post("adminFunc.php", {func: "accept",user: <?php echo '"'.$username.'"'?>, userfile: userFile},
             	function(data){
             		if(data.type=="GOOD"){ 
             			handleRequests();
             		}
             		if (data.type =="ERROR")
                     	{alert(data.result);}
             	}
             	,"json");                                                  	
             }
             
             function deny(userFile){             	             	
             	//$.preLoadImages("images/ajax-loader.gif");
             	$('#queryTable').html('<p><img src="images/ajax-loader.gif"/></p>');  				
             	$.post("adminFunc.php", {func: "deny",user: <?php echo '"'.$username.'"'?>, userfile: userFile},
             	function(data){
             		if(data.type=="GOOD"){ 
             			handleRequests();
             		}
             		if (data.type =="ERROR")
                     	{alert(data.result);}
             	}
             	,"json");                                                  	
             }
             
             function blades(){
             	$('#My_queries').html('</BR><table id="queryTable" class="imagetable" style="alignment-baseline: central"></table>');
             	$('#queryTable').html('<p><img src="images/ajax-loader.gif"/></p>');
             	$('#queryTable').load('admin.php?viewBlades=true #queryTable').fadeIn("slow");
             	$('#My_queries').append('</br><p style="color: navy;text-align:center"><u> Add a new blade </u></p><p style="text-align:center">blade <input type="text" name="bladeA" id="bladeA" size="18"/>  host <input type="text" name="host" id="host" size="18"/>  port <input type="text" name="port" id="port" size="18"/></p><p style="text-align:center">user <input type="text" name="user" id="user" size="18"/>  password <input type="text" name="pass" id="pass" size="18"/></p><p style="text-align:center">DB <input type="text" name="db" id="db" size="18"/>  write DB <input type="text" name="write-db" id="write-db" size="18"/></p><input type="button" onclick="addBlade()" value="Add"/>');
             	$('#My_queries').append('</br><p style="color: navy;text-align:center"><u> Remove blade </u></p><p style="text-align:center">blade <input type="text" name="bladeR" id="bladeR" size="18"/>   <input type="button" onclick="removeBlade()" value="Remove"/></p>');
             	$('#My_queries').append('</br><p style="color: navy;text-align:center"><u> Change default blade </u></p><p style="text-align:center">new default blade <input type="text" id="defaultBlade" size="18"/>   <input type="button" onclick="changeDefaultBlade()" value="Change"/></p>');             	                 
             }
             
             function addBlade(){
             	$.post("adminFunc.php", {func: "addBlade", user: <?php echo '"'.$username.'"'?>, blade: $("#bladeA").val(), host: $("#host").val(), port: $("#port").val(), bladeUser: $("#user").val(), pass: $("#pass").val(), db: $("#db").val(), writedb: $("#write-db").val()},
        		function(data){
        			if (data!=null){
        				if (data.type=="ERROR"){
                 			alert("Error while adding blade: " + data.result);
                 		}else {
                 			blades();
                 			$('#My_queries').append('<p style="color:navy">The Blade was added to config.xml.</p>');                 			
                 		}
        			}else alert("data is null");
                }
        		,"json");        		
             }
             
             function removeBlade(){
             	$.post("adminFunc.php", {func: "removeBlade", user: <?php echo '"'.$username.'"'?>, blade: $("#bladeR").val()},
        		function(data){
        			if (data!=null){
        				if (data.type=="ERROR"){
                 			alert("Error while removing blade: " + data.result);
                 		}else {
                 			blades();
                 			$('#My_queries').append('<p style="color:navy">The Blade was removed from config.xml.</p>');                 			
                 		} 
        			}else alert("data is null");
                }                
        		,"json");        		
             }
             
             function changeDefaultBlade(){				
				$.post("adminFunc.php", {func: "changeDefaultBlade", user: <?php echo '"'.$username.'"'?>, blade: $("#defaultBlade").val()},
        		function(data){
        			if (data!=null){
        				if (data.type=="ERROR"){
                 			alert("Error while changing blade: " + data.result);
                 		}else {
                 			blades();
                 			$('#My_queries').append('<p style="color:navy">The default blade was changed.</p>');                 			
                 		} 
        			}else alert("data is null");
                }                
        		,"json");             	
             }
             
             function dataTables(){
             	$('#My_queries').html('</BR><table id="queryTable" class="imagetable" style="alignment-baseline: central"></table>');
             	$('#queryTable').html('<p><img src="images/ajax-loader.gif"/></p>');
             	$('#queryTable').load('admin.php?viewDataTables=true #queryTable').fadeIn("slow");
             	$('#My_queries').append('</br><p style="color: navy;text-align:center"><u> Change data table </u></p><select id="dataTable"><option>ip-edges</option><option>pop-locations</option><option>popip</option><option>as-info</option></select> <select id="SP"><option>schema</option><option>prefix</option></select> <p style="text-align:center">new value <input type="text" id="paramValue" size="18"/>   <input type="button" onclick="changeParam()" value="Change"/></p>');
             }
             
             function changeParam(){
             	$.post("adminFunc.php", {func: "changeParam", user: <?php echo '"'.$username.'"'?>,dataTable : $("#dataTable").val(), SP : $("#SP").val(), paramValue : $("#paramValue").val()},
        		function(data){
        			if (data!=null){
        				if (data.type=="ERROR"){
                 			alert("Error while removing blade: " + data.result);
                 		}else $('#My_queries').append('<p style="color:navy">The data table was changed.</p>'); 
        			}else alert("data is null");
                }
        		,"json");        		
             }
             
             // loads the parameters info and change options.
             function parameters(){
             	$('#My_queries').html('</BR><table id="queryTable" class="imagetable" style="alignment-baseline: central"></table>');
             	$('#queryTable').html('<p><img src="images/ajax-loader.gif"/></p>');
             	$('#queryTable').load('admin.php?viewParameters=true #queryTable').fadeIn("slow");
             	$('#My_queries').append('</br><p style="color: navy;text-align:center"><u> Change parameter </u></p><select id="configParameter"><?php 
             		$xml = simplexml_load_file("config/config.xml");
					$result = $xml->xpath('/config/config-parameters/parameter');							
						if($result!=FALSE){
							 foreach ($result as $i => $value) {
								echo"<option>".$value->name."</option>";
							 }
					} ?></select> <select id="paramAttribute"><option>description</option><option>value</option><option>units</option></select> <p style="text-align:center">new value <input type="text" id="paramVal" size="18"/>   <input type="button" onclick="changeParamVal()" value="Change"/></p>');
             }
             
             // sends the new value of the parameter to the server to change
             function changeParamVal(){
             	$.post("adminFunc.php", {func: "changeParamVal", user: <?php echo '"'.$username.'"'?>,param : $("#configParameter").val(), attribute : $("#paramAttribute").val(), value : $("#paramVal").val()},
        		function(data){
        			if (data!=null){
        				if (data.type=="ERROR"){
                 			alert("Error while changing parameter: " + data.result);
                 		}else $('#My_queries').append('<p style="color:navy">The parameter was changed.</p>'); 
        			}else alert("data is null");
                }
        		,"json");             	
             }
             
             // change admin password
             function password(){
             	$('#My_queries').html('</br><p style="color: navy;text-align:center"><form><u> Change password </u></p><p style="text-align:center">old password <input type="text" id="oldPass" size="18"/><p style="text-align:center">new password <input type="text" id="newPass" size="18"/><p style="text-align:center">confirm password <input type="text" id="confirmPass" size="18"/></p> <p style="color: navy;text-align:center"><input type="button" onclick="changePassword()" value="Change"/> <input type="reset"/></p></form>');            	
             }
             
             function changePassword(){
             	$.post("adminFunc.php", {func: "changePassword", user: <?php echo '"'.$username.'"'?>,oldPass : $("#oldPass").val(), newPass : $("#newPass").val(), confirmPass : $("#confirmPass").val()},
        		function(data){
        			if (data!=null){
        				if (data.type=="ERROR"){
                 			alert(data.result);
                 		}else $('#My_queries').append('<p style="color:navy">Password updated.</p>'); 
        			}else alert("data is null");
                }
        		,"json");
             }
             
             function invite(){
             	$('#My_queries').html('</br><p style="color: navy;text-align:center"><u> Invite others to join </u></p><p style="text-align:center">Enter an email address of someone you want to invite to use the site. After his registration this user will be automatically authorized.<p style="text-align:center">Email <input type="text" id="email" size="18"/> <input type="button" onclick="inviteUser()" value="Invite"/> </p>');
             }
             
             function inviteUser(){
             	$.post("adminFunc.php", {func: "inviteUser", user: <?php echo '"'.$username.'"'?>,email : $("#email").val()},
        		function(data){
        			if (data!=null){
        				if (data.type=="ERROR"){
                 			alert(data.result);
                 		}else $('#My_queries').append('<p style="color:navy">Invitation sent.</p>'); 
        			}else alert("data is null");
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
        </script>
                    
    </head>

    <body>  
    	   
    	<?php
    	
    	if(isset($_REQUEST['viewRunningQueries'])){
    		$queries = simplexml_load_file("xml/query.xml");
			$result = $queries->xpath('/DATA/QUERY[lastKnownStatus="running"]');
			if(empty($result)){
				echo '<h3 id="queryTable">there are currently no running queries...</h3>';
			} else {
				echo '<table id="queryTable" class="imagetable" style="alignment-baseline: central">';
				echo "<tr>";
				echo "<th>QID</th><th>User</th><th>Year</th><th>Week</th><th>Tables</th><th>AS Count</th><th>Status</th><th>Cancel</th>";
				echo "</tr>";					
				if($result!=FALSE)
				{						
					foreach ($result as $i => $value) {												
						echo "<tr>";							
						echo "<td>".substr($result[$i]->queryID,-4)."</td>";
						$Qusers = "";
						$res = $queries->xpath('/DATA/QUERY[queryID="'.$result[$i]->queryID.'"]/users');
						if($res!=FALSE){
							$arr = $res[0];								
							foreach($arr as $j){
								$Qusers .= (string)$j." ";					
							}									
						}
						echo"<td>".$Qusers."</td>";
						echo"<td>".$result[$i]->year."</td>";
						echo"<td>".$result[$i]->week."</td>";
						echo"<td>".$result[$i]->EdgeTbl."</BR>".$result[$i]->PopTbl."</BR>".$result[$i]->PopLocTbl."</td>";
						echo"<td>".$result[$i]->ASnum."</td>";
						echo "<td>";
						echo '<div id="'.$result[$i]->queryID.'" class="checkStatus">running</div>';							
						echo "</td>" . '<td> <button type="submit" onclick="abort(this.value)" value="'.$result[$i]->queryID.'">X</button></td>';							
						echo "</tr>";		
					} 
				}else echo 'Error while retrieving data from XML.';
				echo '</table>';
			}
		die();
	}
		
    if(isset($_REQUEST['viewPendingUsers'])){
		echo '<table id="queryTable" class="imagetable" style="alignment-baseline: central"><tr><th>Username</th><th>email</th><th>Accept</th><th>Deny</th></tr>';					
		$files = scandir(getcwd().'/users');
		if ($files!=FALSE){
			foreach ($files as $file){							
				if (substr($file, 0,1)!="."){						
					$userfile = simplexml_load_file("users/".$file);
					$result = $userfile->xpath('/user');					
					if($result!=FALSE)
					{
						if("pending" == $result[0]->status){
							echo "<tr>";
							echo "<td>".basename($file,'.xml')."</td>";												
							echo"<td>".(string)$result[0]->email."</td>";													
							echo '<td> <button type="submit" onclick="accept(this.value)" value="'.$file.'">&#8730</button></td>';							
							echo '<td> <button type="submit" onclick="deny(this.value)" value="'.$file.'">X</button></td>';
							echo "</tr>";	
						}																										
					}	
				}
									
			}
		}
		echo '</table>';
		die();
	}
	
	if(isset($_REQUEST["viewUsers"])){
		echo '<table id="queryTable" class="imagetable" style="alignment-baseline: central">';
		echo "<tr>";
		echo "<th>Username</th><th>email</th><th>Status</th>";
		echo "</tr>";					
		$files = scandir(getcwd().'/users');
		if ($files!=FALSE){
			foreach ($files as $file){							
				if (substr($file, 0,1)!="."){						
					$userfile = simplexml_load_file("users/".$file);
					$result = $userfile->xpath('/user');					
					if($result!=FALSE)
					{
						echo "<tr>";
						echo "<td>".basename($file,'.xml')."</td>";																					
						echo"<td>".(string)$result[0]->email."</td>";													
						echo"<td>".(string)$result[0]->status."</td>";							
						echo "</tr>";																	
					}else echo 'Error while retrieving data from '.$file.'.';	
				}												
			}
		}else { echo "<tr><td>ERROR</td></tr>";}		
		echo '</table>';
		die();
	}
	
	if(isset($_REQUEST["viewBlades"])){
			
		$xml = simplexml_load_file('config/config.xml');
		$res = $xml->xpath('/config/blades/blade[@default="true"]');
		if($res!=FALSE)
		{ 
			$defaultBlade = (string)$res[0]->attributes()->name;
		}else {
			echo 'Error while retrieving data from XML.';
			die();
			}
			
		echo '<table id="queryTable" class="imagetable" style="alignment-baseline: central">';
		echo "<tr>";
		echo "<th>Blade</th><th>host</th><th>port</th><th>user</th><th>password</th><th>DB</th><th>write DB</th><th>deault</th>";
		echo "</tr>";									
		foreach ($Blades as $blade) {
																
			echo "<tr>";							
			echo "<td>".$blade["@attributes"]["name"]."</td>";							
			echo "<td>".$blade["host"]."</td>";
			echo "<td>".$blade["port"]."</td>";
			echo "<td>".$blade["user"]."</td>";
			echo "<td>".(is_array($blade["pass"])? '':$blade["pass"])."</td>";
			echo "<td>".$blade["db"]."</td>";
			echo "<td>".$blade["write-db"]."</td><td>";
			if ($defaultBlade==$blade["@attributes"]["name"]){
				echo "yes";
			}										
			echo "</td>";				
			echo "</tr>";		
		}
		echo '</table>';
		 
		die();
	}
	
	
	if(isset($_REQUEST["viewDataTables"])){
													
		$xml = simplexml_load_file("config/config.xml");
		$result = $xml->xpath('/config/data-tables');
							
		if($result!=FALSE)
		{
			echo '</br><p style="color: navy;text-align:center"><u> bla </u></p></br>';
			echo '<table id="queryTable" class="imagetable" style="alignment-baseline: central">';
			echo "<tr>";
			echo "<th>table</th><th>schema</th><th>prefix</th>";
			echo "</tr>";
			$tables = $result[0];						
			foreach ($tables as $i => $value) {																
				echo "<tr>";
				echo"<td>".(string)$i."</td>";
				echo"<td>".$value->schema."</td>";
				echo"<td>".$value->prefix."</td>";
				echo "</tr>";						
			} 
 			echo '</table>';	
		} else echo 'Error while retrieving data from XML.';		
		die();
	}

	if(isset($_REQUEST["viewParameters"])){
													
		$xml = simplexml_load_file("config/config.xml");
		$result = $xml->xpath('/config/config-parameters/parameter');
							
		if($result!=FALSE)
		{
			echo '</br><p style="color: navy;text-align:center"><u> bla </u></p></br>';
			echo '<table id="queryTable" class="imagetable" style="alignment-baseline: central">';
			echo "<tr>";
			echo "<th>Name</th><th>Description</th><th>Value</th><th>Units</th>";
			echo "</tr>";				
			foreach ($result as $i => $value) {																
				echo "<tr>";
				echo"<td>".$value->name."</td>";
				echo"<td>".wordwrap(str_replace('\n', "</br>", $value->description),50, "</br>")."</td>";
				echo"<td>".$value->value."</td>";
				echo"<td>".$value->units."</td>";
				echo "</tr>";						
			} 
 			echo '</table>';	
		}else echo 'Error while retrieving data from XML.';		
		die();
	}
	
	?>   
        
        <div id="container">

            <div id="header">
				<p>
			        <h5 style="text-align: left; margin-left: 5px">Welcome <?php echo $username; ?>,                	
			        	<a href="logout.php"> <u>Logout</u></a>	or <a href="index.php"> <u>Go to queries page</u></a> 
			        </h5>
			    	<div class="main-title">
			    		<img src="images/logo.png">
			    	</div>
			    </p>                
			</div>
            
            <div class="user-select">
            	<h3 style="text-align:center; size:4; color:rgb(112,97,68); font-family: verdana,arial,sans-serif">Admin actions</h3>
	            <div id="adminActions">
	            	<p onclick="password()"><u>Change password</u></p>
	            	<p onclick="updateWeeks()"><u>Update weeks.xml</u></p>
	            	<p onclick="updateAS()"><u>Update ASN_info.xml</u></p>
	            	<p onclick="showQueries()"><u>View running queries</u></p>
	            	<p onclick="viewUsers()"><u>View system users</u></p>
	            	<p onclick="handleRequests()"><u>Accept/Deny pending user requests</u></p>
	            	<p onclick="blades()"><u>Configure blades (config.xml)</u></p>
	            	<p onclick="dataTables()"><u>Configure data tables (config.xml)</u></p>
	            	<p onclick="parameters()"><u>Configure parameters (config.xml)</u></p>
	            	<p onclick="invite()"><u>Invite others to join</u></p>
	            </div>
            </div>
            
            <div id="My_queries">
            	<h3><b>Welcome admin!</b></h3>
            	<p  style="text-align:center"> In this page you can change and update some configuration files of the system.</BR>
            	click on the options on the left, and get further explanation.</BR>
            	Enjoy. </p>	 
            	
        	</div>
        	                                  
            <div class="footer">
                Copyright © 2011 <a href="http://www.netdimes.org/new/">DIMES</a>
            </div>
            
         </div>
    </body>
</html>