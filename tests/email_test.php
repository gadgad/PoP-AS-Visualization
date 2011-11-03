<?php
$path =  "users/gadi.xml";
$userData = simplexml_load_file($path);
$res = $userData->xpath('/user/email');
echo print_r((string)$res[0],true);
?>