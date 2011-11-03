<!--
	this page is loaded when a user registers to the system.
	it recives the info and checks for errors. if all info is ok the user is redirected to welcome.php 
-->

<?php

require_once('bin/email_validator.php');
require_once('bin/save_xml.php');

/*
 *  input validation
 */
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
		if($email == ''){
			$errors[] = 'Email is blank';
		}
		
		if(!check_email_address($email)){
			$error[] = 'Invalid email address';	
		}
		
		if($password == '' || $c_password == ''){
			$errors[] = 'Passwords are blank';
		}
		if($password != $c_password){
			$errors[] = 'Passwords do not match';
		}
		
		$registered = simplexml_load_file('xml/authorized_users.xml');
		$res = $registered->xpath('/DATA[email="'.md5($email).'"]');
		if(!empty($res)){
			$errors[] = 'Email already exists!';
		}
		
		if(count($errors) == 0){
			$xml = new SimpleXMLElement('<user></user>');
			$xml->addChild('password', hash("sha256",$password));
			$xml->addChild('email', $email);
			
			//checking if the user was invited
			$invited = false;
			$invites = simplexml_load_file('xml/invited_users.xml');
			$hashed_email = md5($email);
			$res = $invites->xpath('/DATA[email="'.$hashed_email.'"]');
			if (!empty($res)){ // user has an 'invite'
				$invited = true;
			
				// change status to authorized immediatly
				$xml->addChild('status', 'authorized');
				
				// add user to authorized_users.xml
				$registered->addChild('email',$hashed_email);
				save_xml_file($registered->asXML(),'xml/authorized_users.xml');
				
				// remove email from invties list...		
				$res = $invites->xpath('/DATA/email');
				foreach ($res as $key => $value){
					if (strcmp($value,$hashed_email)==0){	
						$theNodeToBeDeleted = $res[$key];								
						$oNode = dom_import_simplexml($theNodeToBeDeleted);				
						if (!$oNode) {
						    die('Error while converting SimpleXMLelement to DOM');
						}		
						$oNode->parentNode->removeChild($oNode); 				
					}
				}
				save_xml_file($invites->asXML(),'xml/invited_users.xml');	
				
			} else {
				$xml->addChild('status', 'pending'); 
			} 
	
			$xml->asXML('users/' . $username . '.xml');
			header('Location: welcome.php?formComplete=true&invited='.(($invited)?'true':'false'));
			die();
		}
	}
?>

<html>
	<head>
		<title>Register</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
	            
		            <div class="login-box" style="width:350px; background-image: url('images/table-images/cell-blue-register.jpg');">
		            		
						<h1 class="login-header">Register</h1>
						<form method="post" action="">
							<p style="margin-left: 10px">Username <input type="text" name="username" size="18"/></p>
							<p style="margin-left: 10px">Email <input type="text" name="email" size="18"/></p>
							<p style="margin-left: 10px">Password <input type="password" name="password" size="18"/></p>						
							<p style="margin-left: 10px">Confirm Password <input type="password" name="c_password" size="18" /></p>
							<p style="margin-left: 10px; color: gray; font-size: 12px">For username use letters only</p>
							<input type="hidden" name="login" value="true" size="18"/>
							<?php
							
							  if(count($errors) > 0){
								echo '<ul>';
								foreach($errors as $e){
									echo '<li>' . $e . '</li>';
								}
								echo '</ul>';
							  }
							 				
							?>
							<input type="image" value="Register" src="images/register.png" name="login" alt="Submit" style="margin-left: 50px"/>
					
							<br></br>							
						</form>
												                          
		            </div>
		            <div style="text-align:center; padding-top:20px;">
		            	<img src="images/DIMES.gif" style="margin-top: 20px">	
		            </div>
	            </div>
	            
				<div class="about">
					<h3>User Registration Form </h3>   
		    		<p>Dear user, please complete the following form.</br>
		    		After an aproval of your request by the system admin,
		    		you'll be able to login into the system using the security credentials provided here.</p>
		    		<p>An example of the result file:</p>
		    		
		    		<div align="center">
				    	<img src="images/earth_example.jpg">	
				    </div>				    		
				</div>
				
					           
	            <div class="footer">
                	Copyright © 2011 <a href="http://www.netdimes.org/new/">DIMES</a>
            	</div>
         </div>
    </body>
</html>