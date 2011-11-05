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
        	h1{
        		color: rgb(112,97,68);
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
                		
				<h1>Intro</h1>
				<h3>The DIMES project</h3>				
				<p><a href="http://www.netdimes.org/new/">DIMES</a> is a distributed scientific research project, aimed to study the structure and topology of the Internet, with the help of a volunteer community.<br/>Part of the project is taking measurements of PoP and AS geographic locations.</p>				
				<p style="font-size:15px;">PoP - An Internet Point of Presence is an access point to the Internet. It is a physical location that houses servers, routers, ATM switches and digital/analog call aggregators.<br/>AS - Within the Internet, an Autonomous System (AS) is a collection of connected Internet Protocol (IP) routing prefixes under the control of one or more network operators that presents a common, clearly defined routing policy to the Internet.</p>
				<p>The PoP/AS Visualizer is a final project by Gadi Sirota & Keren Ben-Arosh executed at TAU's Networks labratory.</p>
				
				<h1>Info</h1>
				<p>The POP/AS visualization system is a visual, easy-to-use interface to the NetDimes database, providing PoP-level PoP-AS geographic information. <br/>It allows querying the DB and viewing the results on an interactive map.</p>
				
				<h3>Personalized user page</h3>
				<p>The user can generate new queries with the menu on the left. The query data is drivn from the DIMES DB. <br/>On the right the user can view his previous queries and details.</p>	
				<div align="left"><img src="../images/index.png" /></div>
										
			
				<h3>Visualization page</h3>
				<p>The visualization page displays the geographic information on a Google Earth plugin. PoPs are represented by bookmarks and for each AS a different color is given.<br/>On the side panel the user cn control colors, visibility and more.</p>
				<div align="center"><img src="../images/example1.jpg" /></div>
				
				<h3>Visualization examples</h3>
				<div align="center"> <img src="../images/week42.png" /> </div>
				<br />
				<br />
				<div align="center"> <img src="../images/earth_example.jpg" /> </div>
				
	            		
	            					
				<br></br>					
					                          
            </div>
            
            <div id="documentation" style="width: 100%; margin: 30px">
                		
				<h1>Documentation</h1>					
			
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
