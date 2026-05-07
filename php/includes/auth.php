<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
    return current_user() !== null;
}

function role(): ?string {
    $u = current_user();
    return $u['rol'] ?? null;
}

function require_login(): void {
    if (!is_logged_in()) {
        global $BASE_URL;
        header("Location: {$BASE_URL}/login.php");
        exit;
    }
}

function require_role(array $roles): void {
    require_login();
    if (!in_array(role(), $roles, true)) {
        global $BASE_URL;
        $_SESSION['flash_error'] = 'No tienes permisos para esa pagina.';
        header("Location: {$BASE_URL}/dashboard.php");
        exit;
    }
}

function flash_set(string $type, string $msg): void {
    $_SESSION['flash_' . $type] = $msg;
}

function flash_pop(string $type): ?string {
    $key = 'flash_' . $type;
    if (!isset($_SESSION[$key])) return null;
    $m = $_SESSION[$key];
    unset($_SESSION[$key]);
    return $m;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_verify(?string $token): bool {
    return is_string($token) && hash_equals($_SESSION['csrf'] ?? '', $token);
}

function require_post_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf'] ?? null)) {
        http_response_code(400);
        die('Solicitud invalida (CSRF).');
    }
}

function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function rol_label(string $r): string {
    return [
        'admin' => 'Administrador',
        'veterinario' => 'Veterinario',
        'adoptante' => 'Adoptante',
        'donante' => 'Donante',
        'voluntario' => 'Voluntario',
    ][$r] ?? $r;
}

function estado_animal_badge(string $estado): string {
    $cls = [
        'disponible' => 'success',
        'adoptado' => 'secondary',
        'en_tratamiento' => 'warning',
        'no_disponible' => 'dark',
    ][$estado] ?? 'light';
    $lbl = [
        'disponible' => 'Disponible',
        'adoptado' => 'Adoptado',
        'en_tratamiento' => 'En tratamiento',
        'no_disponible' => 'No disponible',
    ][$estado] ?? $estado;
    return "<span class='badge bg-{$cls}'>" . e($lbl) . "</span>";
}

function estado_adopcion_badge(string $estado): string {
    $cls = [
        'pendiente' => 'warning',
        'aprobada' => 'success',
        'rechazada' => 'danger',
        'completada' => 'primary',
    ][$estado] ?? 'light';
    return "<span class='badge bg-{$cls}'>" . e(ucfirst($estado)) . "</span>";
}
