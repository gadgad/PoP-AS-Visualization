<div class="about">
    <h3>About the project</h3>   
    <p style="line-height:150%;padding-left: 30px;padding-right: 30px;">The POP-AS Visualizer was created to visualize geographic data and present it in an intuitive way. With the system you can query the DIMES database for POP-AS info and view the results with the Google earth plugin.</p>
	<p style="line-height:150%;padding-left: 30px;padding-right: 30px;">
	  <?php
	    $filename = "User Guide.pdf"; 
        if(file_exists($filename)) {
          echo ("for more information download the <a href=\"$filename\">user guide</a>");
        } else {
          echo( "Oops.. the user guide is temporary unavailable." );
        }
    ?>    	
	</p>             	            
    <p>An example of the result file:</p>
    <div align="center">
    	<img src="images/earth_example.jpg">	
    </div>
    
</div>