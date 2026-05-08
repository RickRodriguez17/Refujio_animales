<?php
// Configuracion del sistema "Huellitas de Amor".
// Editar segun el entorno (XAMPP, MAMP, WAMP, Laragon, etc).

$DB_HOST = '127.0.0.1';
$DB_PORT = 3306;
$DB_NAME = 'refujio_de_animales';
$DB_USER = 'root';
$DB_PASS = '';

// Nombre y lema del sitio.
$APP_NAME = 'Huellitas de Amor';
$APP_LEMA = 'Refugio para animales sin hogar';

// Detecta automaticamente la URL base segun la carpeta donde esta
// el proyecto. Por ejemplo:
//   htdocs/php       ->  $BASE_URL = "/php"
//   htdocs/refugio   ->  $BASE_URL = "/refugio"
//   directo en htdocs ->  $BASE_URL = ""
$__script = $_SERVER['SCRIPT_NAME'] ?? '';
$__base   = str_replace('\\', '/', dirname($__script));
$BASE_URL = ($__base === '/' || $__base === '.' || $__base === '') ? '' : rtrim($__base, '/');

date_default_timezone_set('America/La_Paz');
