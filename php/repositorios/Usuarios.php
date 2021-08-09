<?php
use php\clases\AdministradorConexion;
use php\clases\AdministradorArchivos;
use php\clases\JsonMapper;
use php\modelos\Usuario;

use php\repositorios\UsuariosRepositorio;
use php\repositorios\HistorialAccesoRepositorio;
use php\modelos\Resultado;
use php\clases\AdministradorCorreo;

error_reporting(E_ALL);
ini_set('display_errors', 1);


include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../configuracion.php';
include '../clases/AdministradorConexion.php';
include '../clases/AdministradorArchivos.php';
include '../clases/AdministradorCorreo.php';
include '../modelos/Usuario.php';
include '../clases/TipoUsuario.php';
include '../repositorios/UsuariosRepositorio.php';
include "../repositorios/HistorialAccesoRepositorio.php";




$origin = "*";
if(isset($_SERVER['HTTP_ORIGIN']))
    $origin =$_SERVER['HTTP_ORIGIN'];
header('Access-Control-Allow-Origin: '.$origin);
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Credentials: true');


// if($session_cookie_domain!="")
//     ini_set('session.cookie_domain', $session_cookie_domain);

$administrador_conexion = new AdministradorConexion();
$resultado = new Resultado();
$conexion=null;
try
{
    $conexion = $administrador_conexion->abrir();
    if($conexion)
    {
        $accion = REQUEST('accion');
        $repositorio = new UsuariosRepositorio($conexion);
        switch ($accion)
        {           
            case 'insertar':
               
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new Usuario());     
                
             
                $resultado = $repositorio->insertar($modelo);     
                if($resultado->mensajeError=="")
                {
                    $administrador_correo = new AdministradorCorreo();
                    if($modelo->nombreUsuario!="" && $modelo->nombreUsuario!=null && $modelo->tipoUsuarioId!=TipoUsuario::CAPACITADO)
                        $resultado = $administrador_correo->enviarCorreoBienvenida($modelo);
                }
            break;
            case 'reenviarCorreo':
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->consultarPorLlaves($llaves);
                if($resultado->correcto())
                {
                    $usuario = $resultado->valor;
                    $administrador_correo = new AdministradorCorreo();
                    if($usuario->nombreUsuario!="" && $usuario->nombreUsuario!=null)
                        $resultado = $administrador_correo->enviarCorreoBienvenida($usuario);
                }
            break;
            case 'recuperar':
                $correoElectronico = REQUEST('correoElectronico');
                $resultado = $repositorio->consultarPorCorreoElectronico($correoElectronico);
                if($resultado->correcto())
                {
                    $usuario = $resultado->valor;
                    $administrador_correo = new AdministradorCorreo();
                    if($usuario->nombreUsuario!="" && $usuario->nombreUsuario!=null)
                        $resultado = $administrador_correo->enviarCorreoRecuperacion($usuario);
                }
                break;
            case 'actualizar':
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new Usuario());                
                $resultado = $repositorio->actualizar($modelo) ;
            break;            
            case 'eliminar':
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->eliminar($llaves);
            break;
            case 'consultarSupervisoresPorEmpresa':
                $empresaId = REQUEST('empresaId');
                $usuarioId = REQUEST('usuarioId');
                $resultado = $repositorio->consultarSupervisoresPorEmpresa($empresaId,$usuarioId);              
            break;
            case 'consultarUsuariosCorportarivoYAdministradores':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $resultado = $repositorio->consultarUsuariosCorportarivoYAdministradores($usuario);
            break;
            case 'consultarUsuariosCorportarivoYAdministradoresPorEmpresa':
                $empresaId = REQUEST('empresaId');
                $resultado = $repositorio->consultarUsuariosCorportarivoYAdministradoresPorEmpresa($empresaId);
            break;
            case 'consultarUsuariosCorportarivoYAdministradoresPorEmpresaSIVAH':
                $empresaId = REQUEST('empresaId');
                $resultado = $repositorio->consultarUsuariosCorportarivoYAdministradoresPorEmpresaSIVAH($empresaId);
            break;
            case 'consultarUsuariosCorportarivoPorEmpresaSIVAH':
                $empresaId = REQUEST('empresaId');
                $resultado = $repositorio->consultarUsuariosCorportarivoPorEmpresaSIVAH($empresaId);
            break;
            case 'consultarPorEmpresaSede':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $opcional = REQUEST('opcional');
                $empresaId = REQUEST('empresaId');
                $sedeId = REQUEST('sedeId');
                $resultado = $repositorio->consultarPorEmpresaSede($usuario,$empresaId,$sedeId,$opcional);
            break;
            case 'consultarPorLlaves':
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->consultarPorLlaves($llaves);
            break;
            case 'consultar':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                
                if($usuario!=null)
                {
                    $opcional = REQUEST('opcional');
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultar($usuario,$criteriosSeleccion,$opcional);
                }
                else
                {
                    $resultado->mensajeError = "La sesión caducó. Inicie sesión e intente de nuevo.";
                    $resultado->codigoError = 10;
                }
            break;
            case 'consultarSimple':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                    
                    if($usuario!=null)
                    {
                        $opcional = REQUEST('opcional');
                        $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                        $resultado = $repositorio->consultarSimple($usuario,$criteriosSeleccion,$opcional);
                    }
                    else
                    {
                        $resultado->mensajeError = "La sesión caducó. Inicie sesión e intente de nuevo.";
                        $resultado->codigoError = 10;
                    }
            break;
            case 'consultarPorPermiso':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $opcional = REQUEST('opcional');
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultarPorPermiso($usuario,$criteriosSeleccion,$opcional);
            break;
            case 'consultarAdministradores':
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultarAdministradores($criteriosSeleccion);
            break;
            case 'consultarPermisos':
                $nombreUsuario = REQUEST('nombreUsuario');
                $contrasena = REQUEST('contrasena');
                $resultado = $repositorio->consultarUsuario($nombreUsuario,$contrasena);
                $permisos = [];
                if($resultado->valor!=null)
                {
                    $usuario = $resultado->valor;
                    if($usuario->permisoSAHA==1)
                        array_push($permisos,(object)['id'=>'SAHA','nombre'=>'SAHA','imagen'=>'images/logoSAHA.png']);
                    if($usuario->permisoSIVAH==1)
                        array_push($permisos,(object)['id'=>'SIVAH','nombre'=>'SIVAH','imagen'=>'images/logoSIVAH.png']);
                    if($usuario->permiso10y7==1)
                        array_push($permisos,(object)['id'=>'10y7','nombre'=>'10y7','imagen'=>'images/logo10y7.png']);
                }
                $resultado->valor = $permisos;
            break;
            case 'iniciarSesion':
                session_start();
                $nombreUsuario = REQUEST('nombreUsuario');
                $contrasena = REQUEST('contrasena');
                $aplicacionId = REQUEST('aplicacionId');
                $aplicacionVersion = REQUEST('aplicacionVersion');
                $resultado = $repositorio->consultarUsuario($nombreUsuario,$contrasena);
                if($resultado->valor!=null)
                {
                    $resultado->valor->contrasena = "*****";
                    $validarSeguridad = true;
                    if($validarSeguridad)
                    {
                        $tienePermiso = false;
                        if($resultado->valor->tipoUsuarioId == TipoUsuario::ADMINISTRADOR)
                            $tienePermiso = true;
                        else
                        {
                            switch($aplicacionId)
                            {
                                case "SAHA":
                                    $tienePermiso =  $resultado->valor->permisoSAHA==1?true:false;
                                    break;
                                case "SIVAH":
                                    $tienePermiso =  $resultado->valor->permisoSIVAH==1?true:false;
                                    break;
                                case "10y7":
                                    $tienePermiso =  $resultado->valor->permiso10y7==1?true:false;
                                break;
                                case "CAVI":
                                    $tienePermiso =  $resultado->valor->permisoCAVI==1?true:false;
                                break;
                            }
                        }
                        
                        if($resultado->valor->estatus!=1)
                            $tienePermiso = false;
                       
                        if($tienePermiso)
                        {
                                switch($aplicacionId)
                                {
                                    case "SAHA":
                                        if($resultado->valor->tipoUsuarioId == TipoUsuario::ADMINISTRADOR || $resultado->valor->tipoUsuarioId == TipoUsuario::COORDINADOR || $resultado->valor->tipoUsuarioId == TipoUsuario::SUPERVISOR ||  $resultado->valor->tipoUsuarioId == TipoUsuario::USUARIO)
                                        {
                                            $_SESSION['usuario']=$resultado->valor;
                                            $historialAccesoRepositorio = new HistorialAccesoRepositorio($conexion);
                                            $historialAccesoRepositorio->insertar($nombreUsuario,$aplicacionId,$aplicacionVersion);
                                        }
                                        else
                                        {
                                            $resultado->valor = null;
                                            $resultado->mensajeError="El acceso a la plataforma en linea esta restringido a usuarios autorizados, si necesita ingresar para realizar cambios por favor solicite los cambios con su supervisor autorizado.";
                                            unset($_SESSION['usuario']);
                                        }
                                    break;
                                    case "SIVAH":
                                        //if($resultado->valor->tipoUsuarioId == TipoUsuario::ADMINISTRADOR || $resultado->valor->tipoUsuarioId == TipoUsuario::COORDINADOR || $resultado->valor->tipoUsuarioId == TipoUsuario::SUPERVISOR)
                                       // {
                                            $_SESSION['usuario']=$resultado->valor;
                                            $historialAccesoRepositorio = new HistorialAccesoRepositorio($conexion);
                                            $historialAccesoRepositorio->insertar($nombreUsuario,$aplicacionId,$aplicacionVersion);
//                                         }
//                                         else
//                                         {
//                                             $resultado->valor = null;
//                                             $resultado->mensajeError="El acceso a la plataforma en linea esta restringido a usuarios autorizados, si necesita ingresar para realizar cambios por favor solicite los cambios con su supervisor autorizado.";
//                                             unset($_SESSION['usuario']);
//                                         }
                                    break;
                                    case "10y7":
                                        if($aplicacionVersion=="html")
                                        {
                                            if($resultado->valor->tipoUsuarioId == TipoUsuario::ADMINISTRADOR || $resultado->valor->tipoUsuarioId == TipoUsuario::COORDINADOR || $resultado->valor->tipoUsuarioId == TipoUsuario::SUPERVISOR)
                                            {
                                                $_SESSION['usuario']=$resultado->valor;
                                                
                                                $historialAccesoRepositorio = new HistorialAccesoRepositorio($conexion);
                                                $historialAccesoRepositorio->insertar($nombreUsuario,$aplicacionId,$aplicacionVersion);
                                            }
                                            else
                                            {
                                                $resultado->valor = null;
                                                $resultado->mensajeError="El acceso a la plataforma en linea esta restringido a usuarios autorizados, si necesita ingresar para realizar cambios por favor solicite los cambios con su supervisor autorizado.";
                                                unset($_SESSION['usuario']);
                                            }
                                        }
                                        else
                                        {
                                            if($resultado->valor->tipoUsuarioId == TipoUsuario::COORDINADOR || $resultado->valor->tipoUsuarioId == TipoUsuario::SUPERVISOR || $resultado->valor->tipoUsuarioId == TipoUsuario::USUARIO)
                                            {
                                                $_SESSION['usuario']=$resultado->valor;
                                                $historialAccesoRepositorio = new HistorialAccesoRepositorio($conexion);
                                                $historialAccesoRepositorio->insertar($nombreUsuario,$aplicacionId,$aplicacionVersion);
                                            }
                                            else
                                            {
                                                $resultado->valor = null;
                                                $resultado->mensajeError="El acceso a la plataforma en linea esta restringido a usuarios autorizados, si necesita ingresar para realizar cambios por favor solicite los cambios con su supervisor autorizado.";
                                                unset($_SESSION['usuario']);
                                            }
                                        }
                                            
                                    break;
                                    case "CAVI":
//                                         if($resultado->valor->tipoUsuarioId == TipoUsuario::ADMINISTRADOR || $resultado->valor->tipoUsuarioId == TipoUsuario::COORDINADOR || $resultado->valor->tipoUsuarioId == TipoUsuario::SUPERVISOR ||  $resultado->valor->tipoUsuarioId == TipoUsuario::CAPACITADO)
//                                         {
//                                             $_SESSION['usuario']=$resultado->valor;
                                            
//                                             $historialAccesoRepositorio = new HistorialAccesoRepositorio($conexion);
//                                             $historialAccesoRepositorio->insertar($nombreUsuario,$aplicacionId,$aplicacionVersion);
//                                         }
//                                         else
//                                         {
//                                             $resultado->valor = null;
//                                             $resultado->mensajeError="El acceso a la plataforma en linea esta restringido a usuarios autorizados, si necesita ingresar para realizar cambios por favor solicite los cambios con su supervisor autorizado.";
//                                             unset($_SESSION['usuario']);
//                                         }
//                                             if($resultado->valor->permisoCAVI==1)
//                                             {
                                                $_SESSION['usuario']=$resultado->valor;
                                                $historialAccesoRepositorio = new HistorialAccesoRepositorio($conexion);
                                                $historialAccesoRepositorio->insertar($nombreUsuario,$aplicacionId,$aplicacionVersion);
//                                             }
//                                             else
//                                             {
//                                                 $resultado->valor = null;
//                                                 $resultado->mensajeError="El acceso a la plataforma en linea esta restringido a usuarios autorizados, si necesita ingresar para realizar cambios por favor solicite los cambios con su supervisor autorizado.";
//                                                 unset($_SESSION['usuario']);
//                                             }
                                      break;
                                    default:
                                        $resultado->valor = null;
                                        $resultado->mensajeError="El acceso a la plataforma en linea esta restringido a usuarios autorizados, si necesita ingresar para realizar cambios por favor solicite los cambios con su supervisor autorizado.";
                                        unset($_SESSION['usuario']);
                                    break;
                                }
                            
                            
                        }
                        else 
                        {
                            $resultado->valor = null;
                            $resultado->mensajeError="El acceso a la plataforma en linea esta restringido a usuarios autorizados, si necesita ingresar para realizar cambios por favor solicite los cambios con su supervisor autorizado.";
                            unset($_SESSION['usuario']);
                            
                        }
                    }
                    else 
                    {
                        $_SESSION['usuario']=$resultado->valor;
                        $historialAccesoRepositorio = new HistorialAccesoRepositorio($conexion);
                        $historialAccesoRepositorio->insertar($nombreUsuario,$aplicacionId,$aplicacionVersion);
                    }
                    
                }
                else
                {
                    unset($_SESSION['usuario']);
                    
                }
            break;
            case "cerrarSesion":
                session_start();
                unset($_SESSION['usuario']);
                unset($_SESSION['url']);
            break;
            case "consultarSesion":
                session_start();
                if(isset($_SESSION['usuario']))
                    $resultado->valor=$_SESSION['usuario'];
            break;
            case "subirFotoPerfil":
                session_start();
                if(isset($_SESSION['usuario']))
                {
                    $usuario = $_SESSION['usuario'];
                    $adminstradorArchivos = new AdministradorArchivos();
                    $archivo = FILES("file");
                    $carpeta = "fotos";
                    $nombreArchivo = "usuario".$usuario->id.".jpg";
                    $resultado=$adminstradorArchivos->subirImagen($carpeta,$archivo,$nombreArchivo);
                    if($resultado->mensajeError=="")
                    {
                        $usuario->fotoPerfil =  "php/fotos/usuario". $usuario->id .".jpg";
                        $_SESSION['usuario'] = $usuario;
                    }
                }
                else
                    $resultado->mensajeError = "No se ha iniciado sesión";
               
            break;
            case 'consultarPorEmpresaSedeArea':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $empresaId = REQUEST('empresaId');
                $sedeId = REQUEST('sedeId');
                $areaId = REQUEST('areaId');
                $opcional = REQUEST('opcional');
                $resultado = $repositorio->consultarPorEmpresaSedeArea($empresaId,$sedeId,$areaId,$opcional,$usuario);
            break;
            case 'consultarPorEmpresaSedeDepartamento':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $empresaId = REQUEST('empresaId');
                $sedeId = REQUEST('sedeId');
                $departamentoId = REQUEST('departamentoId');
                $opcional = REQUEST('opcional');
                $resultado = $repositorio->consultarPorEmpresaSedeDepartamento($empresaId,$sedeId,$departamentoId,$opcional,$usuario);
            break;
            case 'consultarEstructura':
                $empresaId = REQUEST('empresaId');
                $resultado = $repositorio->consultarEstructura($empresaId);
            break;
            case 'importar':
                $empresaId = REQUEST('empresaId');
                $sedeId = REQUEST('sedeId');
                $departamentoId = REQUEST('departamentoId');
                $perfilId = REQUEST('perfilId');
                $supervisor1Id = REQUEST('supervisor1Id');
                
                $adminstradorArchivos = new AdministradorArchivos();
                $archivo = FILES("file");
                $carpeta = "importacion";
                $fecha = new DateTime();
                $nombreArchivo = "importacion" . $fecha->getTimestamp();
                $resultado=$adminstradorArchivos->subirArchivo($carpeta,$archivo,$nombreArchivo);
                if($resultado->correcto())
                {
                    $resultado = $repositorio->importar($empresaId,$sedeId,$departamentoId,$perfilId,$supervisor1Id,$carpeta, $nombreArchivo);
                    if($resultado->correcto())
                    {
                        
                    }
                }
                //$adminstradorArchivos->eliminar($carpeta, $nombreArchivo);
                
            break;
            case 'actualizarPerfil':
                $usuariosIds = REQUEST('usuariosIds');
                $perfilId = REQUEST('perfilId');
                $resultado = $repositorio->actualizarPerfil($usuariosIds,$perfilId);
            break;
//             case 'enviarNotificacion':
//                 session_start();
//                 $usuario = null;
//                 if(isset($_SESSION['usuario']))
//                     $usuario = $_SESSION['usuario'];
//                     $empresaId = REQUEST('empresaId');
//                     $sedeId = REQUEST('sedeId');
//                     $areaId = REQUEST('areaId');
//                     $opcional = REQUEST('opcional');
//                     $resultado = $repositorio->consultarPorEmpresaSedeArea($empresaId,$sedeId,$areaId,$opcional,$usuario);
//                     break;
            default:
                $resultado->mensajeError = 'Acción no válida';
            break;
            
            
            
        }
    }
    
}
catch(Exception $e)
{   
    $resultado->mensajeError = $e->getMessage();
}
finally
{
    if($resultado!=null)
    {
        $json = json_encode($resultado, JSON_PRETTY_PRINT);
        if (FALSE === $json)
            echo '{"mensajeError":"' .json_last_error_msg() . '"}';
            else
                echo $json;
    }
    $administrador_conexion->cerrar($conexion);
}


