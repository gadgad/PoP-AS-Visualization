<?php

	/*
	 * loging off and expiring the cookie at the client's browser. 
	 */
	session_start();
	session_destroy();
	setcookie ("username", "", time() - 3600);
	header('Location: welcome.php');
?>