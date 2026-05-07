<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_role(['admin']);

$db = db();
$total_animales = (int)$db->query("SELECT COUNT(*) FROM animales")->fetchColumn();
$disponibles = (int)$db->query("SELECT COUNT(*) FROM animales WHERE estado='disponible'")->fetchColumn();
$adoptados = (int)$db->query("SELECT COUNT(*) FROM animales WHERE estado='adoptado'")->fetchColumn();
$en_trat = (int)$db->query("SELECT COUNT(*) FROM animales WHERE estado='en_tratamiento'")->fetchColumn();

$adop_total = (int)$db->query("SELECT COUNT(*) FROM adopciones")->fetchColumn();
$adop_pend = (int)$db->query("SELECT COUNT(*) FROM adopciones WHERE estado='pendiente'")->fetchColumn();
$adop_aprob = (int)$db->query("SELECT COUNT(*) FROM adopciones WHERE estado IN ('aprobada','completada')")->fetchColumn();

$don_count = (int)$db->query("SELECT COUNT(*) FROM donaciones")->fetchColumn();
$don_total = (float)$db->query("SELECT IFNULL(SUM(monto),0) FROM donaciones")->fetchColumn();

$voluntarios = (int)$db->query("SELECT COUNT(DISTINCT voluntario_id) FROM inscripciones")->fetchColumn();
$horas = (int)$db->query("SELECT IFNULL(SUM(horas_realizadas),0) FROM inscripciones")->fetchColumn();
$actividades = (int)$db->query("SELECT COUNT(*) FROM actividades")->fetchColumn();

include __DIR__ . '/includes/header.php';
?>
<h3 class="text-primary mb-3">Reportes</h3>
<p class="text-muted small">Resumen general del refugio en tiempo real.</p>

<div class="row g-3">
  <div class="col-md-4"><div class="card p-3 border-top border-3 border-primary"><small>Animales en el refugio</small><h2><?= $total_animales ?></h2><small class="text-muted"><?= $disponibles ?> disponibles · <?= $en_trat ?> en tratamiento</small></div></div>
  <div class="col-md-4"><div class="card p-3 border-top border-3 border-success"><small>Animales adoptados</small><h2><?= $adoptados ?></h2></div></div>
  <div class="col-md-4"><div class="card p-3 border-top border-3 border-warning"><small>Adopciones (total)</small><h2><?= $adop_total ?></h2><small class="text-muted"><?= $adop_pend ?> pendientes · <?= $adop_aprob ?> aprobadas</small></div></div>
  <div class="col-md-4"><div class="card p-3 border-top border-3 border-info"><small>Donaciones recibidas</small><h2><?= $don_count ?></h2><small class="text-muted">Total Bs <?= number_format($don_total, 2) ?></small></div></div>
  <div class="col-md-4"><div class="card p-3 border-top border-3 border-dark"><small>Voluntarios</small><h2><?= $voluntarios ?></h2><small class="text-muted"><?= $actividades ?> actividades · <?= $horas ?> hrs</small></div></div>
</div>

<div class="card p-3 mt-4">
  <h6>Indicadores</h6>
  <table class="table table-sm mb-0">
    <tr><td>Total de animales</td><td class="text-end"><strong><?= $total_animales ?></strong></td><td>Animales disponibles</td><td class="text-end"><strong><?= $disponibles ?></strong></td></tr>
    <tr><td>Animales en tratamiento</td><td class="text-end"><strong><?= $en_trat ?></strong></td><td>Animales adoptados</td><td class="text-end"><strong><?= $adoptados ?></strong></td></tr>
    <tr><td>Adopciones totales</td><td class="text-end"><strong><?= $adop_total ?></strong></td><td>Adopciones pendientes</td><td class="text-end"><strong><?= $adop_pend ?></strong></td></tr>
    <tr><td>Donaciones</td><td class="text-end"><strong><?= $don_count ?></strong></td><td>Monto total donado</td><td class="text-end"><strong>Bs <?= number_format($don_total, 2) ?></strong></td></tr>
    <tr><td>Voluntarios registrados</td><td class="text-end"><strong><?= $voluntarios ?></strong></td><td>Horas de voluntariado</td><td class="text-end"><strong><?= $horas ?> hrs</strong></td></tr>
  </table>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
