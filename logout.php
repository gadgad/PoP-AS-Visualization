<?php

	/*
	 * loging off and planting a cookie at the user. 
	 */
	session_start();
	session_destroy();
	setcookie ("username", "", time() - 3600);
	header('Location: welcome.php');
?>