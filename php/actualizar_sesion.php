<?php
$origin = "*";
if(isset($_SERVER['HTTP_ORIGIN']))
    $origin =$_SERVER['HTTP_ORIGIN'];
header('Access-Control-Allow-Origin: '.$origin);
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Credentials: true');

session_start();

// store session data
if (isset($_SESSION['usuario']))
    $_SESSION['usuario'] = $_SESSION['usuario']; 

 echo 'ok';
?>