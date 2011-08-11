<?php
	session_start();
	$username = $_SESSION['username'];
	if(!file_exists('users/' . $username . '.xml')){
		header('Location: welcome.php');
		die;
	}
?>
