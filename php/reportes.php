<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_role(['admin']);

$db = db();

$total_animales = (int)$db->query("SELECT COUNT(*) FROM animales")->fetchColumn();
$disponibles    = (int)$db->query("SELECT COUNT(*) FROM animales WHERE estado='disponible'")->fetchColumn();
$adoptados      = (int)$db->query("SELECT COUNT(*) FROM animales WHERE estado='adoptado'")->fetchColumn();
$en_trat        = (int)$db->query("SELECT COUNT(*) FROM animales WHERE estado='en_tratamiento'")->fetchColumn();
$no_disp        = (int)$db->query("SELECT COUNT(*) FROM animales WHERE estado='no_disponible'")->fetchColumn();

$adop_total = (int)$db->query("SELECT COUNT(*) FROM adopciones")->fetchColumn();
$adop_pend  = (int)$db->query("SELECT COUNT(*) FROM adopciones WHERE estado='pendiente'")->fetchColumn();
$adop_aprob = (int)$db->query("SELECT COUNT(*) FROM adopciones WHERE estado IN ('aprobada','completada')")->fetchColumn();
$adop_rech  = (int)$db->query("SELECT COUNT(*) FROM adopciones WHERE estado='rechazada'")->fetchColumn();

$don_count = (int)$db->query("SELECT COUNT(*) FROM donaciones")->fetchColumn();
$don_total = (float)$db->query("SELECT IFNULL(SUM(monto),0) FROM donaciones")->fetchColumn();
$don_din   = (int)$db->query("SELECT COUNT(*) FROM donaciones WHERE tipo='dinero'")->fetchColumn();
$don_ins   = (int)$db->query("SELECT COUNT(*) FROM donaciones WHERE tipo='insumo'")->fetchColumn();

$voluntarios = (int)$db->query("SELECT COUNT(DISTINCT voluntario_id) FROM inscripciones")->fetchColumn();
$horas       = (int)$db->query("SELECT IFNULL(SUM(horas_realizadas),0) FROM inscripciones")->fetchColumn();
$actividades = (int)$db->query("SELECT COUNT(*) FROM actividades")->fetchColumn();

// Detalle por especie
$por_especie = $db->query("SELECT especie, COUNT(*) c FROM animales GROUP BY especie ORDER BY c DESC")->fetchAll();

// Animales en tratamiento (lista corta)
$en_trat_list = $db->query("SELECT id, nombre, especie, raza FROM animales WHERE estado='en_tratamiento' ORDER BY nombre")->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h3 class="text-primary mb-0">📊 Reportes</h3>
    <small class="text-muted">Resumen general del refugio en tiempo real.</small>
  </div>
  <div class="no-print">
    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">🖨️ Imprimir</button>
  </div>
</div>

<div class="print-only mb-3">
  <h2 class="mb-0"><?= e($APP_NAME) ?> — Reporte general</h2>
  <small><?= e($APP_LEMA) ?> · Generado el <?= e(date('d/m/Y H:i')) ?></small>
  <hr>
</div>

<div class="row g-3">
  <div class="col-md-4 col-lg-3"><div class="card p-3 border-top border-3 border-primary"><small class="text-muted">Animales en el refugio</small><h2 class="mb-0"><?= $total_animales ?></h2><small class="text-muted"><?= $disponibles ?> disponibles</small></div></div>
  <div class="col-md-4 col-lg-3"><div class="card p-3 border-top border-3 border-success"><small class="text-muted">Adoptados</small><h2 class="mb-0"><?= $adoptados ?></h2><small class="text-muted">total historico</small></div></div>
  <div class="col-md-4 col-lg-3"><div class="card p-3 border-top border-3 border-warning"><small class="text-muted">Adopciones</small><h2 class="mb-0"><?= $adop_total ?></h2><small class="text-muted"><?= $adop_pend ?> pendientes · <?= $adop_aprob ?> aprobadas</small></div></div>
  <div class="col-md-4 col-lg-3"><div class="card p-3 border-top border-3 border-info"><small class="text-muted">Donaciones</small><h2 class="mb-0"><?= $don_count ?></h2><small class="text-muted">Bs <?= number_format($don_total, 2) ?> totales</small></div></div>
  <div class="col-md-4 col-lg-3"><div class="card p-3 border-top border-3 border-dark"><small class="text-muted">Voluntarios activos</small><h2 class="mb-0"><?= $voluntarios ?></h2><small class="text-muted"><?= $actividades ?> actividades · <?= $horas ?> hrs</small></div></div>
  <div class="col-md-4 col-lg-3"><div class="card p-3 border-top border-3" style="border-color:#8e44ad !important;"><small class="text-muted">En tratamiento</small><h2 class="mb-0"><?= $en_trat ?></h2><small class="text-muted">requieren atencion</small></div></div>
