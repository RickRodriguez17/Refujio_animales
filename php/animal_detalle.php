<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM animales WHERE id = ?");
$stmt->execute([$id]);
$a = $stmt->fetch();
if (!$a) { http_response_code(404); die('Animal no encontrado.'); }

$u = current_user();
$r = $u['rol'];

// Solicitar adopcion (adoptante)
$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'solicitar') {
    require_post_csrf();
    if ($r !== 'adoptante') {
        $err = 'Solo los adoptantes pueden solicitar adopcion.';
    } elseif ($a['estado'] !== 'disponible') {
        $err = 'El animal no esta disponible para adopcion.';
    } else {
        // Bloquear duplicado activo
        $check = db()->prepare("SELECT 1 FROM adopciones WHERE animal_id=? AND adoptante_id=? AND estado IN ('pendiente','aprobada')");
        $check->execute([$a['id'], $u['id']]);
        if ($check->fetchColumn()) {
            $err = 'Ya tienes una solicitud activa para este animal.';
        } else {
            $ins = db()->prepare("INSERT INTO adopciones (animal_id, adoptante_id, motivo, documentos) VALUES (?,?,?,?)");
            $ins->execute([
                $a['id'], $u['id'],
                trim($_POST['motivo'] ?? ''),
                trim($_POST['documentos'] ?? ''),
            ]);
            flash_set('ok', 'Solicitud enviada. Te avisaremos cuando sea revisada.');
            header("Location: {$BASE_URL}/adopciones.php");
            exit;
        }
    }
}

// Historial medico publico para el animal (resumen)
$hm = db()->prepare("SELECT hm.*, u.nombre AS vet_nombre FROM historial_medico hm LEFT JOIN usuarios u ON u.id = hm.veterinario_id WHERE hm.animal_id = ? ORDER BY hm.fecha DESC LIMIT 5");
$hm->execute([$a['id']]);
$historial = $hm->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<div class="row g-3">
  <div class="col-md-7">
    <div class="card p-4">
      <div class="d-flex justify-content-between align-items-start">
        <h3 class="mb-1"><?= e($a['nombre']) ?></h3>
        <?= estado_animal_badge($a['estado']) ?>
      </div>
      <p class="text-muted small mb-2"><?= e(ucfirst($a['especie'])) ?> · <?= e($a['raza'] ?: 's/r') ?> · <?= e((string)$a['edad']) ?> a&ntilde;os · <?= e(ucfirst((string)$a['sexo'])) ?></p>
      <p><?= nl2br(e($a['descripcion'])) ?></p>
      <p class="text-muted small">Ingreso al refugio: <?= e($a['fecha_ingreso']) ?></p>
    </div>

    <?php if (!empty($historial)): ?>
    <div class="card p-3 mt-3">
      <h6>Historial medico reciente</h6>
      <ul class="list-unstyled mb-0">
        <?php foreach ($historial as $h): ?>
          <li class="border-bottom py-2 small">
            <strong><?= e($h['fecha']) ?> · <?= e(ucfirst($h['tipo'])) ?></strong>
            — <?= e($h['diagnostico'] ?: ($h['vacuna'] ?: $h['tratamiento'])) ?>
            <span class="text-muted">(<?= e($h['vet_nombre'] ?? 'sin vet') ?>)</span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>
  </div>

  <div class="col-md-5">
    <div class="card p-4">
      <h5 class="text-primary">Solicitar adopcion</h5>
      <?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
      <?php if ($r !== 'adoptante'): ?>
        <p class="small text-muted">Solo los usuarios con rol <strong>Adoptante</strong> pueden enviar solicitudes. <a href="<?= e($BASE_URL) ?>/registro.php">Registrate</a> si todavia no tienes cuenta.</p>
      <?php elseif ($a['estado'] !== 'disponible'): ?>
        <p class="small text-muted">Este animal no esta disponible en este momento.</p>
      <?php else: ?>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="solicitar">
          <div class="mb-2">
            <label class="form-label">¿Por que quieres adoptar a <?= e($a['nombre']) ?>?</label>
            <textarea class="form-control" name="motivo" rows="3" required></textarea>
          </div>
          <div class="mb-2">
            <label class="form-label">Documentos (separados por coma)</label>
            <input class="form-control" name="documentos" placeholder="cedula.pdf, comprobante.pdf">
          </div>
          <button class="btn btn-primary w-100">Enviar solicitud</button>
        </form>
      <?php endif; ?>
    </div>
    <a class="btn btn-link mt-2" href="<?= e($BASE_URL) ?>/animales.php">← Volver al listado</a>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
