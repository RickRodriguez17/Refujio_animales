<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_role(['admin','donante']);

$u = current_user();
$r = $u['rol'];
$err = null;
$ultimo_comprobante = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'donar' && $r === 'donante') {
    require_post_csrf();
    $tipo = $_POST['tipo'] ?? '';
    $monto_raw = trim($_POST['monto'] ?? '');
    $monto = $monto_raw === '' ? null : (float)$monto_raw;
    $desc = trim($_POST['descripcion'] ?? '');
    if (!in_array($tipo, ['dinero','alimento','medicina','otro'], true)) {
        $err = 'Tipo de donacion invalido.';
    } elseif ($tipo === 'dinero' && (!$monto || $monto <= 0)) {
        $err = 'Indica un monto en bolivianos para donaciones de dinero.';
    } else {
        $comprobante = 'COMP-' . strtoupper(bin2hex(random_bytes(4)));
        $stmt = db()->prepare("INSERT INTO donaciones (donante_id, tipo, monto, descripcion, comprobante) VALUES (?,?,?,?,?)");
        $stmt->execute([$u['id'], $tipo, $monto, $desc ?: null, $comprobante]);
        $ultimo_comprobante = $comprobante;
        flash_set('ok', '¡Gracias por tu donacion! Tu comprobante es ' . $comprobante);
        header("Location: {$BASE_URL}/donaciones.php?comp=" . urlencode($comprobante)); exit;
    }
}

$ultimo_comprobante = $_GET['comp'] ?? null;

if ($r === 'admin') {
    $rows = db()->query("SELECT d.*, u.nombre AS donante_nombre FROM donaciones d JOIN usuarios u ON u.id = d.donante_id ORDER BY d.fecha DESC")->fetchAll();
    $total = (float)(db()->query("SELECT IFNULL(SUM(monto),0) FROM donaciones")->fetchColumn());
} else {
    $stmt = db()->prepare("SELECT d.*, u.nombre AS donante_nombre FROM donaciones d JOIN usuarios u ON u.id = d.donante_id WHERE donante_id=? ORDER BY fecha DESC");
    $stmt->execute([$u['id']]);
    $rows = $stmt->fetchAll();
    $total = array_sum(array_map(fn($d) => (float)$d['monto'], $rows));
}

include __DIR__ . '/includes/header.php';
?>
<h3 class="text-primary mb-3">Donaciones</h3>

<?php if ($ultimo_comprobante): ?>
  <div class="alert alert-success">¡Gracias por tu donacion! Tu comprobante es <strong><?= e($ultimo_comprobante) ?></strong>.</div>
<?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>

<?php if ($r === 'donante'): ?>
<div class="card p-3 mb-3">
  <h6>Realizar donacion</h6>
  <form method="post" class="row g-2">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="action" value="donar">
    <div class="col-md-3">
      <label class="form-label small">Tipo *</label>
      <select class="form-select" name="tipo" required>
        <option value="dinero">Dinero (Bs)</option>
        <option value="alimento">Alimento</option>
        <option value="medicina">Medicina</option>
        <option value="otro">Otro</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label small">Monto (Bs)</label>
      <input class="form-control" type="number" min="0" step="0.01" name="monto" placeholder="Solo si es dinero">
    </div>
    <div class="col-md-6">
      <label class="form-label small">Descripcion</label>
      <input class="form-control" name="descripcion" placeholder="Ej: 2 sacos de croquetas">
    </div>
    <div class="col-12">
      <button class="btn btn-primary">Donar</button>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-2">
  <h6 class="mb-0"><?= $r==='admin' ? 'Todas las donaciones' : 'Mis donaciones' ?></h6>
  <span class="text-muted small">Total monetario: <strong>Bs <?= number_format($total, 2) ?></strong></span>
</div>

<div class="table-responsive">
<table class="table table-bordered align-middle bg-white">
  <thead class="table-light">
    <tr>
      <th>Comprobante</th><th>Fecha</th>
      <?php if ($r==='admin'): ?><th>Donante</th><?php endif; ?>
      <th>Tipo</th><th>Monto</th><th>Descripcion</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($rows as $d): ?>
    <tr>
      <td class="small fw-bold"><?= e($d['comprobante']) ?></td>
      <td class="small"><?= e(date('d/m/Y H:i', strtotime($d['fecha']))) ?></td>
      <?php if ($r==='admin'): ?><td class="small"><?= e($d['donante_nombre']) ?></td><?php endif; ?>
      <td><span class="badge bg-secondary"><?= e(ucfirst($d['tipo'])) ?></span></td>
      <td><?= $d['monto'] !== null ? 'Bs ' . number_format($d['monto'], 2) : '<span class="text-muted">—</span>' ?></td>
      <td class="small"><?= e($d['descripcion']) ?></td>
    </tr>
  <?php endforeach; ?>
  <?php if (empty($rows)): ?>
    <tr><td colspan="<?= $r==='admin'?6:5 ?>" class="text-center text-muted">No hay donaciones registradas.</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
