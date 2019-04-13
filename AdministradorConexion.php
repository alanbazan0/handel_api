<?php
namespace clases;
use mysqli;


class AdministradorConexion
{
    private $servidor = "handelscenet.fatcowmysql.com";
    private	$basedatos = "handel_bd";
    private	$usuario = "handel_admin";
    private	$contrasena ="h4nd3l";
    public function abrir()
    {
        return new mysqli($this->servidor,$this->usuario,$this->contrasena,$this->basedatos);
    }

    public function cerrar($connection)
    {
        if($connection)
            $connection->close();
    }

}
