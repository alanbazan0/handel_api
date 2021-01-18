<?php
include 'configuracion.php';
$origin = "*";
if(isset($_SERVER['HTTP_ORIGIN']))
    $origin =$_SERVER['HTTP_ORIGIN'];
header('Access-Control-Allow-Origin: '.$origin);
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Credentials: true');

session_start();

if(isset($_SESSION['tiempo_sesion']))
{
    
    //Figure out how many seconds have passed
    //since the user was last active.
    $secondsInactive = time() - $_SESSION['tiempo_sesion'];
    
    //Convert our minutes into seconds.
   // $expireAfterSeconds = $expireAfter ;
    //Check to see if they have been inactive for too long.
    if($secondsInactive >= $expireAfter)
    {
        //User has been inactive for too long.
        //Kill their session.
        session_unset();
        session_destroy();
        
       // $_SESSION['usuario'] = $_SESSION['usuario']; 
    }
    else 
    {
        echo($expireAfter - $secondsInactive);
    }
   
}
else
    echo(0);


   

   
    
   
 
 
?>