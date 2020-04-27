<?php
namespace php\clases;
use mysqli;
use mysqli_sql_exception;


class AdministradorConexion
{
    private $servidor = "apps-handel.com";
    private	$basedatos = "appshand_saha";
    private	$usuario = "appshand_saha";
    private	$contrasena ="dt=02wq~CEzk";

    public function abrir()
    {
        define("MYSQL_CONN_ERROR", "Unable to connect to database.");
        
        mysqli_report(MYSQLI_REPORT_STRICT);
        try
        {
            $mysqli = new mysqli($this->servidor,$this->usuario,$this->contrasena,$this->basedatos);
            if($mysqli)
            {
                $mysqli->set_charset("utf8");
                ini_set('max_execution_time', 300);
                return $mysqli;
                
            }
        }
        catch (mysqli_sql_exception $e)
        {
            throw $e;
        }
        return null;
    }
    
  
    public function cerrar($connection)
    {
        if($connection)
            //mysqli_close($connection);
            $connection->close();
    }
    
}
?>