<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_role(['admin','voluntario']);

$u = current_user();
$r = $u['rol'];
$err = null;

// Crear actividad (admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'crear_actividad' && $r === 'admin') {
    require_post_csrf();
    $titulo = trim($_POST['titulo'] ?? '');
    $desc = trim($_POST['descripcion'] ?? '');
    $fecha = $_POST['fecha'] ?? '';
    $hrs = (int)($_POST['duracion_horas'] ?? 0);
    $cupo = (int)($_POST['cupo_maximo'] ?? 10);
    if ($titulo === '' || $fecha === '') {
        $err = 'Titulo y fecha son obligatorios.';
    } else {
        $stmt = db()->prepare("INSERT INTO actividades (titulo, descripcion, fecha, duracion_horas, cupo_maximo) VALUES (?,?,?,?,?)");
        $stmt->execute([$titulo, $desc ?: null, $fecha, $hrs, $cupo]);
        flash_set('ok', 'Actividad creada.');
        header("Location: {$BASE_URL}/voluntariado.php"); exit;
    }
}

// Inscribirse (voluntario)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'inscribirme' && $r === 'voluntario') {
    require_post_csrf();
    $aid = (int)($_POST['actividad_id'] ?? 0);
    // Cupo
    $cupo = db()->prepare("SELECT a.cupo_maximo, (SELECT COUNT(*) FROM inscripciones i WHERE i.actividad_id=a.id) AS inscritos FROM actividades a WHERE a.id=?");
    $cupo->execute([$aid]);
    $info = $cupo->fetch();
    if (!$info) {
        $err = 'Actividad no encontrada.';
    } elseif ((int)$info['inscritos'] >= (int)$info['cupo_maximo']) {
        $err = 'La actividad ya esta llena.';
    } else {
        try {
            db()->prepare("INSERT INTO inscripciones (actividad_id, voluntario_id) VALUES (?,?)")->execute([$aid, $u['id']]);
            flash_set('ok', 'Te inscribiste a la actividad.');
        } catch (PDOException $e) {
            flash_set('error', 'Ya estabas inscrito en esa actividad.');
        }
        header("Location: {$BASE_URL}/voluntariado.php"); exit;
    }
}

// Registrar horas (voluntario)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'registrar_horas' && $r === 'voluntario') {
    require_post_csrf();
    $aid = (int)($_POST['actividad_id'] ?? 0);
    $hrs = max(0, (int)($_POST['horas'] ?? 0));
    db()->prepare("UPDATE inscripciones SET horas_realizadas=? WHERE actividad_id=? AND voluntario_id=?")
        ->execute([$hrs, $aid, $u['id']]);
    flash_set('ok', 'Horas actualizadas.');
    header("Location: {$BASE_URL}/voluntariado.php"); exit;
}

// Datos
$actividades = db()->query("SELECT a.*, (SELECT COUNT(*) FROM inscripciones i WHERE i.actividad_id=a.id) AS inscritos FROM actividades a ORDER BY fecha ASC")->fetchAll();
$mis_inscripciones = [];
if ($r === 'voluntario') {
    $stmt = db()->prepare("SELECT i.*, a.titulo, a.fecha FROM inscripciones i JOIN actividades a ON a.id=i.actividad_id WHERE i.voluntario_id=? ORDER BY a.fecha DESC");
    $stmt->execute([$u['id']]);
    $mis_inscripciones = $stmt->fetchAll();
}
// Mapeo actividad_id -> mis horas
$mis_map = [];
foreach ($mis_inscripciones as $i) $mis_map[$i['actividad_id']] = $i;

include __DIR__ . '/includes/header.php';
?>
<h3 class="text-primary mb-3">Voluntariado</h3>
<?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>

<?php if ($r === 'admin'): ?>
<div class="card p-3 mb-3">
  <h6>Crear actividad</h6>
  <form method="post" class="row g-2">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="action" value="crear_actividad">
    <div class="col-md-4">
      <label class="form-label small">Titulo *</label>
      <input class="form-control" name="titulo" required>
    </div>
    <div class="col-md-3">
      <label class="form-label small">Fecha *</label>
      <input class="form-control" type="datetime-local" name="fecha" required>
    </div>
    <div class="col-md-2">
      <label class="form-label small">Duracion (hrs)</label>
      <input class="form-control" type="number" min="0" name="duracion_horas" value="4">
    </div>
    <div class="col-md-3">
      <label class="form-label small">Cupo maximo</label>
      <input class="form-control" type="number" min="1" name="cupo_maximo" value="10">
    </div>
    <div class="col-12">
      <label class="form-label small">Descripcion</label>
      <textarea class="form-control" name="descripcion" rows="2"></textarea>
    </div>
    <div class="col-12">
      <button class="btn btn-primary">Crear</button>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="row g-3">
<?php foreach ($actividades as $a):
    $mia = $mis_map[$a['id']] ?? null;
    $lleno = (int)$a['inscritos'] >= (int)$a['cupo_maximo'];
?>
  <div class="col-md-6">
    <div class="card p-3 h-100">
      <div class="d-flex justify-content-between">
        <h5 class="mb-0"><?= e($a['titulo']) ?></h5>
        <small class="text-muted"><?= e(date('d/m/Y H:i', strtotime($a['fecha']))) ?> · <?= (int)$a['duracion_horas'] ?> hrs</small>
      </div>
      <p class="small mt-1 mb-1 text-muted"><?= e($a['descripcion']) ?></p>
      <small>Inscritos: <strong><?= (int)$a['inscritos'] ?> / <?= (int)$a['cupo_maximo'] ?></strong></small>
      <?php if ($r === 'voluntario'): ?>
        <div class="mt-2">
          <?php if ($mia): ?>
            <form method="post" class="d-flex gap-2 align-items-end">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="action" value="registrar_horas">
              <input type="hidden" name="actividad_id" value="<?= (int)$a['id'] ?>">
              <div>
                <label class="form-label small mb-0">Mis horas</label>
                <input type="number" class="form-control form-control-sm" name="horas" min="0" value="<?= (int)$mia['horas_realizadas'] ?>" style="width:6rem">
              </div>
              <button class="btn btn-sm btn-warning">Guardar horas</button>
              <span class="badge bg-success">Inscrito</span>
            </form>
          <?php elseif ($lleno): ?>
            <button class="btn btn-sm btn-secondary" disabled>Cupo lleno</button>
          <?php else: ?>
            <form method="post">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="action" value="inscribirme">
              <input type="hidden" name="actividad_id" value="<?= (int)$a['id'] ?>">
              <button class="btn btn-sm btn-primary">Inscribirme</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
<?php if (empty($actividades)): ?>
  <div class="col-12"><p class="text-muted">No hay actividades programadas.</p></div>
<?php endif; ?>
</div>

<?php if ($r === 'voluntario' && !empty($mis_inscripciones)): ?>
  <h5 class="mt-4">Mis horas de voluntariado</h5>
  <table class="table table-bordered align-middle bg-white">
    <thead class="table-light"><tr><th>Actividad</th><th>Fecha</th><th>Horas</th></tr></thead>
    <tbody>
    <?php foreach ($mis_inscripciones as $i): ?>
      <tr>
        <td><?= e($i['titulo']) ?></td>
        <td class="small"><?= e(date('d/m/Y', strtotime($i['fecha']))) ?></td>
        <td><strong><?= (int)$i['horas_realizadas'] ?></strong> hrs</td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
