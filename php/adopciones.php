<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_role(['admin','adoptante']);

$u = current_user();
$r = $u['rol'];

// Decidir solicitud (admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'decidir' && $r === 'admin') {
    require_post_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $estado = $_POST['estado'] ?? '';
    $obs = trim($_POST['observaciones'] ?? '');
    if (in_array($estado, ['aprobada','rechazada'], true)) {
        $ad = db()->prepare("SELECT a.*, an.estado AS animal_estado FROM adopciones a JOIN animales an ON an.id = a.animal_id WHERE a.id = ?");
        $ad->execute([$id]);
        $row = $ad->fetch();
        if (!$row) {
            flash_set('error', 'Solicitud no encontrada.');
        } elseif ($row['estado'] !== 'pendiente') {
            flash_set('error', 'La solicitud ya fue resuelta.');
        } elseif ($estado === 'aprobada' && $row['animal_estado'] !== 'disponible') {
            flash_set('error', 'No se puede aprobar: el animal ya no esta disponible.');
        } else {
            db()->beginTransaction();
            db()->prepare("UPDATE adopciones SET estado=?, observaciones=?, fecha_resolucion=NOW() WHERE id=?")
                ->execute([$estado, $obs, $id]);
            if ($estado === 'aprobada') {
                // El animal pasa a Adoptado y rechazamos automaticamente otras pendientes del mismo animal.
                db()->prepare("UPDATE animales SET estado='adoptado' WHERE id = ?")->execute([$row['animal_id']]);
                db()->prepare("UPDATE adopciones SET estado='rechazada', observaciones=CONCAT(IFNULL(observaciones,''),' [otra solicitud fue aprobada]'), fecha_resolucion=NOW() WHERE animal_id=? AND id<>? AND estado='pendiente'")
                    ->execute([$row['animal_id'], $id]);
            }
            db()->commit();
            flash_set('ok', 'Solicitud ' . $estado . '.');
        }
    }
    header("Location: {$BASE_URL}/adopciones.php"); exit;
}

if ($r === 'admin') {
    $rows = db()->query("SELECT a.*, an.nombre AS animal_nombre, u.nombre AS adoptante_nombre, u.email AS adoptante_email
        FROM adopciones a
        JOIN animales an ON an.id = a.animal_id
        JOIN usuarios u ON u.id = a.adoptante_id
        ORDER BY a.fecha_solicitud DESC")->fetchAll();
} else {
    $stmt = db()->prepare("SELECT a.*, an.nombre AS animal_nombre, u.nombre AS adoptante_nombre, u.email AS adoptante_email
        FROM adopciones a
        JOIN animales an ON an.id = a.animal_id
        JOIN usuarios u ON u.id = a.adoptante_id
        WHERE a.adoptante_id = ?
        ORDER BY a.fecha_solicitud DESC");
    $stmt->execute([$u['id']]);
    $rows = $stmt->fetchAll();
}

include __DIR__ . '/includes/header.php';
?>
<h3 class="text-primary mb-3"><?= $r==='admin' ? 'Solicitudes de adopcion' : 'Mis solicitudes de adopcion' ?></h3>

<?php if (empty($rows)): ?>
  <p class="text-muted">No hay solicitudes <?= $r==='admin' ? 'registradas' : 'tuyas todavia' ?>. <a href="<?= e($BASE_URL) ?>/animales.php">Ver animales disponibles</a>.</p>
<?php else: ?>
<div class="table-responsive">
<table class="table table-bordered align-middle bg-white">
  <thead class="table-light">
    <tr>
      <th>#</th><th>Animal</th>
      <?php if ($r === 'admin'): ?><th>Adoptante</th><?php endif; ?>
      <th>Motivo</th><th>Estado</th><th>Solicitada</th><th>Observaciones</th>
      <?php if ($r === 'admin'): ?><th>Acciones</th><?php endif; ?>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($rows as $a): ?>
    <tr>
      <td><?= (int)$a['id'] ?></td>
      <td><strong><?= e($a['animal_nombre']) ?></strong></td>
      <?php if ($r === 'admin'): ?>
        <td><?= e($a['adoptante_nombre']) ?><br><small class="text-muted"><?= e($a['adoptante_email']) ?></small></td>
      <?php endif; ?>
      <td class="small"><?= e($a['motivo']) ?><?php if ($a['documentos']): ?><br><small class="text-muted">Docs: <?= e($a['documentos']) ?></small><?php endif; ?></td>
      <td><?= estado_adopcion_badge($a['estado']) ?></td>
      <td class="small"><?= e(date('d/m/Y H:i', strtotime($a['fecha_solicitud']))) ?></td>
      <td class="small"><?= e($a['observaciones'] ?? '') ?></td>
      <?php if ($r === 'admin'): ?>
        <td>
          <?php if ($a['estado'] === 'pendiente'): ?>
            <form method="post" class="d-flex flex-column gap-1" onsubmit="return confirm('¿Confirmar?');">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="action" value="decidir">
              <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
              <input type="text" class="form-control form-control-sm" name="observaciones" placeholder="Observaciones">
              <div class="d-flex gap-1">
                <button class="btn btn-sm btn-success flex-fill" name="estado" value="aprobada">Aprobar</button>
                <button class="btn btn-sm btn-danger flex-fill" name="estado" value="rechazada">Rechazar</button>
              </div>
            </form>
          <?php else: ?>
            <small class="text-muted">resuelta <?= $a['fecha_resolucion'] ? date('d/m/Y', strtotime($a['fecha_resolucion'])) : '' ?></small>
          <?php endif; ?>
        </td>
      <?php endif; ?>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
