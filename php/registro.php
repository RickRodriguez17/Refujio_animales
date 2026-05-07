<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

if (is_logged_in()) {
    header("Location: {$BASE_URL}/dashboard.php");
    exit;
}

$err = null;
$old = ['nombre'=>'','email'=>'','rol'=>'adoptante','telefono'=>'','direccion'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf'] ?? null)) {
        $err = 'Token invalido.';
    } else {
        $old = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'email'  => trim($_POST['email'] ?? ''),
            'rol'    => $_POST['rol'] ?? 'adoptante',
            'telefono' => trim($_POST['telefono'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
        ];
        $pass = $_POST['password'] ?? '';

        if (!in_array($old['rol'], ['adoptante','donante','voluntario'], true)) {
            $err = 'Rol invalido para registro publico.';
        } elseif ($old['nombre'] === '' || $old['email'] === '' || strlen($pass) < 6) {
            $err = 'Completa los datos. La contrase&ntilde;a debe tener al menos 6 caracteres.';
        } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $err = 'Email invalido.';
        } else {
            $exists = db()->prepare("SELECT 1 FROM usuarios WHERE email = ?");
            $exists->execute([$old['email']]);
            if ($exists->fetchColumn()) {
                $err = 'Ese email ya esta registrado.';
            } else {
                $stmt = db()->prepare("INSERT INTO usuarios (nombre,email,password,rol,telefono,direccion) VALUES (?,?,?,?,?,?)");
                $stmt->execute([
                    $old['nombre'], $old['email'],
                    password_hash($pass, PASSWORD_BCRYPT),
                    $old['rol'], $old['telefono'] ?: null, $old['direccion'] ?: null,
                ]);
                flash_set('ok', 'Cuenta creada, ya puedes ingresar.');
                header("Location: {$BASE_URL}/login.php");
                exit;
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card p-4">
      <h3 class="mb-3 text-primary">Crear cuenta</h3>
      <?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="col-md-6">
          <label class="form-label">Nombre completo *</label>
          <input class="form-control" name="nombre" required value="<?= e($old['nombre']) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Email *</label>
          <input class="form-control" name="email" type="email" required value="<?= e($old['email']) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Contrase&ntilde;a *</label>
          <input class="form-control" name="password" type="password" required minlength="6">
        </div>
        <div class="col-md-6">
          <label class="form-label">Rol *</label>
          <select class="form-select" name="rol">
            <?php foreach (['adoptante','donante','voluntario'] as $r): ?>
              <option value="<?= e($r) ?>" <?= $old['rol']===$r?'selected':'' ?>><?= e(rol_label($r)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Telefono</label>
          <input class="form-control" name="telefono" value="<?= e($old['telefono']) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Direccion</label>
          <input class="form-control" name="direccion" value="<?= e($old['direccion']) ?>">
        </div>
        <div class="col-12">
          <button class="btn btn-primary">Crear cuenta</button>
          <a class="btn btn-link" href="<?= e($BASE_URL) ?>/login.php">¿Ya tienes cuenta? Ingresa</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
