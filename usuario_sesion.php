<?php
session_start();
$usuario = null;
if(isset($_SESSION['usuario']))
    $usuario = $_SESSION['usuario'];
var_dump($usuario);
?>
