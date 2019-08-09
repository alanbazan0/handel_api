<?php
session_start();

// store session data
if (isset($_SESSION['usuario']))
    $_SESSION['usuario'] = $_SESSION['usuario']; 

 echo 'ok';
?>