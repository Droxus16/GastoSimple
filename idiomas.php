<?php
session_start();


$idiomas_disponibles = ['es','en','fr','zh'];
$idioma = $_SESSION['idioma'] ?? 'es';
if (!in_array($idioma, $idiomas_disponibles)) {
    $idioma = 'es';
    $_SESSION['idioma'] = 'es';
}
$archivo = __DIR__ . "/idiomas/{$idioma}.php";
if (file_exists($archivo)) {
    include $archivo;
} else {
    include __DIR__ . "/idiomas/es.php";
}

