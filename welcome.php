<?php
	include("bin/load_config.php");
	
	$error = false;
	if(isset($_POST['login'])){
		$username = preg_replace('/[^A-Za-z]/', '', $_POST['username']);
		$password = md5($_POST['password']);
		if(file_exists('users/' . $username . '.xml')){
			$xml = new SimpleXMLElement('users/' . $username . '.xml', 0, true);
			if($password == $xml->password){
				session_start();
				$_SESSION['username'] = $username;
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
        <script type="text/javascript" src="js/loadData.js"></script>            
    </head>

    <body>        
        
        <div id="container" style="font-family:font-family: verdana,arial,sans-serif;">
        	<!--comic sans ms;-->

            <div id="header">
                <h1 style="margin-bottom:10px;text-align:center;color:Navy">PoP/AS Visualizer</h1>
            </div>
						                       
            <div id="login" style="margin-left:3%;background-color:#FFD700;width:27%;height: 85%; float:left;
            clear: none">
            
	            <div style="background-image: url('images/table-images/cell-blue-big.jpg');background-position: 0% 0%;
  					background-repeat: repeat-x;margin:10px">
	            		
					<h1 style="margin-top: 20px; margin-left: 10px">Login</h1>
					<form method="post" action="">
						<p style="margin-left: 10px">Username <input type="text" name="username" size="18"/></p>
						<p style="margin-left: 10px">Password <input type="password" name="password" size="18"/></p>
						<?php
						if($error){
							echo '<p>Invalid username and/or password</p>';
						}
						?>
						<p style="margin-left: 50px"><input type="submit" value="Login" name="login" /></p>
					</form>
					<p>not a user? <a href="register.php">Register</a> now! </p>
					                          
	            </div>
            </div>
            
            <div id="about" style="margin-right: 3%;background-color:#EEEFEE;width:67%;height:85%;float: right; clear:right; text-align:center">
                <h3>About the project</h3>                	            
            </div>
            
            <div id="footer" style="background-color:#FFA500;clear:both;text-align:center;margin-top: 10px">
                Copyright © 2011 DIMES
            </div>
            
         </div>
    </body>
</html>