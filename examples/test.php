<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Hello jQuery</title>
        <script src="Scripts/jquery-1.4.4.js" 
          type="text/javascript"></script>
    </head>
    <body>
        <?php
        // put your code here
        $username="codeLimited";
        $password="codeLimited";
        $database="DIMES_DISTANCES";
        mysql_connect('127.0.0.1:5554',$username);
        echo "connected!";
        mysql_close();
        ?>
    </body>
</html>
