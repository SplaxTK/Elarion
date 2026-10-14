<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Este comando só pode ser executado via terminal." . PHP_EOL;
    exit(1);
}

require_once __DIR__ . '/../includes/auth.php';

$options = getopt('', ['username:', 'display-name:', 'email:', 'password:']);

$username = trim((string) ($options['username'] ?? ''));
$displayName = trim((string) ($options['display-name'] ?? $username));
$email = trim((string) ($options['email'] ?? ''));
$password = (string) ($options['password'] ?? '');

if ($username === '' || $email === '' || $password === '') {
    echo "Uso: php site/admin/create-admin.php --username=admin --email=admin@exemplo.com --password='SenhaForte123' [--display-name='Administrador']" . PHP_EOL;
    exit(1);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo 'E-mail inválido.' . PHP_EOL;
    exit(1);
}

if (!isAdminEmail($email)) {
    echo 'Adicione este e-mail à lista em config/admin_acounts.php antes de criar a conta de administrador.' . PHP_EOL;
    exit(1);
}

if (!passwordStrength($password) || strlen($password) < 6) {
    echo 'A senha deve ter pelo menos 6 caracteres, incluindo letras maiúsculas, minúsculas e números.' . PHP_EOL;
    exit(1);
}

ensureSchema();

$existing = $pdo->prepare('SELECT id, role FROM usuarios WHERE username = :username OR email = :email LIMIT 1');
$existing->execute([
    ':username' => $username,
    ':email' => $email,
]);
$record = $existing->fetch(PDO::FETCH_ASSOC);

if ($record) {
    $statement = $pdo->prepare('UPDATE usuarios SET username = :username, display_name = :display_name, email = :email, password_hash = :password_hash, role = :role, status = :status WHERE id = :id');
    $statement->execute([
        ':username' => $username,
        ':display_name' => $displayName,
        ':email' => $email,
        ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ':role' => 'ADMIN',
        ':status' => 'ACTIVE',
        ':id' => (int) $record['id'],
    ]);

    echo "Conta de administrador atualizada com sucesso: {$username}" . PHP_EOL;
    exit(0);
}

$statement = $pdo->prepare('INSERT INTO usuarios (username, display_name, email, password_hash, role, status) VALUES (:username, :display_name, :email, :password_hash, :role, :status)');
$statement->execute([
    ':username' => $username,
    ':display_name' => $displayName,
    ':email' => $email,
    ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
    ':role' => 'ADMIN',
    ':status' => 'ACTIVE',
]);

echo "Conta de administrador criada com sucesso: {$username}" . PHP_EOL;
