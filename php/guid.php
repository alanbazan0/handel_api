<?php
use php\clases\Token;
require_once("clases/Token.php");

$guid = Token::crear();

echo $guid;