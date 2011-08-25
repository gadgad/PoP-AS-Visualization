<?php
	include_once("bin/load_config.php");
	include_once("bin/xml_writer.php");
	
	$error = false;
	if(isset($_POST['login'])){
		$username = preg_replace('/[^A-Za-z]/', '', $_POST['username']);
		$password = md5($_POST['password']);
		if(file_exists('users/' . $username . '.xml')){
			$xml = new SimpleXMLElement('users/' . $username . '.xml', 0, true);
			if($password == $xml->password){
				session_start();
				$_SESSION['username'] = $username;
				setcookie('username',$username,time()+3600*24*31*12);
				header('Location: test.php');
				die();
			}
		}
		$error = true;
	}
?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Welcome-Login</title>        
        <script src="js/jquery-1.6.2.min.js" type="text/javascript"></script>
        <link rel="stylesheet" href="css/visual.css" type="text/css" media="screen" />
        <script type="text/javascript" src="js/loadData.js"></script>            
    </head>

    <body>        
        
        <div id="container">

            <div id="header">
                <div class="main-title">
                	<img src="images/logo.png">
                </div>
            </div>
						                       
            <div id="login" class="enter">
            
	            <div class="login-box">
	            		
					<h1 class="login-header">Login</h1>
					<form method="post" action="">
						<p style="margin-left: 10px">Username <input type="text" name="username" size="18"/></p>
						<p style="margin-left: 10px">Password <input type="password" name="password" size="18"/></p>
						<?php
						if($error){
							echo '<p style="color:red; font-size:0.9em; margin-left:10px">Invalid username and/or password</p>';
						}
						?>
						
						<input type="image" value="Login" src="images/Login.png" name="login" alt="Submit" style="margin-left: 50px"/>
						
					<!--	<input type="submit" value="Login" name="login" style="margin-left: 50px"/>-->
					</form>
					<p style="margin-left: 10px; font-size: 12px">not a user? <a href="register.php">Register</a> now! </p>
					<br></br>					
					                          
	            </div>
            </div>
            
            <div class="about">
                <h3>About the project</h3>                	            
            </div>
            
            <div class="footer">
                Copyright © 2011 <a href="http://www.netdimes.org/new/">DIMES</a>
            </div>
            
         </div>
    </body>
</html>
<?php
	echo "checking";
	// run a script to check running queries

	$xml = simplexml_load_file("xml\query.xml");							
	$queries = $xml->xpath('/DATA/QUERY[lastKnownStatus="running"]');		
	if($queries!=FALSE) // there are running queries
	{
		$mysqli = new mysqli($host,$user,$pass,$database,$port);
		
		if ($mysqli->connect_error) {
 		   ret_res('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
		}
		
		$sql = "show processlist";
		if ($processes = $mysqli->query($sql)){
			//$processArr[];	
			while ($row = $processes->fetch_assoc()) {
		        foreach($row as $key => $value){
					//get all PIDs that are running(State!=null) to an array
					if (!$row["State"]){
						$processArr[] = $row["Id"];
					}
				}
		     }	
			$processes->free();
				
			foreach ($queries as $key => $value){
			// check if the query finished. if so - create files & drop temp. tables
				if (!in_array($queries[$key]->processID,$processArr)){ //the query finished.
					
					//generate files
					$XW = new xml_Writer($queries[$key]->blade,$queries[$key]->queryID);
					$done = $XW->writeXML();
					
					//drop tables
					$sql = 'drop table if exist DPV_EDGE_'.$queries[$key]->queryID;
					$res = $mysqli->query($sql);
					$sql = 'drop table if exist DPV_POP_'.$queries[$key]->queryID;
					$res = $mysqli->query($sql);
					
					// changes status in XML					
					$res = $queries->xpath('/DATA/QUERY[queryID="'.$queries[$key]->queryID.'"]/lastKnownStatus');							  
					$theNodeToBeDeleted = $res[0];								
					$oNode = dom_import_simplexml($theNodeToBeDeleted);				
					if (!$oNode) {
					    echo 'Error while converting SimpleXMLelement to DOM';
					}		
					$oNode->parentNode->removeChild($oNode); 								
					$queries[$key]->addChild('lastKnownStatus','completed');
				}
				$xml->asXML("xml\query.xml");  			
			}
		}
		$mysqli->close();
	}
?>
