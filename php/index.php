<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';

if (is_logged_in()) {
    header("Location: {$BASE_URL}/dashboard.php");
    exit;
}
include __DIR__ . '/includes/header.php';
?>
<div class="text-center py-5">
  <h1 class="display-5 fw-bold text-primary">🐾 <?= e($APP_NAME) ?></h1>
  <p class="lead"><?= e($APP_LEMA) ?></p>
  <p class="text-muted">Sistema basico para gestionar animales, adopciones, atenciones medicas, donaciones y voluntarios del refugio.</p>
  <a class="btn btn-primary btn-lg me-2" href="<?= e($BASE_URL) ?>/login.php">Ingresar</a>
  <a class="btn btn-outline-primary btn-lg" href="<?= e($BASE_URL) ?>/registro.php">Crear cuenta</a>
</div>

<div class="row g-3">
  <div class="col-md-4"><div class="card p-3 h-100"><h5 class="text-primary">Adopta</h5><p class="small">Encuentra a tu nuevo amigo entre los animales disponibles.</p></div></div>
  <div class="col-md-4"><div class="card p-3 h-100"><h5 class="text-primary">Dona</h5><p class="small">Apoya con dinero o insumos. Recibes un comprobante.</p></div></div>
  <div class="col-md-4"><div class="card p-3 h-100"><h5 class="text-primary">Voluntariate</h5><p class="small">Inscribete en actividades y registra tus horas.</p></div></div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
