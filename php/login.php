<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

if (is_logged_in()) {
    header("Location: {$BASE_URL}/dashboard.php");
    exit;
}

$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf'] ?? null)) {
        $err = 'Token invalido, recarga la pagina.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $pass = $_POST['password'] ?? '';
        $stmt = db()->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if ($u && (int)$u['activo'] === 1 && password_verify($pass, $u['password'])) {
            unset($u['password']);
            $_SESSION['user'] = $u;
            session_regenerate_id(true);
            header("Location: {$BASE_URL}/dashboard.php");
            exit;
        }
        $err = $u && (int)$u['activo'] !== 1
            ? 'Tu usuario esta inactivo. Pide al administrador que lo active.'
            : 'Email o contrase&ntilde;a incorrectos.';
    }
}

include __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card p-4">
      <h3 class="mb-3 text-primary">Ingresar</h3>
      <?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input class="form-control" name="email" type="email" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label">Contrase&ntilde;a</label>
          <input class="form-control" name="password" type="password" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">Ingresar</button>
      </form>
      <hr>
      <div class="small text-muted">
        Demo:<br>
        admin@refugio.bo / admin123<br>
        vet@refugio.bo / vet123<br>
        adoptante@refugio.bo / adopta123<br>
        donante@refugio.bo / dona123<br>
        voluntario@refugio.bo / volun123
      </div>
      <div class="text-center mt-3">
        <a href="<?= e($BASE_URL) ?>/registro.php">¿No tienes cuenta? Registrate</a>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
