<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_login();

$r = role();

// Eliminar (solo admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete' && $r === 'admin') {
    require_post_csrf();
    $id = (int)($_POST['id'] ?? 0);
    // Borrar foto del disco si existe
    $row = db()->prepare("SELECT foto FROM animales WHERE id=?");
    $row->execute([$id]);
    $foto = $row->fetchColumn();
    if ($foto) {
        $f = __DIR__ . '/uploads/animales/' . basename($foto);
        if (is_file($f)) { @unlink($f); }
    }
    db()->prepare("DELETE FROM animales WHERE id = ?")->execute([$id]);
    flash_set('ok', 'Animal eliminado.');
    header("Location: {$BASE_URL}/animales.php"); exit;
}

// Filtros
$q       = trim((string)($_GET['q']       ?? ''));
$estado  = trim((string)($_GET['estado']  ?? ''));
$especie = trim((string)($_GET['especie'] ?? ''));

$sql = "SELECT * FROM animales WHERE 1=1";
$params = [];
if ($q !== '') {
    $sql .= " AND (nombre LIKE ? OR raza LIKE ? OR descripcion LIKE ?)";
    $like = '%' . $q . '%';
    $params[] = $like; $params[] = $like; $params[] = $like;
}
$estados_validos  = ['disponible','en_tratamiento','no_disponible','adoptado'];
$especies_validas = ['perro','gato','conejo','ave','otro'];
if (in_array($estado, $estados_validos, true))   { $sql .= " AND estado=?";  $params[] = $estado; }
if (in_array($especie, $especies_validas, true)) { $sql .= " AND especie=?"; $params[] = $especie; }
$sql .= " ORDER BY id DESC";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$animales = $stmt->fetchAll();

// Totales por estado para badges del filtro
$counts = ['todos' => 0,'disponible'=>0,'en_tratamiento'=>0,'no_disponible'=>0,'adoptado'=>0];
foreach (db()->query("SELECT estado, COUNT(*) c FROM animales GROUP BY estado") as $rw) {
    $counts[$rw['estado']] = (int)$rw['c'];
    $counts['todos'] += (int)$rw['c'];
}

include __DIR__ . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h3 class="text-primary mb-0">🐾 Animales del refugio</h3>
    <small class="text-muted">Conoce a los que esperan un hogar.</small>
  </div>
  <div class="d-flex gap-2 no-print">
    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">🖨️ Imprimir</button>
    <?php if ($r === 'admin'): ?>
      <a class="btn btn-primary btn-sm" href="<?= e($BASE_URL) ?>/animal_form.php">+ Registrar animal</a>
    <?php endif; ?>
  </div>
</div>

<form method="get" class="card p-3 mb-3 no-print">
  <div class="row g-2 align-items-end">
    <div class="col-md-5">
      <label class="form-label small mb-1">Buscar por nombre, raza o descripcion</label>
      <input class="form-control form-control-sm" name="q" value="<?= e($q) ?>" placeholder="Ej: Lola, mestizo, jugueton...">
    </div>
    <div class="col-md-3">
      <label class="form-label small mb-1">Estado</label>
      <select class="form-select form-select-sm" name="estado">
        <option value="">Todos (<?= (int)$counts['todos'] ?>)</option>
        <option value="disponible"      <?= $estado==='disponible'?'selected':'' ?>>Disponible (<?= (int)$counts['disponible'] ?>)</option>
        <option value="en_tratamiento"  <?= $estado==='en_tratamiento'?'selected':'' ?>>En tratamiento (<?= (int)$counts['en_tratamiento'] ?>)</option>
        <option value="adoptado"        <?= $estado==='adoptado'?'selected':'' ?>>Adoptado (<?= (int)$counts['adoptado'] ?>)</option>
        <option value="no_disponible"   <?= $estado==='no_disponible'?'selected':'' ?>>No disponible (<?= (int)$counts['no_disponible'] ?>)</option>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label small mb-1">Especie</label>
      <select class="form-select form-select-sm" name="especie">
        <option value="">Todas</option>
        <?php foreach ($especies_validas as $sp): ?>
          <option value="<?= e($sp) ?>" <?= $especie===$sp?'selected':'' ?>><?= e(ucfirst($sp)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2 d-flex gap-1">
      <button class="btn btn-primary btn-sm flex-fill">Filtrar</button>
      <?php if ($q!=='' || $estado!=='' || $especie!==''): ?>
        <a class="btn btn-outline-secondary btn-sm" href="<?= e($BASE_URL) ?>/animales.php" title="Limpiar">×</a>
      <?php endif; ?>
    </div>
  </div>
</form>

<div class="row g-3">
<?php foreach ($animales as $a): ?>
  <div class="col-sm-6 col-md-4 col-lg-3">
    <div class="card h-100 animal-card">
      <div class="animal-photo">
        <?php if (!empty($a['foto'])): ?>
          <img src="<?= e($BASE_URL) ?>/uploads/animales/<?= e($a['foto']) ?>" alt="Foto de <?= e($a['nombre']) ?>" loading="lazy">
        <?php else: ?>
          <div class="animal-photo-placeholder"><?= especie_emoji($a['especie']) ?></div>
        <?php endif; ?>
        <span class="animal-state-badge"><?= estado_animal_badge($a['estado']) ?></span>
      </div>
      <div class="p-3 d-flex flex-column flex-grow-1">
        <h5 class="mb-1"><?= e($a['nombre']) ?></h5>
        <small class="text-muted mb-2">
          <?= e(ucfirst($a['especie'])) ?> · <?= e($a['raza'] ?: 's/r') ?> ·
          <?= e((string)$a['edad']) ?> a&ntilde;os ·
          <?= e(ucfirst((string)$a['sexo'])) ?>
        </small>
        <p class="small text-truncate-2 mb-3"><?= e($a['descripcion']) ?></p>
        <div class="mt-auto d-flex gap-2 no-print">
          <a class="btn btn-sm btn-outline-primary flex-fill" href="<?= e($BASE_URL) ?>/animal_detalle.php?id=<?= (int)$a['id'] ?>">Ver detalle</a>
          <?php if ($r === 'admin'): ?>
            <a class="btn btn-sm btn-outline-secondary" title="Editar" href="<?= e($BASE_URL) ?>/animal_form.php?id=<?= (int)$a['id'] ?>">✏️</a>
            <form method="post" onsubmit="return confirm('¿Eliminar a <?= e($a['nombre']) ?>?');" class="m-0">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" title="Eliminar">🗑️</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>
<?php if (empty($animales)): ?>
  <div class="col-12">
    <div class="card p-4 text-center text-muted">
      No hay animales que coincidan con los filtros.
    </div>
  </div>
<?php endif; ?>
</div>

<div class="text-muted small mt-3 print-only">
  Reporte generado el <?= e(date('d/m/Y H:i')) ?> · <?= count($animales) ?> animales mostrados.
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
