<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if (!$pdo) {
    setFlash('error', 'Não foi possível encerrar a sessão no momento.');
    redirect('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('error', 'A sessão não pôde ser encerrada.');
    redirect('../index.php');
}

if (!validateCsrf($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Token inválido. A sessão não foi encerrada.');
    redirect('../index.php');
}

$currentUser = currentUser();
if ($currentUser) {
    logAdminAction((int) $currentUser['id'], 'logout', 'Usuário saiu da conta');
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_destroy();
setFlash('success', 'Sessão encerrada com sucesso.');
redirect('../index.php');
