<!-- shows the PoP/AS visualizaiton header, welcome msg and logout option -->
<div id="header">
	<p>
        <h5 style="text-align: left; margin-left: 5px">Welcome <?php echo $username; ?>,                	
        	<a href="logout.php"> <u>Logout</u></a>	<?php if ($username=="admin" && basename($_SERVER['REQUEST_URI'],".php")!="admin"){echo 'or <a href="admin.php"> <u>Go to admin page</u></a>';}?> 
        </h5>
    	<div class="main-title">
    		<img src="images/logo.png">
    	</div>
    </p>                
</div>