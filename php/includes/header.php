<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

$u = current_user();
$r = $u['rol'] ?? null;

function nav_for_role(?string $r, string $base): array {
    $links = [['Inicio', $base . '/dashboard.php']];
    if ($r === null) return [];
    $links[] = ['Animales', $base . '/animales.php'];
    if (in_array($r, ['admin','adoptante'], true)) {
        $links[] = ['Adopciones', $base . '/adopciones.php'];
    }
    if (in_array($r, ['admin','veterinario'], true)) {
        $links[] = ['Historial Medico', $base . '/historial_medico.php'];
    }
    if (in_array($r, ['admin','donante'], true)) {
        $links[] = ['Donaciones', $base . '/donaciones.php'];
    }
    if (in_array($r, ['admin','voluntario'], true)) {
        $links[] = ['Voluntariado', $base . '/voluntariado.php'];
    }
    if ($r === 'admin') {
        $links[] = ['Usuarios', $base . '/usuarios.php'];
        $links[] = ['Reportes', $base . '/reportes.php'];
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
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= e($BASE_URL) ?>/">
      🐾 <?= e($APP_NAME) ?>
      <small class="text-muted fs-6"><?= e($APP_LEMA) ?></small>
    </a>
    <?php if ($u): ?>
      <div class="d-flex align-items-center">
        <ul class="navbar-nav flex-row gap-3 me-3">
          <?php foreach (nav_for_role($r, $BASE_URL) as $lk): ?>
            <li class="nav-item">
              <a class="nav-link <?= basename($lk[1]) === $current ? 'active fw-bold' : '' ?>" href="<?= e($lk[1]) ?>">
                <?= e($lk[0]) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
        <span class="text-muted small me-2">
          <?= e($u['nombre']) ?>
          <span class="badge bg-light text-dark"><?= e(rol_label($r)) ?></span>
        </span>
        <a class="btn btn-sm btn-outline-danger" href="<?= e($BASE_URL) ?>/logout.php">Cerrar sesion</a>
      </div>
    <?php else: ?>
      <div>
        <a class="btn btn-sm btn-outline-primary me-2" href="<?= e($BASE_URL) ?>/login.php">Ingresar</a>
        <a class="btn btn-sm btn-primary" href="<?= e($BASE_URL) ?>/registro.php">Crear cuenta</a>
      </div>
    <?php endif; ?>
  </div>
</nav>

<main class="container py-4">
  <?php if ($flash_ok): ?>
    <div class="alert alert-success"><?= e($flash_ok) ?></div>
  <?php endif; ?>
  <?php if ($flash_err): ?>
    <div class="alert alert-danger"><?= e($flash_err) ?></div>
  <?php endif; ?>
