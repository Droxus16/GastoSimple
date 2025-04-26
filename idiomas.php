<?php
// idiomas.php
// Carga el archivo de traducciones según la preferencia del usuario guardada en sesión.

session_start();

// Idiomas soportados
$idiomas_disponibles = ['es','en','fr','zh'];

// Determinar el idioma actual: primero por sesión, si no, usar 'es'
$idioma = $_SESSION['idioma'] ?? 'es';
if (!in_array($idioma, $idiomas_disponibles)) {
    $idioma = 'es';
    $_SESSION['idioma'] = 'es';
}

// Construir ruta al archivo de traducción
$archivo = __DIR__ . "/idiomas/{$idioma}.php";

// Incluir el archivo, o caer al español si no existe
if (file_exists($archivo)) {
    include $archivo;
} else {
    include __DIR__ . "/idiomas/es.php";
}

// Ahora tendrás disponible el array $traducciones con todas las cadenas:
