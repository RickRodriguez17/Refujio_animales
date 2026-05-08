<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

if (is_logged_in()) {
    header("Location: {$BASE_URL}/dashboard.php");
    exit;
}

// Mostramos hasta 6 animales disponibles como vitrina publica.
try {
    $vitrina = db()->query("SELECT id, nombre, especie, raza, edad, sexo, foto FROM animales WHERE estado='disponible' ORDER BY id DESC LIMIT 6")->fetchAll();
} catch (Throwable $e) {
    $vitrina = [];
}

include __DIR__ . '/includes/header.php';
?>
<section class="hero text-center text-white rounded-4 p-5 mb-4">
  <div class="hero-emoji" aria-hidden="true">🐶 🐱 🐰</div>
  <h1 class="display-5 fw-bold mb-2">Cada huellita merece un hogar</h1>
  <p class="lead mb-4"><?= e($APP_LEMA) ?></p>
  <div class="d-flex flex-wrap justify-content-center gap-2">
    <a class="btn btn-light btn-lg fw-semibold" href="<?= e($BASE_URL) ?>/login.php">Ingresar</a>
    <a class="btn btn-outline-light btn-lg" href="<?= e($BASE_URL) ?>/registro.php">Crear cuenta</a>
  </div>
</section>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card p-4 h-100 text-center info-card">
      <div class="display-6 mb-1">🏠</div>
      <h5 class="text-primary">Adopta</h5>
      <p class="small mb-0">Encuentra a tu nuevo amigo entre los animales rescatados.</p>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card p-4 h-100 text-center info-card">
      <div class="display-6 mb-1">💝</div>
      <h5 class="text-primary">Dona</h5>
      <p class="small mb-0">Apoya con dinero o insumos. Recibes un comprobante.</p>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card p-4 h-100 text-center info-card">
      <div class="display-6 mb-1">🤝</div>
      <h5 class="text-primary">Voluntariate</h5>
      <p class="small mb-0">Inscribete en actividades y registra tus horas.</p>
    </div>
  </div>
</div>

<?php if (!empty($vitrina)): ?>
<h4 class="text-primary mb-3">Buscando hogar</h4>
<div class="row g-3 mb-4">
  <?php foreach ($vitrina as $a): ?>
    <div class="col-6 col-md-4 col-lg-2">
      <div class="card h-100 animal-mini">
        <div class="animal-photo animal-photo-sm">
          <?php if (!empty($a['foto'])): ?>
            <img src="<?= e($BASE_URL) ?>/uploads/animales/<?= e($a['foto']) ?>" alt="Foto de <?= e($a['nombre']) ?>">
          <?php else: ?>
            <div class="animal-photo-placeholder"><?= especie_emoji($a['especie']) ?></div>
          <?php endif; ?>
        </div>
        <div class="p-2 text-center">
          <strong class="d-block"><?= e($a['nombre']) ?></strong>
          <small class="text-muted"><?= e(ucfirst($a['especie'])) ?></small>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<p class="text-center small text-muted">Ingresa o crea cuenta para ver detalles y solicitar adopci&oacute;n.</p>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
