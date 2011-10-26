<html>
    <head>
        
        <style type="text/css">
        	p{
        		margin-left: 50 px;
        		font-family: Arial;
        	}
        </style>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>PoP/AS Visualization</title>        
        <script src="../js/jquery-1.6.2.min.js" type="text/javascript"></script>
        <link rel="stylesheet" href="../css/visual.css" type="text/css" media="screen" />
        <style type="text/css">
        	p{
        		margin-left: 50 px;
        		font-family: Arial;
        		line-height: 150%;        		
        	}
        	h3{
        		color: rgb(54,95,145);
        	}
        </style>          
    </head>

    <body>        
        

            <div id="header">
                <div class="main-title">
                	<img src="../images/logo.png" style="margin-top:25px">
                </div>
                <img src="../images/DIMES.gif" style="margin-right: 30px;float: right; clear: left">
            </div>
						                       
            <div id="information" style="width: 100%; margin: 30px">
                		
				<h1 class="login-header">Info</h1>
				<p>The POP/AS visualization system is a visual, easy-to-use interface to the NetDimes database, providing PoP-level PoP-AS geographic information. </p>
				<p>It allows querying the DB and viewing the results on an interactive map.</p>
				<h3>Personalized user page</h3>
				<div style = "width: 100%;">
					
					<div style = "width: 30%; float: left; height: 60%">
						<p>The user can generate new queries with the menu on the left.</p>
						<p>On the right the user can view his previous queries and details.</p>
						<br />
					</div>
					<div style = "width: 70%; float: right; height: 60%"> <img src="../images/index.png" /> </div>
					
				</div>
				<h3>Visualization examples</h3>
				<div align="center"> <img src="../images/week42.png" /> </div>
				<br />
				<br />
				<div align="center"> <img src="../images/earth_example.jpg" /> </div>
				
	            		
	            					
				<br></br>					
					                          
            </div>
            
            <div id="documentation" style="width: 100%; margin: 30px">
                		
				<h1 class="login-header">Documentation</h1>					
			
				<?php 
		 
			        if(file_exists("User Guide.pdf")) {
			          echo ("<p>Full explaination regarding system use available in the <a href=User Guide.pdf>User Guide</a></p>");
			        }
					
					if(file_exists("Project Book.pdf")) {
			          echo ("<p>To learn more aboute system design and achitecture -  <a href=Project Book.pdf>Project Book</a></p>");
			        }
					
					if(file_exists("Installation Guide.pdf")) {
			          echo ("<p>For installing, use the <a href=Installation Guide.pdf>Installation Guide</a></p>");
			        }
				?>				
					                          
            </div>
	           
            
            
            
        <div class="footer">
            Copyright © 2011 <a href="http://www.netdimes.org/new/">DIMES</a>
        </div>
            
    </body>
</html>
