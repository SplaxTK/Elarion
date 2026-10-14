<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if (!$pdo) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        rememberAuthForm('login', [
            'identificador' => trim((string) ($_POST['identificador'] ?? '')),
        ], ['_form' => 'Não foi possível conectar ao banco de dados. Tente novamente em alguns instantes.']);
    }
    redirect('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('error', 'Requisição inválida.');
    redirect('../index.php');
}

if (isset($_GET['acao']) && $_GET['acao'] === 'recuperar') {
    setFlash('info', 'Recuperação de senha ainda não está habilitada. Configure SMTP para ativar este fluxo.');
    redirect('../index.php');
}

$identifier = trim((string) ($_POST['identificador'] ?? ''));
$password = (string) ($_POST['senha'] ?? '');

if (!validateCsrf($_POST['csrf_token'] ?? '')) {
    rememberAuthForm('login', ['identificador' => $identifier], ['_form' => 'Token inválido. Tente novamente.']);
    redirect('../index.php');
}

if ($identifier === '' || $password === '') {
    $errors = [];
    if ($identifier === '') {
        $errors['identificador'] = 'Informe seu usuário ou e-mail.';
    }
    if ($password === '') {
        $errors['senha'] = 'Informe sua senha.';
    }
    rememberAuthForm('login', ['identificador' => $identifier], $errors);
    redirect('../index.php');
}

if (strlen($password) < 6) {
    rememberAuthForm('login', ['identificador' => $identifier], ['senha' => 'A senha deve ter pelo menos 6 caracteres.']);
    redirect('../index.php');
}

$statement = $pdo->prepare('SELECT * FROM usuarios WHERE LOWER(username) = LOWER(:username) OR LOWER(email) = LOWER(:email) LIMIT 1');
$statement->execute([
    ':username' => $identifier,
    ':email' => $identifier,
]);
$user = $statement->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password_hash'])) {
    rememberAuthForm('login', ['identificador' => $identifier], ['senha' => 'Usuário/e-mail ou senha inválidos.']);
    redirect('../index.php');
}

if ($user['status'] !== 'ACTIVE') {
    rememberAuthForm('login', ['identificador' => $identifier], ['senha' => 'Esta conta está inativa e não pode realizar login.']);
    redirect('../index.php');
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['user_username'] = $user['username'];
$_SESSION['user_display_name'] = $user['display_name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = isAdminEmail($user['email']) ? 'ADMIN' : 'USER';
$_SESSION['user_status'] = $user['status'];

$pdo->prepare('UPDATE usuarios SET last_login_at = CURRENT_TIMESTAMP, role = :role WHERE id = :id')->execute([
    ':role' => isAdminEmail($user['email']) ? 'ADMIN' : 'USER',
    ':id' => $user['id'],
]);

clearAuthForm();
setFlash('success', 'Login realizado com sucesso.');
redirect('../index.php');
