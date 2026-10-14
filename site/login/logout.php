<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

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
session_regenerate_id(true);
setFlash('success', 'Sessão encerrada com sucesso.');
redirect('../index.php');
