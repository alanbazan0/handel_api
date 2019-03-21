<?php
use php\modelos\Usuario;

function GET($name, $def = '')
{
    if (isset($_GET[$name]))
        return $_GET[$name];
        else
            return $def;
}

function REQUEST($name, $def = '')
{
    if (isset($_REQUEST[$name]))
        return $_REQUEST[$name];
        else
            return $def;
}

function GET_IP()
{
    if (isset($_SERVER['REMOTE_ADDR'])) 
        return $_SERVER['REMOTE_ADDR'];
  
    return "";
}

function FILES($name)
{
    if (isset($_FILES[$name]))
        return $_FILES[$name];
        else
            return null;
}

function copyright()
{
    return "<p>Copyright © 2018 Apps Handel. Todos los derechos reservados. <a href='https://apps-handel.com'>Apps Handel</a>.</p>";
    
}

function usuarioDefault()
{
    $usuario = new Usuario();
    $usuario->nombre ="Alan"; 
    $usuario->apellido ="Bazán"; 
    $usuario->tipoUsuarioId = 6; 
    $usuario->nombreUsuario ="contact@alanbazan.net";
    return $usuario;
}

function reArrayFiles($file)
{
    $file_ary = array();
    $file_count = count($file['name']);
    $file_key = array_keys($file);
    
    for($i=0;$i<$file_count;$i++)
    {
        foreach($file_key as $val)
        {
            $file_ary[$i][$val] = $file[$val][$i];
        }
    }
    return $file_ary;
}
