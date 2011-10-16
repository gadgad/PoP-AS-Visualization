<?php

	/*
	 * this file prevents unauthorized users to enter internal pages. 
	 * a non-logged user could only reach welcome.php
	 */

	$valid_requests = array('index.php','admin.php','visual_frontend.php');
	session_start();
	$username = isset($_COOKIE['username'])? $_COOKIE['username'] : $_SESSION['username'];
	if(!file_exists('users/' . $username . '.xml')){
		if(in_array(basename($_SERVER['REQUEST_URI']), $valid_requests))
			$_SESSION['request_url'] = $_SERVER['REQUEST_URI'];
		session_write_close();
		header('Location: welcome.php');
		die;
	}
?>
