<?php
abstract class EstatusCorreo
{
    const CREADO = 1;
    const PROCESANDO = 2;
    const PROCESADO = 3;
    const ENVIADO = 4;
    const NO_ENVIADO = 5;
    const OMITIDO = 6;
}