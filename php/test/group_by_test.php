<?php

use php\clases\ArrayUtils;

include "../clases/ArrayUtils.php";

$records = array((object)["nombre"=>"Alan", "cumplidas" => 2, "enviadas" => 3],
    (object)["nombre"=>"Alan", "cumplidas" => 8, "enviadas" => 7],
    (object)["nombre"=>"Abisai", "cumplidas" => 10, "enviadas" => 10]
);

$groups = ArrayUtils::groupBySUM("nombre", "cumplidas,enviadas", $records);

var_dump($groups);