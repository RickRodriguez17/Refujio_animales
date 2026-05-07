<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';
require_login();

$u = current_user();
$r = $u['rol'];

include __DIR__ . '/includes/header.php';
?>
<div class="card p-4 mb-4 bg-primary text-white" style="background: linear-gradient(135deg,#c0392b,#e57373) !important;">
  <h3 class="mb-1">¡Hola, <?= e($u['nombre']) ?>!</h3>
  <div>Bienvenido a <strong><?= e($APP_NAME) ?></strong>.</div>
</div>

<h5>Accesos rapidos</h5>
<div class="row g-3">
<?php
$cards = [];
if (in_array($r, ['admin','adoptante','donante','voluntario','veterinario'], true)) {
    $cards[] = ['Animales', $BASE_URL.'/animales.php', 'Ver animales del refugio.'];
}
if (in_array($r, ['admin','adoptante'], true)) {
    $cards[] = ['Adopciones', $BASE_URL.'/adopciones.php', $r==='admin' ? 'Aprobar o rechazar solicitudes.' : 'Sigue el estado de tus solicitudes.'];
}
if (in_array($r, ['admin','veterinario'], true)) {
    $cards[] = ['Historial Medico', $BASE_URL.'/historial_medico.php', 'Consultas, vacunas y tratamientos.'];
}
if (in_array($r, ['admin','donante'], true)) {
    $cards[] = ['Donaciones', $BASE_URL.'/donaciones.php', $r==='admin' ? 'Controlar las donaciones recibidas.' : 'Apoya con dinero o insumos.'];
}
if (in_array($r, ['admin','voluntario'], true)) {
    $cards[] = ['Voluntariado', $BASE_URL.'/voluntariado.php', 'Actividades e inscripciones.'];
}
if ($r === 'admin') {
    $cards[] = ['Usuarios', $BASE_URL.'/usuarios.php', 'Administrar usuarios.'];
    $cards[] = ['Reportes', $BASE_URL.'/reportes.php', 'Resumen general del refugio.'];
}
foreach ($cards as $c): ?>
  <div class="col-md-4">
    <a class="card p-3 h-100 text-decoration-none text-dark" href="<?= e($c[1]) ?>">
      <h5 class="text-primary mb-1"><?= e($c[0]) ?></h5>
      <small class="text-muted"><?= e($c[2]) ?></small>
    </a>
  </div>
<?php endforeach; ?>
</div>

<div class="card p-3 mt-4">
  <h6>Sobre el refugio</h6>
  <p class="small mb-0"><strong><?= e($APP_NAME) ?></strong> es un refugio que rescata, atiende y busca hogar para animales abandonados. Tu participacion ayuda a salvarlos.</p>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
