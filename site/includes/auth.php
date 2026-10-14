<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_name('elarion_session');
    session_start();
}

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/config/admin_acounts.php';

function ensureSchema(): void
{
    global $pdo;

    if (!$pdo) {
        return;
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL,
        display_name VARCHAR(80) NOT NULL,
        email VARCHAR(150) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('USER', 'ADMIN') NOT NULL DEFAULT 'USER',
        status ENUM('ACTIVE', 'INACTIVE') NOT NULL DEFAULT 'ACTIVE',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        last_login_at TIMESTAMP NULL DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY unique_username (username),
        UNIQUE KEY unique_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_actions (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        actor_id INT UNSIGNED NOT NULL,
        action VARCHAR(120) NOT NULL,
        detail TEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_actor_id (actor_id),
        CONSTRAINT fk_admin_actions_actor FOREIGN KEY (actor_id) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function currentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $email = (string) ($_SESSION['user_email'] ?? '');

    return [
        'id' => (int) ($_SESSION['user_id'] ?? 0),
        'username' => (string) ($_SESSION['user_username'] ?? ''),
        'display_name' => (string) ($_SESSION['user_username'] ?? ''),
        'email' => $email,
        'role' => isAdminEmail($email) ? 'ADMIN' : 'USER',
        'status' => strtoupper((string) ($_SESSION['user_status'] ?? 'ACTIVE')),
    ];
}

function isAuthenticated(): bool
{
    return currentUser() !== null;
}

function isAdmin(): bool
{
    $user = currentUser();

    return $user !== null && isAdminEmail($user['email']);
}

function isAdminEmail(string $email): bool
{
    $adminEmails = array_map(
        static fn (string $configuredEmail): string => strtolower(trim($configuredEmail)),
        FLOWUP_ADMIN_EMAILS,
    );

    return in_array(strtolower(trim($email)), $adminEmails, true);
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'][] = [
        'type' => $type,
        'message' => $message,
    ];
}

function rememberAuthForm(string $panel, array $values, array $errors = []): void
{
    $_SESSION['auth_form'] = [
        'panel' => $panel,
        'values' => $values,
        'errors' => $errors,
    ];
    $_SESSION['auth_open_panel'] = $panel;
}

function clearAuthForm(): void
{
    unset($_SESSION['auth_form'], $_SESSION['auth_open_panel']);
}

function getFlash(): array
{
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return $flash;
}

function redirect(string $path): void
{
    header('Location: ' . $path, true, 302);
    exit;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

function validateCsrf(string $token): bool
{
    return hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token);
}

function relativeSiteUrl(string $path): string
{
    $scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $normalizedPath = ltrim($path, './');

    if (preg_match('#/(admin|crud|login|termos)$#', $scriptDirectory)) {
        return '../' . $normalizedPath;
    }

    return './' . $normalizedPath;
}

function requireAuth(): void
{
    if (!isAuthenticated()) {
        setFlash('error', 'Faça login para continuar.');
        redirect(relativeSiteUrl('index.php'));
    }
}

function requireAdmin(): void
{
    requireAuth();

    if (!isAdmin()) {
        setFlash('error', 'Acesso negado. Você não tem permissão para acessar este painel.');
        redirect(relativeSiteUrl('index.php'));
    }
}

function logAdminAction(int $actorId, string $action, ?string $detail = null): void
{
    global $pdo;

    if (!$pdo) {
        return;
    }

    $statement = $pdo->prepare('INSERT INTO admin_actions (actor_id, action, detail) VALUES (:actor_id, :action, :detail)');
    $statement->execute([
        ':actor_id' => $actorId,
        ':action' => $action,
        ':detail' => $detail,
    ]);
}

function firstAdminExists(): bool
{
    global $pdo;

    if (!$pdo) {
        return false;
    }

    $statement = $pdo->query("SELECT 1 FROM usuarios WHERE role = 'ADMIN' LIMIT 1");
    return $statement !== false && $statement->fetchColumn() !== false;
}

function getUserById(int $userId): ?array
{
    global $pdo;

    if (!$pdo) {
        return null;
    }

    $statement = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
    $statement->execute([':id' => $userId]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}

function passwordStrength(string $password): bool
{
    return strlen($password) >= 6
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/\d/', $password);
}

ensureSchema();
