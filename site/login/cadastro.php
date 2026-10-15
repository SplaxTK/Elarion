<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if (!$pdo) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        rememberAuthForm('register', [
            'usuario' => trim((string) ($_POST['usuario'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
        ], ['_form' => 'Não foi possível conectar ao banco de dados no momento. Tente novamente mais tarde.']);
    }
    redirect('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('error', 'Requisição inválida.');
    redirect('../index.php');
}

$username = trim((string) ($_POST['usuario'] ?? ''));
$displayName = $username;
$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['senha'] ?? '');
$confirmPassword = (string) ($_POST['confirmar_senha'] ?? '');
$acceptedTerms = !empty($_POST['aceite_termos']);

if (!validateCsrf($_POST['csrf_token'] ?? '')) {
    rememberAuthForm('register', ['usuario' => $username, 'email' => $email], ['_form' => 'Token inválido. Tente novamente.']);
    redirect('../index.php');
}

if ($username === '' || $email === '' || $password === '' || $confirmPassword === '') {
    $errors = [];
    if ($email === '') {
        $errors['email'] = 'Informe seu e-mail.';
    }
    if ($username === '') {
        $errors['usuario'] = 'Informe seu nome de usuário.';
    }
    if ($password === '') {
        $errors['senha'] = 'Informe uma senha.';
    }
    if ($confirmPassword === '') {
        $errors['confirmar_senha'] = 'Confirme sua senha.';
    }
    rememberAuthForm('register', ['usuario' => $username, 'email' => $email], $errors);
    redirect('../index.php');
}

if (!preg_match('/^[A-Za-z0-9_.-]{3,25}$/', $username)) {
    rememberAuthForm('register', ['usuario' => $username, 'email' => $email], ['usuario' => 'Use de 3 a 25 caracteres: letras, números, ponto, traço ou underscore.']);
    redirect('../index.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    rememberAuthForm('register', ['usuario' => $username, 'email' => $email], ['email' => 'Informe um e-mail válido.']);
    redirect('../index.php');
}

if (!$acceptedTerms) {
    rememberAuthForm('register', ['usuario' => $username, 'email' => $email], ['aceite_termos' => 'Aceite os termos de uso e a política de privacidade para continuar.']);
    redirect('../index.php');
}

if ($password !== $confirmPassword) {
    rememberAuthForm('register', ['usuario' => $username, 'email' => $email], ['confirmar_senha' => 'As senhas não coincidem.']);
    redirect('../index.php');
}

if (!passwordStrength($password) || strlen($password) < 6) {
    rememberAuthForm('register', ['usuario' => $username, 'email' => $email], ['senha' => 'Use pelo menos 6 caracteres, com letras maiúsculas, minúsculas e números.']);
    redirect('../index.php');
}

try {
    $duplicate = $pdo->prepare('SELECT username, email FROM usuarios WHERE LOWER(username) = LOWER(:username) OR LOWER(email) = LOWER(:email) LIMIT 1');
    $duplicate->execute([
        ':username' => $username,
        ':email' => $email,
    ]);
    $duplicateUser = $duplicate->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('[elarion][auth][cadastro] verificação de usuário duplicado falhou: ' . $e->getMessage());
    rememberAuthForm('register', ['usuario' => $username, 'email' => $email], ['_form' => 'Não foi possível processar o cadastro. Tente novamente mais tarde.']);
    redirect('../index.php');
}

if ($duplicateUser) {
    $usernameTaken = strcasecmp($duplicateUser['username'], $username) === 0;
    $emailTaken = strcasecmp($duplicateUser['email'], $email) === 0;
    $errors = [];
    if ($usernameTaken) {
        $errors['usuario'] = 'Este nome de usuário já está em uso.';
    }
    if ($emailTaken) {
        $errors['email'] = 'Este e-mail já está cadastrado.';
    }
    rememberAuthForm('register', ['usuario' => $username, 'email' => $email], $errors);
    redirect('../index.php');
}

$userRole = isAdminEmail($email) ? 'ADMIN' : 'USER';
try {
    $statement = $pdo->prepare('INSERT INTO usuarios (username, display_name, email, password_hash, role, status) VALUES (:username, :display_name, :email, :password_hash, :role, :status)');
    $statement->execute([
        ':username' => $username,
        ':display_name' => $displayName,
        ':email' => strtolower($email),
        ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ':role' => $userRole,
        ':status' => 'ACTIVE',
    ]);

    $userId = (int) $pdo->lastInsertId();
} catch (PDOException $e) {
    error_log('[elarion][auth][cadastro] criação de usuário falhou: ' . $e->getMessage());
    rememberAuthForm('register', ['usuario' => $username, 'email' => $email], ['_form' => 'Não foi possível processar o cadastro. Tente novamente mais tarde.']);
    redirect('../index.php');
}

session_regenerate_id(true);
$_SESSION['user_id'] = $userId;
$_SESSION['user_username'] = $username;
$_SESSION['user_display_name'] = $username;
$_SESSION['user_email'] = strtolower($email);
$_SESSION['user_role'] = $userRole;
$_SESSION['user_status'] = 'ACTIVE';

clearAuthForm();
setFlash('success', 'Cadastro realizado com sucesso. Bem-vindo(a) ao Elarion!');
redirect('../index.php');
