<?php
$errors = array();
if(isset($_POST['login'])){
	$username = preg_replace('/[^A-Za-z]/', '', $_POST['username']);
	$email = $_POST['email'];
	$password = $_POST['password'];
	$c_password = $_POST['c_password'];
	
	if(file_exists('users/' . $username . '.xml')){
		$errors[] = 'Username already exists';
	}
	if($username == ''){
		$errors[] = 'Username is blank';
	}	
	if($password == '' || $c_password == ''){
		$errors[] = 'Passwords are blank';
	}
	if($password != $c_password){
		$errors[] = 'Passwords do not match';
	}
	if(count($errors) == 0){
		$xml = new SimpleXMLElement('<user></user>');
		$xml->addChild('password', md5($password));
		$xml->addChild('email', $email);
		$xml->asXML('users/' . $username . '.xml');
		header('Location: welcome.php');
		die();
	}
}
?>

<html>
	<head>
		<title>Register</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
	            		
					<h1 style="margin-top: 20px; margin-left: 10px">Register</h1>
					<form method="post" action="">
						<p style="margin-left: 10px">Username <input type="text" name="username" size="18"/></p>
						<p style="margin-left: 10px">Password <input type="password" name="password" size="18"/></p>						
						<p style="margin-left: 10px">Confirm Password <input type="password" name="c_password" size="18" /></p>
						<p style="margin-left: 10px; color: gray" size = "2">For username use letters only</p>
						<?php
						
						  if(count($errors) > 0){
							echo '<ul>';
							foreach($errors as $e){
								echo '<li>' . $e . '</li>';
							}
							echo '</ul>';
						  }
						 				
						?>
						<p style="margin-left: 50px"><input type="submit" value="Register" name="login" /></p>
					</form>
					
					                          
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