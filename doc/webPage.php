<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>PoP/AS Visualization</title>        
        <script src="../js/jquery-1.6.2.min.js" type="text/javascript"></script>
        <link rel="stylesheet" href="../css/visual.css" type="text/css" media="screen" />          
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
