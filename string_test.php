<?php

$validadas = $_REQUEST["validadas"];

if(isset($validadas) && $validadas!="")
{
  echo "aplica filtro ";
  var_dump($validadas);
}