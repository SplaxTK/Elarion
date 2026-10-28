<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

if (!$pdo) {
    setFlash('error', 'Não foi possível conectar ao banco de dados.');
    redirect('../index.php');
}

$targetId = (int) ($_GET['id'] ?? 0);
if ($targetId <= 0) {
    setFlash('error', 'ID de usuário inválido.');
    redirect('index.php');
}

$userRecord = getUserById($targetId);
if (!$userRecord) {
    setFlash('error', 'Usuário não encontrado.');
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!validateCsrf($token)) {
        setFlash('error', 'Token inválido. Tente novamente.');
        redirect("editar.php?id=$targetId");
    }

    $newUsername = trim((string) ($_POST['username'] ?? ''));
    $newEmail = strtolower(trim((string) ($_POST['email'] ?? '')));
    $newStatus = in_array($_POST['status'] ?? '', ['ACTIVE', 'INACTIVE'], true) ? $_POST['status'] : 'ACTIVE';
    $currentUser = currentUser();

    if (!preg_match('/^[A-Za-z0-9_.-]{3,25}$/', $newUsername)) {
        setFlash('error', 'Use de 3 a 25 caracteres no usuário: letras, números, ponto, traço ou underscore.');
        redirect("editar.php?id=$targetId");
    }

    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'Informe um e-mail válido.');
        redirect("editar.php?id=$targetId");
    }

    $duplicate = $pdo->prepare('SELECT id FROM usuarios WHERE id != :id AND (LOWER(username) = LOWER(:username) OR LOWER(email) = LOWER(:email)) LIMIT 1');
    $duplicate->execute([
        ':id' => $targetId,
        ':username' => $newUsername,
        ':email' => $newEmail,
    ]);
    if ($duplicate->fetch(PDO::FETCH_ASSOC)) {
        setFlash('error', 'Este nome de usuário ou e-mail já está em uso.');
        redirect("editar.php?id=$targetId");
    }

    $newRole = isAdminEmail($newEmail) ? 'ADMIN' : 'USER';

    if ((int) $userRecord['id'] === (int) $currentUser['id'] && ($newRole !== 'ADMIN' || $newStatus !== 'ACTIVE')) {
        setFlash('error', 'Você não pode remover nem desativar o próprio acesso de administrador.');
        redirect("editar.php?id=$targetId");
    }

    $statement = $pdo->prepare('UPDATE usuarios SET username = :username, display_name = :display_name, email = :email, role = :role, status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
    $statement->execute([
        ':username' => $newUsername,
        ':display_name' => $newUsername,
        ':email' => $newEmail,
        ':role' => $newRole,
        ':status' => $newStatus,
        ':id' => $targetId,
    ]);

    logAdminAction((int) $currentUser['id'], 'update_user', "Atualizou usuário #$targetId (User: $newUsername, Email: $newEmail, Status: $newStatus)");
    setFlash('success', 'Usuário atualizado com sucesso.');
    redirect('index.php');
}

$flashMessages = getFlash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário | Elarion</title>
    <link rel="stylesheet" href="../assents/css/style.css">
    <script src="../assents/js/script.js" defer></script>
</head>
<body class="admin-page interior-page">
    <?php require_once __DIR__ . '/../includes/header.php'; ?>
    
    <main class="admin-shell">
        <section class="admin-header">
            <div>
                <p class="eyebrow">Administração</p>
                <h1>Editar Usuário</h1>
            </div>
            <a class="button button-outline" href="index.php">Voltar</a>
        </section>

        <?php foreach ($flashMessages as $flash): ?>
            <div class="notice notice-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endforeach; ?>

        <section class="admin-panel" style="max-width: 600px; margin: 0 auto; padding: 20px; background: #fff; border-radius: 8px;">
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                
                <div style="margin-bottom: 15px;">
                    <label for="username" style="display: block; font-weight: bold; margin-bottom: 5px;">Usuário</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($userRecord['username']) ?>" required style="width: 100%; padding: 8px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="email" style="display: block; font-weight: bold; margin-bottom: 5px;">E-mail</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($userRecord['email']) ?>" required style="width: 100%; padding: 8px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="status" style="display: block; font-weight: bold; margin-bottom: 5px;">Status da Conta</label>
                    <select id="status" name="status" style="width: 100%; padding: 8px;">
                        <option value="ACTIVE" <?= $userRecord['status'] === 'ACTIVE' ? 'selected' : '' ?>>Ativa (ACTIVE)</option>
                        <option value="INACTIVE" <?= $userRecord['status'] === 'INACTIVE' ? 'selected' : '' ?>>Inativa (INACTIVE)</option>
                    </select>
                </div>

                <button class="button button-primary" type="submit" style="width: 100%;">Salvar Alterações</button>
            </form>
        </section>
    </main>
</body>
</html>