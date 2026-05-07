<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_role(['admin','veterinario']);

$u = current_user();
$err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'crear') {
    require_post_csrf();
    $animal_id = (int)($_POST['animal_id'] ?? 0);
    $tipo = $_POST['tipo'] ?? '';
    $diag = trim($_POST['diagnostico'] ?? '');
    $vac = trim($_POST['vacuna'] ?? '');
    $trat = trim($_POST['tratamiento'] ?? '');
    $obs = trim($_POST['observaciones'] ?? '');
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    if (!in_array($tipo, ['consulta','vacuna','tratamiento','cirugia'], true) || !$animal_id) {
        $err = 'Datos invalidos.';
    } else {
        // Solo el veterinario o admin pueden registrar; veterinario_id = vet logueado o, si admin, el primer vet.
        $vetId = $u['id'];
        if ($u['rol'] === 'admin') {
            $row = db()->query("SELECT id FROM usuarios WHERE rol='veterinario' AND activo=1 LIMIT 1")->fetch();
            $vetId = $row ? (int)$row['id'] : (int)$u['id'];
        }
        $stmt = db()->prepare("INSERT INTO historial_medico (animal_id, veterinario_id, tipo, diagnostico, vacuna, tratamiento, observaciones, fecha) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$animal_id, $vetId, $tipo, $diag ?: null, $vac ?: null, $trat ?: null, $obs ?: null, $fecha]);
        // Actualizar estado del animal segun el tipo.
        if ($tipo === 'tratamiento' || $tipo === 'cirugia') {
            db()->prepare("UPDATE animales SET estado='en_tratamiento' WHERE id=? AND estado='disponible'")->execute([$animal_id]);
        }
        flash_set('ok', 'Atencion medica registrada.');
        header("Location: {$BASE_URL}/historial_medico.php"); exit;
    }
}

$animales = db()->query("SELECT id, nombre FROM animales ORDER BY nombre")->fetchAll();
$rows = db()->query("SELECT hm.*, an.nombre AS animal_nombre, u.nombre AS vet_nombre
    FROM historial_medico hm
    JOIN animales an ON an.id = hm.animal_id
    LEFT JOIN usuarios u ON u.id = hm.veterinario_id
    ORDER BY hm.fecha DESC, hm.id DESC")->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<h3 class="text-primary mb-3">Historial medico</h3>

<?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>

<div class="card p-3 mb-3">
  <h6>Registrar atencion medica</h6>
  <form method="post" class="row g-2">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="action" value="crear">
    <div class="col-md-3">
      <label class="form-label small">Animal *</label>
      <select class="form-select" name="animal_id" required>
        <option value="">Selecciona...</option>
        <?php foreach ($animales as $a): ?>
          <option value="<?= (int)$a['id'] ?>"><?= e($a['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label small">Tipo *</label>
      <select class="form-select" name="tipo" required>
        <option value="consulta">Consulta</option>
        <option value="vacuna">Vacuna</option>
        <option value="tratamiento">Tratamiento</option>
        <option value="cirugia">Cirugia</option>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label small">Fecha</label>
      <input class="form-control" type="date" name="fecha" value="<?= e(date('Y-m-d')) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label small">Diagnostico</label>
      <input class="form-control" name="diagnostico">
    </div>
    <div class="col-md-2">
      <label class="form-label small">Vacuna / Tratamiento</label>
      <input class="form-control" name="vacuna" placeholder="Nombre vacuna">
    </div>
    <div class="col-12">
      <label class="form-label small">Observaciones</label>
      <textarea class="form-control" name="observaciones" rows="2"></textarea>
    </div>
    <div class="col-12">
      <button class="btn btn-primary">Registrar</button>
    </div>
  </form>
</div>

<div class="table-responsive">
<table class="table table-bordered align-middle bg-white">
  <thead class="table-light">
    <tr><th>Fecha</th><th>Animal</th><th>Tipo</th><th>Diagnostico / Vacuna</th><th>Veterinario</th><th>Observaciones</th></tr>
  </thead>
  <tbody>
  <?php foreach ($rows as $h): ?>
    <tr>
      <td class="small"><?= e($h['fecha']) ?></td>
      <td><strong><?= e($h['animal_nombre']) ?></strong></td>
      <td><span class="badge bg-info text-dark"><?= e(ucfirst($h['tipo'])) ?></span></td>
      <td class="small"><?= e($h['diagnostico'] ?: ($h['vacuna'] ?: $h['tratamiento'])) ?></td>
      <td class="small"><?= e($h['vet_nombre'] ?? '-') ?></td>
      <td class="small"><?= e($h['observaciones']) ?></td>
    </tr>
  <?php endforeach; ?>
  <?php if (empty($rows)): ?>
    <tr><td colspan="6" class="text-center text-muted">No hay registros.</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
