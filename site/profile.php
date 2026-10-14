<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireAuth();

$currentUser = currentUser();
$flashMessages = getFlash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil | Elarion</title>
    <link rel="stylesheet" href="./assents/css/style.css">
    <script src="./assents/js/script.js" defer></script>
</head>
<body class="interior-page">
    <?php require_once __DIR__ . '/includes/header.php'; ?>
    <main class="page-shell">
        <section class="profile-card">
            <?php foreach ($flashMessages as $flash): ?>
                <div class="notice notice-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endforeach; ?>

            <p class="eyebrow">Minha conta</p>
            <h1><?= htmlspecialchars($currentUser['display_name']) ?></h1>
            <div class="profile-grid">
                <div>
                    <label>Nome de usuário</label>
                    <strong><?= htmlspecialchars($currentUser['username']) ?></strong>
                </div>
                <div>
                    <label>E-mail</label>
                    <strong><?= htmlspecialchars($currentUser['email']) ?></strong>
                </div>
                <div>
                    <label>Papel</label>
                    <strong><?= htmlspecialchars($currentUser['role']) ?></strong>
                </div>
                <div>
                    <label>Status</label>
                    <strong><?= htmlspecialchars($currentUser['status']) ?></strong>
                </div>
            </div>
            <div class="profile-actions">
                <a class="button button-primary" href="./configuracoes.php">Editar perfil</a>
                <?php if (isAdmin()): ?>
                    <a class="button button-outline" href="./crud/index.php" target="_blank" rel="noopener noreferrer">Painel de admin</a>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
