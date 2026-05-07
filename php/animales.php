<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_login();

$r = role();

// Eliminar (solo admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete' && $r === 'admin') {
    require_post_csrf();
    $id = (int)($_POST['id'] ?? 0);
    db()->prepare("DELETE FROM animales WHERE id = ?")->execute([$id]);
    flash_set('ok', 'Animal eliminado.');
    header("Location: {$BASE_URL}/animales.php"); exit;
}

$animales = db()->query("SELECT * FROM animales ORDER BY id DESC")->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h3 class="text-primary mb-0">Animales del refugio</h3>
    <small class="text-muted">Conoce a los animales que esperan un hogar.</small>
  </div>
  <?php if ($r === 'admin'): ?>
    <a class="btn btn-primary" href="<?= e($BASE_URL) ?>/animal_form.php">+ Registrar animal</a>
  <?php endif; ?>
</div>

<div class="row g-3">
<?php foreach ($animales as $a): ?>
  <div class="col-md-4">
    <div class="card p-3 h-100">
      <div class="d-flex justify-content-between">
        <h5 class="mb-1"><?= e($a['nombre']) ?></h5>
        <?= estado_animal_badge($a['estado']) ?>
      </div>
      <small class="text-muted"><?= e(ucfirst($a['especie'])) ?> · <?= e($a['raza'] ?: 's/r') ?> · <?= e($a['edad']) ?> a&ntilde;os · <?= e(ucfirst((string)$a['sexo'])) ?></small>
      <p class="small mt-2 mb-2"><?= e($a['descripcion']) ?></p>
      <div class="mt-auto d-flex gap-2">
        <a class="btn btn-sm btn-outline-primary flex-fill" href="<?= e($BASE_URL) ?>/animal_detalle.php?id=<?= (int)$a['id'] ?>">Ver detalle</a>
        <?php if ($r === 'admin'): ?>
          <a class="btn btn-sm btn-outline-secondary" href="<?= e($BASE_URL) ?>/animal_form.php?id=<?= (int)$a['id'] ?>">Editar</a>
          <form method="post" onsubmit="return confirm('¿Eliminar a <?= e($a['nombre']) ?>?');">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endforeach; ?>
<?php if (empty($animales)): ?>
  <div class="col-12"><p class="text-muted">No hay animales registrados todavia.</p></div>
<?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
