<?php
	session_start();
	$username = isset($_COOKIE['username'])? $_COOKIE['username'] : $_SESSION['username'];
	if(!file_exists('users/' . $username . '.xml')){
		header('Location: welcome.php');
		die;
	}
?>
