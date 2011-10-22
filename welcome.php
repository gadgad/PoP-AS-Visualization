<!--
	the login page. the first page to load.  
-->
<?php
	//include_once("bin/load_config.php");
	/*
	 * after the user enters his login details, we check for correctness, user status and cookie
	 */
	
	$error = false;
	if(isset($_POST['login'])){
		$username = preg_replace('/[^A-Za-z]/', '', $_POST['username']);
		$password = hash("sha256",$_POST['password']);
		if(file_exists('users/' . $username . '.xml')){
			$xml = new SimpleXMLElement('users/' . $username . '.xml', 0, true);
			if($password == $xml->password){
				if ("authorized" == $xml->status){
					session_start();
					$_SESSION['username'] = $username;
					setcookie('username',$username,time()+3600*24*31*12);
					$url = (isset($_SESSION['request_url']))? $_SESSION['request_url'] : (($username == 'admin')? 'admin.php':'index.php');
					//session_write_close(); 
					header('Location: '.$url);
					die();	
				}elseif ("pending" == $xml->status) {										 
					header('Location: pending.php');
					die();
				}else {
					header('Location: denied.php');
					die();
				}				
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
    </head>

    <body>        
        
        <div id="container">

            <div id="header">
                <div class="main-title">
                	<img src="images/logo.png">
                </div>
            </div>
						                       
            <div id="login" class="enter">
            
	            <div class="login-box" style="width:320px; background-image: url('images/table-images/cell-blue-login.jpg');">
	            		
					<h1 class="login-header">Login</h1>
					<form method="post" action="" id="myform" name="myform">
						<p style="margin-left: 10px">Username <input type="text" name="username" size="18"/></p>
						<p style="margin-left: 10px">Password <input type="password" name="password" size="18"/></p>
						<?php
						if($error){
							echo '<p style="color:red; font-size:0.9em; margin-left:10px">Invalid username and/or password</p>';
						}
						?>
						
					<!--	<input type="image" value="Login" src="images/Login.png" name="login" alt="Submit" style="margin-left: 50px"/>-->
						
					<!-- 	<a href="javascript:document.myform.submit()" name="login" value="Login"> 						 						
							<img src="images/Login.png" alt="Submit"  style="margin-left: 50px" name="login" value="Login"/> 					
							</a> -->
						
						<input type="submit" value="Login" name="login" style="margin-left: 50px"/> 
					</form>
					<p style="margin-left: 10px; font-size: 12px">not a user? <a href="register.php">Register</a> now! </p>
					<br></br>					
					                          
	            </div>
	            
	            <div style="text-align:center; padding-top:20px;">
	            	<img src="images/DIMES.gif" style="margin-top: 20px">	
	            </div>
	            
            </div>
            
            <?php
	    		if(isset($_REQUEST["formComplete"])) echo
		    		"<div class=\"about\">
		    		<h3>Thank you! </h3>   
		    		<p>Dear user, your registration request was sent to the site admin successfully.</br>
		    		We we'll let you know as soon as your request is approved.</p>
		    		</div>";
		    		
				else include("info.php")
              ?>
            
            <div class="footer">
                Copyright © 2011 <a href="http://www.netdimes.org/new/">DIMES</a>
            </div>
            
         </div>
    </body>
</html>
