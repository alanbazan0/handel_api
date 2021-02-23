<?php
session_start();
$variable = null;
if(isset($_SESSION['variable']))
    $variable = $_SESSION['variable'];
var_dump($variable);
?>
