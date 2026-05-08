<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_role(['admin']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$row = ['id'=>0,'nombre'=>'','especie'=>'perro','raza'=>'','edad'=>0,'sexo'=>'macho','descripcion'=>'','estado'=>'disponible','foto'=>null];

if ($id) {
    $stmt = db()->prepare("SELECT * FROM animales WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) { http_response_code(404); die('Animal no encontrado.'); }
    $row = $found;
}

$err = null;

/**
 * Procesa la subida de foto. Devuelve [filename|null, error|null].
 */
function procesar_foto_subida(?array $file): array {
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Error al subir la foto (codigo ' . (int)$file['error'] . ').'];
    }
    if (($file['size'] ?? 0) > 3 * 1024 * 1024) {
        return [null, 'La foto es demasiado grande (max 3 MB).'];
    }
    $info = @getimagesize($file['tmp_name']);
    $permitidos = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
    if (!$info || !isset($permitidos[$info[2]])) {
        return [null, 'Formato no permitido. Usa JPG, PNG, WEBP o GIF.'];
    }
    $ext = $permitidos[$info[2]];
    $dir = __DIR__ . '/uploads/animales';
    if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
    $nombre = 'animal_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destino = $dir . '/' . $nombre;
    if (!move_uploaded_file($file['tmp_name'], $destino)) {
        return [null, 'No se pudo guardar la foto en el servidor.'];
    }
    return [$nombre, null];
}

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

    $quitar_foto = !empty($_POST['quitar_foto']);
    [$nuevaFoto, $errFoto] = procesar_foto_subida($_FILES['foto'] ?? null);

    if ($row['nombre'] === '' || $row['especie'] === '') {
        $err = 'Nombre y especie son obligatorios.';
    } elseif ($errFoto) {
        $err = $errFoto;
    } else {
        $fotoFinal = $row['foto'];
        if ($quitar_foto) { $fotoFinal = null; }
        if ($nuevaFoto)   { $fotoFinal = $nuevaFoto; }

        // Si reemplaza/quita foto y habia una anterior, intentamos borrarla del disco.
        if ($id && !empty($row['foto']) && $fotoFinal !== $row['foto']) {
            $viejo = __DIR__ . '/uploads/animales/' . basename($row['foto']);
            if (is_file($viejo)) { @unlink($viejo); }
        }

        if ($id) {
            $stmt = db()->prepare("UPDATE animales SET nombre=?,especie=?,raza=?,edad=?,sexo=?,descripcion=?,estado=?,foto=? WHERE id=?");
            $stmt->execute([$row['nombre'],$row['especie'],$row['raza'],$row['edad'],$row['sexo'],$row['descripcion'],$row['estado'],$fotoFinal,$id]);
        } else {
            $stmt = db()->prepare("INSERT INTO animales (nombre,especie,raza,edad,sexo,descripcion,estado,foto,fecha_ingreso) VALUES (?,?,?,?,?,?,?,?,CURDATE())");
            $stmt->execute([$row['nombre'],$row['especie'],$row['raza'],$row['edad'],$row['sexo'],$row['descripcion'],$row['estado'],$fotoFinal]);
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
      <h3 class="text-primary">🐾 <?= $id ? 'Editar' : 'Registrar' ?> animal</h3>
      <?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
      <form method="post" class="row g-3" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <?php if (!empty($row['foto'])): ?>
          <div class="col-12 text-center">
            <img src="<?= e($BASE_URL) ?>/uploads/animales/<?= e($row['foto']) ?>"
                 alt="Foto de <?= e($row['nombre']) ?>"
                 class="rounded shadow-sm" style="max-height:220px; object-fit:cover;">
            <div class="form-check mt-2 d-inline-block">
              <input class="form-check-input" type="checkbox" name="quitar_foto" id="quitar_foto" value="1">
              <label class="form-check-label small" for="quitar_foto">Quitar foto actual</label>
            </div>
          </div>
        <?php endif; ?>

        <div class="col-md-6">
          <label class="form-label">Nombre *</label>
          <input class="form-control" name="nombre" required value="<?= e($row['nombre']) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Especie *</label>
          <select class="form-select" name="especie" required>
            <?php foreach (['perro','gato','conejo','ave','otro'] as $sp): ?>
              <option value="<?= e($sp) ?>" <?= $row['especie']===$sp?'selected':'' ?>><?= e(ucfirst($sp)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Sexo</label>
          <select class="form-select" name="sexo">
            <option value="macho"  <?= $row['sexo']==='macho'?'selected':'' ?>>Macho</option>
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
          <label class="form-label">Foto de referencia</label>
          <input class="form-control" type="file" name="foto" accept="image/jpeg,image/png,image/webp,image/gif">
          <small class="text-muted">JPG, PNG, WEBP o GIF. M&aacute;ximo 3 MB.</small>
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
