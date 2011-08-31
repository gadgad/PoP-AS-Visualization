<?php
	session_start();
	$username = isset($_COOKIE['username'])? $_COOKIE['username'] : $_SESSION['username'];
	if(!file_exists('users/' . $username . '.xml')){
		$_SESSION['request_url'] = $_SERVER['REQUEST_URI'];
		session_write_close();
		header('Location: welcome.php');
		die;
	}
?>
