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