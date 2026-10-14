<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireAuth();

$currentUser = currentUser();
$flashMessages = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!validateCsrf($token)) {
        setFlash('error', 'Token inválido. Tente novamente.');
        redirect('configuracoes.php');
    }

    $email = trim((string) ($_POST['email'] ?? ''));
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $newPasswordConfirm = (string) ($_POST['new_password_confirm'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'Informe um e-mail válido.');
        redirect('configuracoes.php');
    }

    $duplicate = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email AND id != :id LIMIT 1');
    $duplicate->execute([
        ':email' => $email,
        ':id' => $currentUser['id'],
    ]);
    if ($duplicate->fetch()) {
        setFlash('error', 'Este e-mail já está cadastrado.');
        redirect('configuracoes.php');
    }

    if ($newPassword !== '') {
        if ($newPassword !== $newPasswordConfirm) {
            setFlash('error', 'As senhas não coincidem.');
            redirect('configuracoes.php');
        }
        if (!passwordStrength($newPassword) || strlen($newPassword) < 6) {
            setFlash('error', 'A nova senha deve ter pelo menos 6 caracteres, incluindo letras maiúsculas, minúsculas e números.');
            redirect('configuracoes.php');
        }
    }

    $statement = $pdo->prepare('UPDATE usuarios SET display_name = :display_name, email = :email, updated_at = CURRENT_TIMESTAMP' . ($newPassword !== '' ? ', password_hash = :password_hash' : '') . ' WHERE id = :id');
    $params = [
        ':display_name' => $currentUser['username'],
        ':email' => $email,
        ':id' => $currentUser['id'],
    ];

    if ($newPassword !== '') {
        $params[':password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    $statement->execute($params);
    $_SESSION['user_display_name'] = $currentUser['username'];
    $_SESSION['user_email'] = $email;

    setFlash('success', 'Perfil atualizado com sucesso.');
    redirect('configuracoes.php');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações | Elarion</title>
    <link rel="stylesheet" href="./assents/css/style.css">
    <script src="./assents/js/script.js" defer></script>
</head>
<body class="interior-page">
    <?php require_once __DIR__ . '/includes/header.php'; ?>
    <main class="page-shell">
        <section class="profile-card settings-card">
            <?php foreach ($flashMessages as $flash): ?>
                <div class="notice notice-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endforeach; ?>

            <p class="eyebrow">Configurações</p>
            <h1>Editar conta</h1>
            <form method="post" class="settings-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">

                <label>
                    <span>E-mail</span>
                    <input type="email" name="email" value="<?= htmlspecialchars($currentUser['email']) ?>" required>
                </label>

                <label>
                    <span>Nova senha</span>
                    <input type="password" name="new_password" autocomplete="new-password">
                </label>

                <label>
                    <span>Confirmar nova senha</span>
                    <input type="password" name="new_password_confirm" autocomplete="new-password">
                </label>

                <button class="button button-primary" type="submit">Salvar alterações</button>
            </form>
        </section>
    </main>
</body>
</html>
