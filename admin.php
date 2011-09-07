<?php
	include_once("bin/load_config.php");
	include_once("bin/xml_writer.php");

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
        <script type="text/javascript" src="js/loadData.js"></script>
        <script type="text/javascript">
        	
        	function updateWeeks(){
        		// add info about the action
        		$('#My_queries').html('<h3><b>Update weeks.xml</b></h3><p>The weeks.xml file holds the information of the possible years&weeks to display when generating a new query.</p><p>A week will only apear if all the three tables: edge,pop location and pop-IP exists for that week.</p><p>If any changes where made to the DB(e.g. new tables where added) click the UPDATE button to generate a new and updated file.</p><br></br><input type="submit" onclick="updateWeeksB()" value="Update"/>').fadeIn("slow");        		
        	}
        	
        	function updateWeeksB(){
  //      		$.post("adminFunc.php", {func: "updateWeeks", user: <?php echo '"'.$username.'"'?>},"json");
        		$('#My_queries').append('<p style="color:navy">The file is now being updated.</p>')
        	}
        	
        	function updateAS(){
        		$('#My_queries').html('<h3><b>Update AS_info.xml</b></h3><p>The AS_info.xml file holds the information about the AS - ASN,country and ISP.</p><p>If new ASs were added, click the update button to update the file.</p><br></br><input type="button" onclick="updateASB()" value="Update"/>');
        	}
        	
        	function updateASB(){
    //    		$.post("adminFunc.php", {func: "updateAS", user: <?php echo '"'.$username.'"'?>},"json");
        		$('#My_queries').append('<p style="color:navy">The file is now being updated.</p>')
        	}
        	
        	function showQueries(){
        		$.post("adminFunc.php", {func: "showQueries", user: <?php echo '"'.$username.'"'?>},"json");
        	}
        	
        	
        	
        </script>
                    
    </head>

    <body>        
        
        <div id="container">

            <?php include("header.php") ?>
            
            <div class="user-select">
            	<h3 style="text-align:center; size:4; color:rgb(112,97,68); font-family: verdana,arial,sans-serif">Admin actions</h3>
	            <div id="adminActions">
	            	<p onclick="updateWeeks()"><u>Update weeks.xml</u></p>
	            	<p onclick="updateAS()"><u>Update AS_info.xml</u></p>
	            	<p onclick="showQueries()"><u>View running queries</u></p>
	            </div>
            </div>
            
            <div id="My_queries">
            	<h3><b>Welcome admin!</b></h3>
            	<p>In this page you can change and update some configuration files of the system.</p>
            	<p>click on the options on the left, and get further explanation.</p>
            	<p>Enjoy. </p>	 
            	
            	
        	</div>
            
                        
            <div class="footer">
                Copyright © 2011 <a href="http://www.netdimes.org/new/">DIMES</a>
            </div>
            
         </div>
    </body>
</html>