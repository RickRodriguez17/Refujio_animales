<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

$u = current_user();
$r = $u['rol'] ?? null;

function nav_for_role(?string $r, string $base): array {
    if ($r === null) return [];
    $links = [['🏠', 'Inicio', $base . '/dashboard.php']];
    $links[] = ['🐾', 'Animales', $base . '/animales.php'];
    if (in_array($r, ['admin','adoptante'], true)) {
        $links[] = ['❤️', 'Adopciones', $base . '/adopciones.php'];
    }
    if (in_array($r, ['admin','veterinario'], true)) {
        $links[] = ['💊', 'Historial Medico', $base . '/historial_medico.php'];
    }
    if (in_array($r, ['admin','donante'], true)) {
        $links[] = ['💝', 'Donaciones', $base . '/donaciones.php'];
    }
    if (in_array($r, ['admin','voluntario'], true)) {
        $links[] = ['🤝', 'Voluntariado', $base . '/voluntariado.php'];
    }
    if ($r === 'admin') {
        $links[] = ['👥', 'Usuarios', $base . '/usuarios.php'];
        $links[] = ['📊', 'Reportes', $base . '/reportes.php'];
    }
    return $links;
}

$current = basename($_SERVER['PHP_SELF']);
$flash_ok = flash_pop('ok');
$flash_err = flash_pop('error');
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($APP_NAME) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= e($BASE_URL) ?>/assets/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm sticky-top no-print">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= e($BASE_URL) ?>/">
      <span class="brand-emoji">🐾</span>
      <span>
        <span class="text-primary"><?= e($APP_NAME) ?></span>
        <small class="d-block text-muted fw-normal" style="font-size:.7rem;line-height:1;"><?= e($APP_LEMA) ?></small>
      </span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <?php if ($u): ?>
        <ul class="navbar-nav ms-auto align-items-lg-center">
          <?php foreach (nav_for_role($r, $BASE_URL) as $lk): ?>
            <li class="nav-item">
              <a class="nav-link <?= basename($lk[2]) === $current ? 'active fw-bold' : '' ?>" href="<?= e($lk[2]) ?>">
                <span class="me-1"><?= $lk[0] ?></span><?= e($lk[1]) ?>
              </a>
            </li>
          <?php endforeach; ?>
          <li class="nav-item d-flex align-items-center ms-lg-3 mt-2 mt-lg-0">
            <span class="text-muted small me-2 text-truncate" style="max-width:14rem;">
              <?= e($u['nombre']) ?>
              <span class="badge rounded-pill bg-light text-dark border"><?= e(rol_label($r)) ?></span>
            </span>
            <a class="btn btn-sm btn-outline-danger" href="<?= e($BASE_URL) ?>/logout.php">Salir</a>
          </li>
        </ul>
      <?php else: ?>
        <div class="ms-auto mt-2 mt-lg-0">
          <a class="btn btn-sm btn-outline-primary me-2" href="<?= e($BASE_URL) ?>/login.php">Ingresar</a>
          <a class="btn btn-sm btn-primary" href="<?= e($BASE_URL) ?>/registro.php">Crear cuenta</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>

<main class="container py-4">
  <?php if ($flash_ok): ?>
    <div class="alert alert-success no-print"><?= e($flash_ok) ?></div>
  <?php endif; ?>
  <?php if ($flash_err): ?>
    <div class="alert alert-danger no-print"><?= e($flash_err) ?></div>
  <?php endif; ?>