</div>

<div class="row g-3 mt-1">
  <div class="col-md-6">
    <div class="card p-3">
      <h6 class="text-primary">Animales por estado</h6>
      <table class="table table-sm align-middle mb-0">
        <thead><tr><th>Estado</th><th class="text-end">Cantidad</th></tr></thead>
        <tbody>
          <tr><td>Disponible</td>      <td class="text-end"><strong><?= $disponibles ?></strong></td></tr>
          <tr><td>En tratamiento</td>  <td class="text-end"><strong><?= $en_trat ?></strong></td></tr>
          <tr><td>Adoptado</td>        <td class="text-end"><strong><?= $adoptados ?></strong></td></tr>
          <tr><td>No disponible</td>   <td class="text-end"><strong><?= $no_disp ?></strong></td></tr>
          <tr class="table-light"><td><strong>Total</strong></td><td class="text-end"><strong><?= $total_animales ?></strong></td></tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card p-3">
      <h6 class="text-primary">Animales por especie</h6>
      <table class="table table-sm align-middle mb-0">
        <thead><tr><th>Especie</th><th class="text-end">Cantidad</th></tr></thead>
        <tbody>
          <?php foreach ($por_especie as $e): ?>
            <tr><td><?= e(ucfirst($e['especie'])) ?></td><td class="text-end"><strong><?= (int)$e['c'] ?></strong></td></tr>
          <?php endforeach; ?>
          <?php if (empty($por_especie)): ?>
            <tr><td colspan="2" class="text-center text-muted">Sin datos</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card p-3">
      <h6 class="text-primary">Donaciones</h6>
      <table class="table table-sm align-middle mb-0">
        <tbody>
          <tr><td>Total de donaciones</td>   <td class="text-end"><strong><?= $don_count ?></strong></td></tr>
          <tr><td>Donaciones en dinero</td>  <td class="text-end"><strong><?= $don_din ?></strong></td></tr>
          <tr><td>Donaciones de insumos</td> <td class="text-end"><strong><?= $don_ins ?></strong></td></tr>
          <tr class="table-light"><td><strong>Monto total</strong></td><td class="text-end"><strong>Bs <?= number_format($don_total, 2) ?></strong></td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card p-3">
      <h6 class="text-primary">Adopciones</h6>
      <table class="table table-sm align-middle mb-0">
        <tbody>
          <tr><td>Pendientes</td>  <td class="text-end"><strong><?= $adop_pend ?></strong></td></tr>
          <tr><td>Aprobadas / completadas</td><td class="text-end"><strong><?= $adop_aprob ?></strong></td></tr>
          <tr><td>Rechazadas</td>  <td class="text-end"><strong><?= $adop_rech ?></strong></td></tr>
          <tr class="table-light"><td><strong>Total</strong></td><td class="text-end"><strong><?= $adop_total ?></strong></td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="col-12">
    <div class="card p-3">
      <h6 class="text-primary">Animales en tratamiento</h6>
      <?php if (!empty($en_trat_list)): ?>
        <table class="table table-sm align-middle mb-0">
          <thead><tr><th>#</th><th>Nombre</th><th>Especie</th><th>Raza</th></tr></thead>
          <tbody>
            <?php foreach ($en_trat_list as $a): ?>
              <tr>
                <td><?= (int)$a['id'] ?></td>
                <td><strong><?= e($a['nombre']) ?></strong></td>
                <td><?= e(ucfirst($a['especie'])) ?></td>
                <td><?= e($a['raza'] ?: 's/r') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="small text-muted mb-0">No hay animales en tratamiento en este momento.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="text-muted small mt-3 print-only">
  <hr>
  <?= e($APP_NAME) ?> · Reporte generado el <?= e(date('d/m/Y H:i')) ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
