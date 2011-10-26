<?php
	function check_email_address($email) {
		if(preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",$email)){
			list($username,$domain)=split('@',$email);
			if(!checkdnsrr($domain,'MX')) {
		    	return false;
		    }
		    return true;
		  }
		  return false;
	}
?>