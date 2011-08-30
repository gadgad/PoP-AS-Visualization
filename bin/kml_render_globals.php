<?php
	function setGlobal($param,$value) {
		if(isset($_POST['submitted'])) {
			if(is_bool($value)){
				define($param,isset($_POST[$param]));
				setcookie($param,isset($_POST[$param])? 1:0,time()+3600*24*30);
				//$_COOKIE[$param] = isset($_POST[$param])? 1:0;	
			} else if(isset($_POST[$param])){
				define($param,$_POST[$param]);
				setcookie($param,$_POST[$param],time()+3600*24*30);
				//$_COOKIE[$param] = $_POST[$param];	
			}
		} else if(isset($_COOKIE[$param])){
				define($param,$_COOKIE[$param]);
		} else { // not submitted and no data in cookie
			if(is_bool($value)){
				setcookie($param,($value)?1:0,time()+3600*24*30);
			} else {
				setcookie($param,$value,time()+3600*24*30);
			}	
			define($param,$value);
		}
	}
	
	setGlobal('MIN_LINE_WIDTH',3);
	setGlobal('MAX_LINE_WIDTH',5);
	setGlobal('INITIAL_ALTITUDE',10);
	setGlobal('ALTITUDE_DELTA',5000);
	setGlobal('STDEV_THRESHOLD',2);
	setGlobal('DRAW_CIRCLES',true);
	setGlobal('INTER_CON',true);
	setGlobal('INTRA_CON',true);
	setGlobal('CONNECTED_POPS_ONLY',true);
	setGlobal('USE_COLOR_PICKER',false);
	
?>