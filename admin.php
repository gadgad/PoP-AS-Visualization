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
        		$.post("adminFunc.php", {func: "updateWeeks", user: <?php echo '"'.$username.'"'?>},"json");
        	}
        	
        	function updateAS(){
        		$.post("adminFunc.php", {func: "updateAS", user: <?php echo '"'.$username.'"'?>},"json");
        	}
        	
        	function showQueries(){
        		$.post("adminFunc.php", {func: "showQueries", user: <?php echo '"'.$username.'"'?>},"json");
        	}
        	
        	
        </script>
                    
    </head>

    <body>        
        
        <div id="container">

            <div id="header">
                <div class="main-title">
                	<img src="images/logo.png">
                	<br></br>                	
                	<h3 style="text-align:center; size:4; color:rgb(112,97,68); font-family: verdana,arial,sans-serif">Admin page</h3>
                </div>
            </div>
            
            <div id="user-select">
            	<h3 style="text-align:center; size:4; color:rgb(112,97,68); font-family: verdana,arial,sans-serif">Admin actions</h3>
	            <div id="adminActions">
	            	<p onclick="updateWeeks()"><u>Update weeks.xml</u></p>
	            	<p onclick="updateAS()"><u>Update AS_info.xml</u></p>
	            	<p onclick="showQueries()"><u>View running queries</u></p>
	            </div>
            </div>
            
            <div id="My_queries">
            	
        	</div>
            
                        
            <div class="footer">
                Copyright © 2011 <a href="http://www.netdimes.org/new/">DIMES</a>
            </div>
            
         </div>
    </body>
</html>