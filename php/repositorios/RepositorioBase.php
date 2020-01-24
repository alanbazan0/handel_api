<?php
namespace php\repositorios;
require_once("../clases/Resultado.php");
use php\modelos\Resultado;
class RepositorioBase
{
    public function calcularId($campoId, $tabla)
    {
        $resultado = new Resultado();
        $consulta =  "SELECT IFNULL(MAX($campoId),0)+1 AS id FROM $tabla"; 
       
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->execute())
            {
                if ($sentencia->bind_result($id))
                {                   
                    if($sentencia->fetch())
                    {
                        $resultado->valor = $id;
                    }
                    else
                        $resultado->mensajeError =  __FUNCTION__. " No se encontró ningún resultado";
                }
                else
                    $resultado->mensajeError =  __FUNCTION__. " Falló el enlace del resultado";
            }
            else
                $resultado->mensajeError =  __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
        }
        else
            $resultado->mensajeError =  __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        return $resultado;
    }
    
    
    public function groupBy($campos)
    {
        $alias = "GROUP BY ";
        for ($i = 0; $i < count($campos);$i++)
        {
            $campo = $campos[$i];
            $alias.=$campo->alias;
            if($i < count($campos) - 1)
                $alias.=", "; 
        }
        return $alias;
    }
    
    public function orderBy($campos)
    {
        $alias = "ORDER BY ";
        for ($i = 0; $i < count($campos);$i++)
        {
            $campo = $campos[$i];
            $alias.=$campo->alias;
            if($i < count($campos) - 1)
                $alias.=", ";
        }
        return $alias;
    }
    
    public function where($filtros)
    {
        $texto = "";
        if($filtros)
        {
            $count = count($filtros);
            if($count>0)
            {
                $texto = " WHERE ";
                for($i = 0; $i < $count; $i++)
                {
                    $filtro = $filtros[$i];
                    $tabla="";
                    if(isset($filtro->tabla))
                        $tabla = $filtro->tabla . ".";
                    if($this->esCadena($filtro->tipoDato))
                    {                   
                        if(isset($filtro->operador))
                        {
                            if($filtro->operador=="IN")
                                $texto .= trim($tabla) . trim($filtro->campo) . " IN (" . $filtro->valor.") ";
                            else
                                $texto .= trim($tabla) . trim($filtro->campo) . " " . $filtro->operador . " ? ";
                        }
                        else
                            $texto .= trim($tabla) . trim($filtro->campo) . " LIKE CONCAT('%',?,'%') ";
                    }
                    else if($this->esFecha($filtro->tipoDato))
                    {
                        if(isset($filtro->operador))
                        {
                            if($filtro->operador=="IN")
                                $texto .= trim($tabla) . trim($filtro->campo) . " IN (" . $filtro->valor.") ";
                            else
                                $texto .= trim($tabla) . trim($filtro->campo) . " " . $filtro->operador . " ? ";
                        }
                        else
                            $texto .= trim($tabla) . trim($filtro->campo) . " = ? ";
                    }
                    else
                    {
                        if(isset($filtro->operador))
                        {
                            if($filtro->operador=="IN")
                                $texto .= trim($tabla) . trim($filtro->campo) . " IN (" . $filtro->valor.") ";
                            else
                                $texto .= trim($tabla) . trim($filtro->campo) . " " . $filtro->operador . " ? ";
                        }
                        else       
                            $texto .= trim($tabla) . trim($filtro->campo) . " = ? ";
                    }
                    if($i < count($filtros) - 1)
                        $texto .= " AND ";
                        
                }
             }
        } 
        return $texto;
    }
    
    public function and($filtros)
    {
        $texto = "";
        if($filtros)
        {
            $count = count($filtros);
            if($count>0)
            {
                $texto = " AND ";
                for($i = 0; $i < $count; $i++)
                {
                    $filtro = $filtros[$i];
                    $tabla="";
                    if(isset($filtro->tabla))
                        $tabla = $filtro->tabla . ".";
                        if($this->esCadena($filtro->tipoDato))
                        {
                            if(isset($filtro->operador))
                            {
                                if($filtro->operador=="IN")
                                    $texto .= trim($tabla) . trim($filtro->campo) . " IN (" . $filtro->valor.") ";
                                    else
                                        $texto .= trim($tabla) . trim($filtro->campo) . " " . $filtro->operador . " ? ";
                            }
                            else
                                $texto .= trim($tabla) . trim($filtro->campo) . " LIKE CONCAT('%',?,'%') ";
                        }
                        else if($this->esFecha($filtro->tipoDato))
                        {
                            if(isset($filtro->operador))
                            {
                                if($filtro->operador=="IN")
                                    $texto .= trim($tabla) . trim($filtro->campo) . " IN (" . $filtro->valor.") ";
                                    else
                                        $texto .= trim($tabla) . trim($filtro->campo) . " " . $filtro->operador . " ? ";
                            }
                            else
                                $texto .= trim($tabla) . trim($filtro->campo) . " = ? ";
                        }
                        else
                        {
                            if(isset($filtro->operador))
                            {
                                if($filtro->operador=="IN")
                                    $texto .= trim($tabla) . trim($filtro->campo) . " IN (" . $filtro->valor.") ";
                                    else
                                        $texto .= trim($tabla) . trim($filtro->campo) . " " . $filtro->operador . " ? ";
                            }
                            else
                                $texto .= trim($tabla) . trim($filtro->campo) . " = ? ";
                        }
                        if($i < count($filtros) - 1)
                            $texto .= " AND ";
                            
                }
            }
        }
        return $texto;
    }
    
    public function bind_param($sentencia, $filtros)
    {
        $bind = false;
        $filtros = $this->eliminarFiltrosIN($filtros);
        if(count($filtros)>0)
        {
            $types = $this->types($filtros);
            $bind_names[] = $types;
            for ($i=0; $i<count($filtros);$i++)
            {
                $bind_name = 'bind' . $i;
                $$bind_name = $filtros[$i]->valor;
                $bind_names[] = &$$bind_name;
            }
            $bind = call_user_func_array(array($sentencia,'bind_param'),$bind_names);
        }
        else
            $bind = true;
       return $bind;
    }
    
    private function eliminarFiltrosIN($filtros)
    {
        $filtrosIN = array();
        for ($i=0; $i<count($filtros);$i++)
        {   
           $filtro = $filtros[$i];
           if(isset($filtro->operador))
           {
               if($filtro->operador=="IN")
               {
                   
               }
               else
                   array_push($filtrosIN, $filtro);
               
           }
           else
               array_push($filtrosIN, $filtro);
        }
        return $filtrosIN;
    }
    
    
    public function get_result($sentencia)
    {
        $registros = array();
        $meta = $sentencia->result_metadata();        
        while ($field = $meta->fetch_field()) {
            $var = $field->name;
            $$var = null;
            $fields[$var] = &$$var;
        }
        $bind = call_user_func_array(array($sentencia, 'bind_result'), $fields);
        
        $i = 0;
        while ($sentencia->fetch()) 
        {
            $results[$i] = array();
            foreach($fields as $k => $v)
                $results[$i][$k] = $v;
            $i++;
        }    
        return $results;
    }
    
    public function types($filtros)
    {
        $texto = "";
        if($filtros)
        {           
            for($i = 0; $i < count($filtros); $i++)
            {
                $filtro = $filtros[$i];
                if($this->esCadena($filtro->tipoDato) || $this->esFecha($filtro->tipoDato))
                    $texto .= "s";
                else
                    $texto .= "i";
            }
        }
        return $texto;
    }
    
    public function select($campos)
    {        
        $texto = "";
        if($campos)
        {
            $texto = "SELECT ";
            for($i = 0; $i < count($campos); $i++)
            {
                $campo = $campos[$i];
                $texto .= trim($campo->tablaId) . "." . trim($campo->campoId) . " AS C" . $campo->orden ;
                if($i < count($campos) - 1)
                    $texto .= ", ";
            }
        }
       
        return $texto;
    }
    
    public function selectAlias($campos)
    {
        $texto = "";
        if($campos)
        {
            $texto = "SELECT ";
            for($i = 0; $i < count($campos); $i++)
            {
                $campo = $campos[$i];
                $texto .= trim($campo->tabla) . "." . trim($campo->campo) . " AS " . $campo->alias ;
                if($i < count($campos) - 1)
                    $texto .= ", ";
            }
        }
        
        return $texto;
    }
    public function insert($tablaId, $campos)
    {
        $texto = "";
        if($campos)
        {
            $texto = "INSERT INTO ".$tablaId." ( ";
            for($i = 0; $i < count($campos); $i++)
            {
                $campo = $campos[$i];
                $texto .= trim($campo->campoId);
                if($i < count($campos) - 1)
                    $texto .= ", ";
            }
            $texto .= ') VALUES( ';
            for($i = 0; $i < count($campos); $i++)
            {
                $texto .= '?' ;
                if($i < count($campos) - 1)
                    $texto .= ", ";
            }
            $texto .= ')';
        }      
        
        return $texto;
    }
    
    public function update($tablaId, $campos)
    {
        $texto = "";
        if($campos)
        {
            $texto = "UPDATE ".$tablaId." SET ";
            for($i = 0; $i < count($campos); $i++)
            {
                $campo = $campos[$i];
                $texto .= trim($campo->campoId) ." = ? ";
                if($i < count($campos) - 1)
                    $texto .= ", ";
            }
        }
        
        return $texto;
    }
    
    public function esCadena($tipoDato)
    {
        if(strtolower($tipoDato) == 'varchar')
            return true;
        else 
            return false;
    }
    
    
    public function esFecha($tipoDato)
    {
        if(strtolower($tipoDato) == 'date')
            return true;
            else
                return false;
    }
}

