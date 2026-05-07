<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_role(['admin']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$row = ['id'=>0,'nombre'=>'','especie'=>'perro','raza'=>'','edad'=>0,'sexo'=>'macho','descripcion'=>'','estado'=>'disponible'];

if ($id) {
    $stmt = db()->prepare("SELECT * FROM animales WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) { http_response_code(404); die('Animal no encontrado.'); }
}

$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_csrf();
    $row = array_merge($row, [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'especie' => trim($_POST['especie'] ?? ''),
        'raza' => trim($_POST['raza'] ?? ''),
        'edad' => (int)($_POST['edad'] ?? 0),
        'sexo' => $_POST['sexo'] ?? 'macho',
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'estado' => $_POST['estado'] ?? 'disponible',
    ]);
    if ($row['nombre'] === '' || $row['especie'] === '') {
        $err = 'Nombre y especie son obligatorios.';
    } else {
        if ($id) {
            $stmt = db()->prepare("UPDATE animales SET nombre=?,especie=?,raza=?,edad=?,sexo=?,descripcion=?,estado=? WHERE id=?");
            $stmt->execute([$row['nombre'],$row['especie'],$row['raza'],$row['edad'],$row['sexo'],$row['descripcion'],$row['estado'],$id]);
        } else {
            $stmt = db()->prepare("INSERT INTO animales (nombre,especie,raza,edad,sexo,descripcion,estado,fecha_ingreso) VALUES (?,?,?,?,?,?,?,CURDATE())");
            $stmt->execute([$row['nombre'],$row['especie'],$row['raza'],$row['edad'],$row['sexo'],$row['descripcion'],$row['estado']]);
        }
        flash_set('ok', $id ? 'Animal actualizado.' : 'Animal registrado.');
        header("Location: {$BASE_URL}/animales.php"); exit;
    }
}

include __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card p-4">
      <h3 class="text-primary"><?= $id ? 'Editar' : 'Registrar' ?> animal</h3>
      <?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="col-md-6">
          <label class="form-label">Nombre *</label>
          <input class="form-control" name="nombre" required value="<?= e($row['nombre']) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Especie *</label>
          <input class="form-control" name="especie" required value="<?= e($row['especie']) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Sexo</label>
          <select class="form-select" name="sexo">
            <option value="macho" <?= $row['sexo']==='macho'?'selected':'' ?>>Macho</option>
            <option value="hembra" <?= $row['sexo']==='hembra'?'selected':'' ?>>Hembra</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Raza</label>
          <input class="form-control" name="raza" value="<?= e($row['raza']) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Edad (a&ntilde;os)</label>
          <input class="form-control" type="number" min="0" max="40" name="edad" value="<?= e((string)$row['edad']) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Estado</label>
          <select class="form-select" name="estado">
            <?php foreach (['disponible','en_tratamiento','no_disponible','adoptado'] as $s): ?>
              <option value="<?= e($s) ?>" <?= $row['estado']===$s?'selected':'' ?>><?= e(ucfirst(str_replace('_',' ',$s))) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Descripcion</label>
          <textarea class="form-control" name="descripcion" rows="3"><?= e($row['descripcion']) ?></textarea>
        </div>
        <div class="col-12">
          <button class="btn btn-primary"><?= $id ? 'Guardar cambios' : 'Registrar' ?></button>
          <a class="btn btn-link" href="<?= e($BASE_URL) ?>/animales.php">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
