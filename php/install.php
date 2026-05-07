<?php
// Instalador del sistema "Huellitas de Amor".
// Ejecutar UNA SOLA VEZ desde el navegador: http://localhost/php/install.php
// Crea la base DB_REFUJIO, todas las tablas y carga datos demo.

require_once __DIR__ . '/includes/config.php';

header('Content-Type: text/html; charset=utf-8');
echo "<!doctype html><meta charset='utf-8'><title>Instalar Huellitas de Amor</title>";
echo "<style>body{font-family:system-ui,sans-serif;max-width:780px;margin:2rem auto;padding:1rem;line-height:1.5;background:#fff8f4}h1{color:#c0392b}pre{background:#fff;border:1px solid #eee;padding:.5rem;border-radius:4px}.ok{color:#188038}.err{color:#b00020}a.btn{display:inline-block;padding:.5rem 1rem;background:#c0392b;color:#fff;border-radius:4px;text-decoration:none;margin-top:1rem}</style>";
echo "<h1>🐾 Instalar Huellitas de Amor</h1>";

function paso(string $msg, bool $ok = true): void {
    $cls = $ok ? 'ok' : 'err';
    echo "<p class='{$cls}'>" . ($ok ? '✓ ' : '✗ ') . htmlspecialchars($msg) . "</p>";
    @ob_flush(); flush();
}

try {
    $dsn = "mysql:host={$DB_HOST};port={$DB_PORT};charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    paso("Conectado a MySQL en {$DB_HOST}:{$DB_PORT}");
} catch (PDOException $e) {
    paso("No se pudo conectar a MySQL: " . $e->getMessage(), false);
    echo "<p>Edita <code>includes/config.php</code> con tus credenciales de MySQL y vuelve a abrir esta pagina.</p>";
    exit;
}

$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$DB_NAME}` CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci");
paso("Base de datos `{$DB_NAME}` lista");
$pdo->exec("USE `{$DB_NAME}`");

// Cargar el esquema desde db.sql.
$sql = file_get_contents(__DIR__ . '/db.sql');
// Quitar el USE/CREATE DATABASE inicial porque ya lo hicimos.
$sql = preg_replace('/CREATE\s+DATABASE.*?;/is', '', $sql);
$sql = preg_replace('/USE\s+\S+;/i', '', $sql);

foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
    if ($stmt === '') continue;
    $pdo->exec($stmt);
}
paso("Tablas creadas / verificadas");

// Limpiar datos previos para hacer el seed idempotente.
foreach (['inscripciones','actividades','donaciones','historial_medico','adopciones','animales','usuarios'] as $t) {
    $pdo->exec("DELETE FROM `{$t}`");
    $pdo->exec("ALTER TABLE `{$t}` AUTO_INCREMENT = 1");
}
paso("Tablas limpiadas para sembrar datos demo");

// --- Usuarios demo ---
$usuarios = [
    ['Edwin Aguilar',     'admin@refugio.bo',      'admin123',    'admin'],
    ['Dra. Ana Bustillos','vet@refugio.bo',        'vet123',      'veterinario'],
    ['Ricardo Rodriguez', 'adoptante@refugio.bo',  'adopta123',   'adoptante'],
    ['Mauricio Burgoa',   'donante@refugio.bo',    'dona123',     'donante'],
    ['Cristofer Molina',  'voluntario@refugio.bo', 'volun123',    'voluntario'],
];
$ins = $pdo->prepare("INSERT INTO usuarios (nombre,email,password,rol) VALUES (?,?,?,?)");
foreach ($usuarios as $u) {
    $ins->execute([$u[0], $u[1], password_hash($u[2], PASSWORD_BCRYPT), $u[3]]);
}
paso("5 usuarios demo creados (uno por rol)");

// --- Animales ---
$animales = [
    ['Manchas',  'perro', 'Mestizo',     2, 'macho',  'Perro juguetón rescatado del centro.', 'disponible'],
    ['Luna',     'gato',  'Siames',      3, 'hembra', 'Gata cariñosa, le gusta dormir al sol.', 'disponible'],
    ['Rocky',    'perro', 'Labrador',    5, 'macho',  'En tratamiento por dermatitis leve.',    'en_tratamiento'],
    ['Firulais', 'perro', 'Pastor Aleman',4,'macho',  'Vacunado y desparasitado, buen guardian.','disponible'],
    ['Michi',    'gato',  'Mestizo',     1, 'macho',  'Gato adoptado recientemente.',           'adoptado'],
];
$ins = $pdo->prepare("INSERT INTO animales (nombre,especie,raza,edad,sexo,descripcion,estado,fecha_ingreso) VALUES (?,?,?,?,?,?,?,CURDATE())");
foreach ($animales as $a) $ins->execute($a);
paso("5 animales demo creados");

// --- Adopciones ---
$pdo->exec("INSERT INTO adopciones (animal_id, adoptante_id, motivo, estado) VALUES
  ((SELECT id FROM animales WHERE nombre='Luna'), (SELECT id FROM usuarios WHERE email='adoptante@refugio.bo'), 'Quiero darle un buen hogar a Luna.', 'pendiente')");
paso("1 adopcion pendiente de demo (Luna)");

// --- Historial medico ---
$pdo->exec("INSERT INTO historial_medico (animal_id, veterinario_id, tipo, diagnostico, observaciones, fecha) VALUES
  ((SELECT id FROM animales WHERE nombre='Rocky'),
   (SELECT id FROM usuarios WHERE email='vet@refugio.bo'),
   'consulta', 'Dermatitis leve', 'Aplicar pomada cada 12 hrs por 7 dias.', CURDATE())");
paso("1 atencion medica de demo");

// --- Donaciones ---
$pdo->exec("INSERT INTO donaciones (donante_id, tipo, monto, descripcion, comprobante) VALUES
  ((SELECT id FROM usuarios WHERE email='donante@refugio.bo'), 'dinero', 200.00, 'Aporte mensual', 'COMP-INICIAL01'),
  ((SELECT id FROM usuarios WHERE email='donante@refugio.bo'), 'alimento', NULL, '2 sacos de croquetas (15 kg c/u)', 'COMP-INICIAL02')");
paso("2 donaciones demo");

// --- Voluntariado ---
$pdo->exec("INSERT INTO actividades (titulo, descripcion, fecha, duracion_horas, cupo_maximo) VALUES
  ('Jornada de limpieza del refugio', 'Ayudaremos a limpiar las jaulas, alimentar a los animales y pasearlos.', DATE_ADD(NOW(), INTERVAL 7 DAY), 4, 15)");
$pdo->exec("INSERT INTO inscripciones (actividad_id, voluntario_id) VALUES
  ((SELECT id FROM actividades LIMIT 1), (SELECT id FROM usuarios WHERE email='voluntario@refugio.bo'))");
paso("1 actividad de voluntariado + 1 voluntario inscrito");

echo "<h2>Instalacion completada</h2>";
echo "<p>Usuarios demo:</p><pre>";
foreach ($usuarios as $u) {
    printf("  %-13s %-25s %s\n", $u[3], $u[1], $u[2]);
}
echo "</pre>";
echo "<a class='btn' href='login.php'>Ir al login</a>";
