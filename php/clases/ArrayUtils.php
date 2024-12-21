<?php
namespace php\clases;

class ArrayUtils
{
    public static function groupBySUM($fields, $operationFields, $records)
    {
        $groups= array();
        if($records!=null)
        {
            $fieldsArray = explode(",",$fields); 
            $operationFieldsArray = explode(",",$operationFields); 
            for($i = 0; $i < count($records); $i++)
            {
                $item = $records[$i]; 
                $group = ArrayUtils::searchGroup($item,$fieldsArray,$groups);
                if($group==null)
                {
                    $group = (object)[];
                    for($j=0; $j<count($fieldsArray); $j++)
                    {
                       // $group[$fieldsArray[$j]] = $item[$fieldsArray[$j]];
                        $group->{$fieldsArray[$j]} = $item->{$fieldsArray[$j]};
                    }
                   /* for($i = 0; $i < count($operationFieldsArray); $i++)
                    {
                        $group->{$operationFieldsArray[$i]} = 0;;
                    }*/
                    $group->data = array();
                    
                    
                    ArrayUtils::addAndCalculateOperations($operationFieldsArray, $group, $item);
                    
                    
                    array_push($groups,$group);
                }
                else
                {
                    ArrayUtils::addAndCalculateOperations($operationFieldsArray, $group, $item);
                    
                }
            }
        }
        
        
        
        return $groups;
    }
    
   static function orderBy($records)
   {
       $getName = function ($registro) {
           return $registro->nombre;
       };
       $names = array_map($getName, $records);
       sort($names);
       $sorted = [];
       foreach ($names as $name) {
           foreach ($records as $record) {
               if ($record->nombre === $name) {
                   $sorted[] = $record;
                   break;
               }
           }
       }
       return $sorted;
   }
   
    
    static function addAndCalculateOperations($operationFields, $group, $item)
    {
        for($i = 0; $i < count($operationFields); $i++)
        {
            ArrayUtils::sum(trim($operationFields[$i]), $group, $item);
        }
        array_push($group->data,$item);
    }
    
    static function sum($operationField, $group, $item)
    {
        if(!isset($group->{$operationField}))
            $group->{$operationField} = 0;
        else if($group->{$operationField}==null)
            $group->{$operationField} = 0;
        
        $group->{$operationField} += $item->{$operationField};
    }
    
    static function searchGroup($item, $arrayFields, $groups)
    {
        $val1 = ArrayUtils::getValues($item, $arrayFields);
        for($i= 0; $i < count($groups); $i++)
        {
            $val2 = ArrayUtils::getValues($groups[$i], $arrayFields);
            if(ArrayUtils::equalValues($val1,$val2))
                return $groups[$i];
        }
        return null;
    }
    
    static function getValues($item, $fieldsArray)
    {
        $values = array();
        for($i=0; $i< count($fieldsArray); $i++)
        {
            $field = $fieldsArray[$i];
            $value = ArrayUtils::getValue($item, $field);
           // $values.push(value);
           array_push($values, $value);
        }
        return $values;
    }
    
    static function getValue($item, $field)
    {
        $fieldsArray = explode($field,".");
        if(count($fieldsArray) == 1)
            //return $item[$field];
            return $item->{$field};
        else
        {
            if(count($fieldsArray) > 0)
            {
                //$obj = $item[$fieldsArray[0]];
                $obj = $item->{$fieldsArray[0]};
                for ($i = 1; $i < count($fieldsArray); $i++)
                {
                    $f = $fieldsArray[$i];
                    //$obj = $obj[$f];
                    $obj = $obj->{$f};
                }
            }
            return $obj;
        }
    }
    
    static function equalValues($values1, $values2)
    {
        for($i=0; $i< count($values1); $i++)
        {
            if($values1[$i]!=$values2[$i])
                return false;
        }
        return true;
    }
    
    static function orderByNombreCompleto($records)
    {
        $getName = function ($registro) {
            return $registro->nombreCompleto;
        };
        $names = array_map($getName, $records);
        sort($names);
        $sorted = [];
        foreach ($names as $name) {
            foreach ($records as $record) {
                if ($record->nombreCompleto === $name) {
                    $sorted[] = $record;
                    break;
                }
            }
        }
        return $sorted;
    }
    
    static function filter($records, $field, $value)
    {
        $filtered = array();
        for($i = 0; $i < count($records); $i++)
        {
            $record = $records[$i];
            if($record->{$field} == $value)
                array_push($filtered, $record);
        }
        return $filtered;
    }
    
}

