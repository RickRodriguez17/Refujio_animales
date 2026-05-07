<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_role(['admin']);

$u = current_user();
$err = null;
$edit = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'crear') {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass = $_POST['password'] ?? '';
        $rol = $_POST['rol'] ?? 'adoptante';
        if ($nombre === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6 || !in_array($rol, ['admin','veterinario','adoptante','donante','voluntario'], true)) {
            $err = 'Datos invalidos.';
        } else {
            try {
                db()->prepare("INSERT INTO usuarios (nombre,email,password,rol) VALUES (?,?,?,?)")
                    ->execute([$nombre, $email, password_hash($pass, PASSWORD_BCRYPT), $rol]);
                flash_set('ok', 'Usuario creado.');
                header("Location: {$BASE_URL}/usuarios.php"); exit;
            } catch (PDOException $e) {
                $err = 'Email ya registrado.';
            }
        }
    } elseif ($action === 'guardar') {
        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $rol = $_POST['rol'] ?? '';
        $activo = isset($_POST['activo']) ? 1 : 0;
        $pass = $_POST['password'] ?? '';
        if ($id <= 0 || $nombre === '' || !in_array($rol, ['admin','veterinario','adoptante','donante','voluntario'], true)) {
            $err = 'Datos invalidos.';
        } else {
            if ($pass !== '') {
                if (strlen($pass) < 6) { $err = 'La contrasena debe tener al menos 6 caracteres.'; }
                else {
                    db()->prepare("UPDATE usuarios SET nombre=?, rol=?, activo=?, password=? WHERE id=?")
                        ->execute([$nombre, $rol, $activo, password_hash($pass, PASSWORD_BCRYPT), $id]);
                }
            } else {
                db()->prepare("UPDATE usuarios SET nombre=?, rol=?, activo=? WHERE id=?")
                    ->execute([$nombre, $rol, $activo, $id]);
            }
            if (!$err) {
                flash_set('ok', 'Usuario actualizado.');
                header("Location: {$BASE_URL}/usuarios.php"); exit;
            }
        }
    } elseif ($action === 'eliminar') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id === (int)$u['id']) {
            $err = 'No puedes eliminarte a ti mismo.';
        } else {
            db()->prepare("DELETE FROM usuarios WHERE id=?")->execute([$id]);
            flash_set('ok', 'Usuario eliminado.');
            header("Location: {$BASE_URL}/usuarios.php"); exit;
        }
    }
}

if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM usuarios WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

$rows = db()->query("SELECT id,nombre,email,rol,activo,creado_en FROM usuarios ORDER BY id DESC")->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<h3 class="text-primary mb-3">Usuarios del sistema</h3>
<?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card p-3">
      <h6><?= $edit ? 'Editar' : 'Nuevo' ?> usuario</h6>
      <form method="post" class="row g-2">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="<?= $edit ? 'guardar' : 'crear' ?>">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
        <div class="col-12">
          <label class="form-label small">Nombre *</label>
          <input class="form-control form-control-sm" name="nombre" required value="<?= e($edit['nombre'] ?? '') ?>">
        </div>
        <?php if (!$edit): ?>
        <div class="col-12">
          <label class="form-label small">Email *</label>
          <input class="form-control form-control-sm" type="email" name="email" required>
        </div>
        <?php endif; ?>
        <div class="col-12">
          <label class="form-label small">Contrase&ntilde;a <?= $edit ? '(deja vacio para no cambiar)' : '*' ?></label>
          <input class="form-control form-control-sm" type="password" name="password" <?= $edit?'':'required' ?> minlength="6">
        </div>
        <div class="col-12">
          <label class="form-label small">Rol *</label>
          <select class="form-select form-select-sm" name="rol">
            <?php foreach (['admin','veterinario','adoptante','donante','voluntario'] as $r):
              $sel = ($edit['rol'] ?? '') === $r ? 'selected' : ''; ?>
              <option value="<?= e($r) ?>" <?= $sel ?>><?= e(rol_label($r)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php if ($edit): ?>
        <div class="col-12 form-check ms-2">
          <input class="form-check-input" type="checkbox" name="activo" id="ac" <?= ((int)($edit['activo'] ?? 1))===1 ? 'checked':'' ?>>
          <label class="form-check-label small" for="ac">Activo</label>
        </div>
        <?php endif; ?>
        <div class="col-12">
          <button class="btn btn-primary btn-sm w-100"><?= $edit ? 'Guardar cambios' : 'Crear usuario' ?></button>
          <?php if ($edit): ?>
            <a class="btn btn-link btn-sm" href="<?= e($BASE_URL) ?>/usuarios.php">Cancelar</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <div class="col-md-8">
    <div class="table-responsive">
    <table class="table table-bordered align-middle bg-white">
      <thead class="table-light"><tr><th>#</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Activo</th><th>Acciones</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $row): ?>
        <tr>
          <td><?= (int)$row['id'] ?></td>
          <td><?= e($row['nombre']) ?></td>
          <td class="small"><?= e($row['email']) ?></td>
          <td><span class="badge bg-secondary"><?= e(rol_label($row['rol'])) ?></span></td>
          <td><?= ((int)$row['activo'])===1 ? 'Si' : '<span class="text-danger">No</span>' ?></td>
          <td>
            <a class="btn btn-sm btn-outline-secondary" href="<?= e($BASE_URL) ?>/usuarios.php?edit=<?= (int)$row['id'] ?>">Editar</a>
            <?php if ((int)$row['id'] !== (int)$u['id']): ?>
              <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar usuario?');">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="eliminar">
                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
