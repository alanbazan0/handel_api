<?php
// Endpoint post-deploy: descomprime vendor.zip subido por CI/CD y lo borra.
// El token se compara contra un archivo fuera del webroot, subido manualmente
// una sola vez al servidor (nunca versionado en git).

$tokenArchivo = __DIR__ . '/../migrate_token.txt';
$tokenEsperado = file_exists($tokenArchivo) ? trim(file_get_contents($tokenArchivo)) : null;

if ($tokenEsperado === null || $tokenEsperado === '' || ($_POST['token'] ?? '') !== $tokenEsperado) {
    http_response_code(403);
    exit('Token inválido');
}

$zipPath = __DIR__ . '/vendor.zip';
if (!file_exists($zipPath)) {
    http_response_code(404);
    exit('vendor.zip no encontrado');
}

$zip = new ZipArchive();
if ($zip->open($zipPath) === true) {
    $zip->extractTo(__DIR__);
    $zip->close();
    unlink($zipPath);
    echo 'OK';
} else {
    http_response_code(500);
    echo 'No se pudo abrir vendor.zip';
}
