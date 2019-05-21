<?php
use php\clases\AdministradorCorreo;
use php\modelos\Usuario;
use php\modelos\Resultado;


include '../clases/Resultado.php';
include '../modelos/Usuario.php';
include '../clases/AdministradorCorreo.php';
$administrador_correo = new AdministradorCorreo();
$usuario = new Usuario();
$usuario->nombre = "Alan";
$usuario->contrasena = "123";
$usuario->nombreUsuario = "alanbazan@hotmail.com";
$usuario->permisoSAHA = 1;
$usuario->permisoSIVAH = 1;
$usuario->permiso10y7 = 1;
echo $administrador_correo->enviarCorreoBienvenida($usuario);
