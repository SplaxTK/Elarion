<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$searchTerm = trim((string) ($_GET['q'] ?? ''));
$roleFilter = in_array($_GET['role'] ?? '', ['USER', 'ADMIN'], true) ? $_GET['role'] : 'ALL';
$statusFilter = in_array($_GET['status'] ?? '', ['ACTIVE', 'INACTIVE'], true) ? $_GET['status'] : 'ALL';
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;

$filters = [];
if ($searchTerm !== '') {
    $filters[] = '(LOWER(username) LIKE :username_term OR LOWER(email) LIKE :email_term OR LOWER(display_name) LIKE :display_name_term)';
}
if ($roleFilter !== 'ALL') {
    $filters[] = 'role = :role';
}
if ($statusFilter !== 'ALL') {
    $filters[] = 'status = :status';
}

$query = 'SELECT * FROM usuarios';
if ($filters) {
    $query .= ' WHERE ' . implode(' AND ', $filters);
}
$query .= ' ORDER BY created_at DESC LIMIT :offset, :limit';

$statement = $pdo->prepare($query);

$params = [];
if ($searchTerm !== '') {
    $searchPattern = '%' . strtolower($searchTerm) . '%';
    $params[':username_term'] = $searchPattern;
    $params[':email_term'] = $searchPattern;
    $params[':display_name_term'] = $searchPattern;
}
if ($roleFilter !== 'ALL') {
    $params[':role'] = $roleFilter;
}
if ($statusFilter !== 'ALL') {
    $params[':status'] = $statusFilter;
}
$countParams = $params;
$offset = ($page - 1) * $perPage;
$params[':offset'] = $offset;
$params[':limit'] = $perPage;

$statement->execute($params);
$users = $statement->fetchAll(PDO::FETCH_ASSOC);

$totalCountStatement = $pdo->prepare('SELECT COUNT(*) FROM usuarios' . ($filters ? ' WHERE ' . implode(' AND ', $filters) : ''));
$totalCountStatement->execute($countParams);
$totalUsers = (int) $totalCountStatement->fetchColumn();

$stats = [
    'total' => (int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn(),
    'admins' => (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE role = 'ADMIN'")->fetchColumn(),
    'active' => (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE status = 'ACTIVE'")->fetchColumn(),
];

$actionLog = $pdo->query('SELECT a.*, u.username AS actor_username FROM admin_actions a INNER JOIN usuarios u ON u.id = a.actor_id ORDER BY a.created_at DESC LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!validateCsrf($token)) {
        setFlash('error', 'Token inválido. Tente novamente.');
        redirect('index.php');
    }

    $targetId = (int) ($_POST['user_id'] ?? 0);
    $newStatus = in_array($_POST['status'] ?? '', ['ACTIVE', 'INACTIVE'], true) ? $_POST['status'] : 'ACTIVE';

    if ($targetId <= 0) {
        setFlash('error', 'Usuário inválido.');
        redirect('index.php');
    }

    $currentUser = currentUser();
    $userRecord = getUserById($targetId);
    if (!$userRecord) {
        setFlash('error', 'Usuário não encontrado.');
        redirect('index.php');
    }

    $newRole = isAdminEmail($userRecord['email']) ? 'ADMIN' : 'USER';

    if ((int) $userRecord['id'] === (int) $currentUser['id'] && $newRole !== 'ADMIN') {
        setFlash('error', 'Você não pode remover o próprio acesso de administrador.');
        redirect('index.php');
    }

    $statement = $pdo->prepare('UPDATE usuarios SET role = :role, status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
    $statement->execute([
        ':role' => $newRole,
        ':status' => $newStatus,
        ':id' => $targetId,
    ]);

    logAdminAction((int) $currentUser['id'], 'update_user', 'Atualizou usuário #' . $targetId . ' para role=' . $newRole . ' status=' . $newStatus);
    setFlash('success', 'Permissões do usuário atualizadas com sucesso.');
    redirect('index.php');
}

$flashMessages = getFlash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo | Elarion</title>
    <link rel="stylesheet" href="../assents/css/style.css">
    <script src="../assents/js/script.js" defer></script>
</head>
<body class="admin-page interior-page">
    <?php require_once __DIR__ . '/../includes/header.php'; ?>
    <main class="admin-shell">
        <section class="admin-header">
            <div>
                <p class="eyebrow">Administração</p>
                <h1>Painel do reino</h1>
            </div>
            <a class="button button-primary" href="../index.php">Voltar ao site</a>
        </section>

        <?php foreach ($flashMessages as $flash): ?>
            <div class="notice notice-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endforeach; ?>

        <section class="admin-stats">
            <article>
                <span>Total de usuários</span>
                <strong><?= $stats['total'] ?></strong>
            </article>
            <article>
                <span>Administradores</span>
                <strong><?= $stats['admins'] ?></strong>
            </article>
            <article>
                <span>Contas ativas</span>
                <strong><?= $stats['active'] ?></strong>
            </article>
        </section>

        <section class="admin-panel">
            <div class="admin-toolbar">
                <form method="get" class="admin-search-form">
                    <input type="search" name="q" value="<?= htmlspecialchars($searchTerm) ?>" placeholder="Buscar por nome, e-mail ou usuário">
                    <select name="role">
                        <option value="ALL" <?= $roleFilter === 'ALL' ? 'selected' : '' ?>>Todos os papéis</option>
                        <option value="USER" <?= $roleFilter === 'USER' ? 'selected' : '' ?>>USER</option>
                        <option value="ADMIN" <?= $roleFilter === 'ADMIN' ? 'selected' : '' ?>>ADMIN</option>
                    </select>
                    <select name="status">
                        <option value="ALL" <?= $statusFilter === 'ALL' ? 'selected' : '' ?>>Todos os status</option>
                        <option value="ACTIVE" <?= $statusFilter === 'ACTIVE' ? 'selected' : '' ?>>ACTIVE</option>
                        <option value="INACTIVE" <?= $statusFilter === 'INACTIVE' ? 'selected' : '' ?>>INACTIVE</option>
                    </select>
                    <button class="button button-primary" type="submit">Filtrar</button>
                </form>
            </div>

            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Usuário</th>
                            <th>E-mail</th>
                            <th>Papel</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users === []): ?>
                            <tr>
                                <td colspan="5" class="empty-state">Nenhum usuário encontrado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($user['display_name']) ?></strong><br>
                                        <small><?= htmlspecialchars($user['username']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= isAdminEmail($user['email']) ? 'ADMIN' : 'USER' ?></td>
                                    <td>
                                        <span class="status-badge status-<?= strtolower($user['status']) ?>"><?= htmlspecialchars($user['status']) ?></span>
                                    </td>
                                    <td>
                                        <form method="post" class="inline-form">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                            <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
                                            <span><?= isAdminEmail($user['email']) ? 'ADMIN' : 'USER' ?></span>
                                            <select name="status">
                                                <option value="ACTIVE" <?= $user['status'] === 'ACTIVE' ? 'selected' : '' ?>>ACTIVE</option>
                                                <option value="INACTIVE" <?= $user['status'] === 'INACTIVE' ? 'selected' : '' ?>>INACTIVE</option>
                                            </select>
                                            <button class="button button-outline" type="submit">Salvar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <?php $totalPages = max(1, (int) ceil($totalUsers / $perPage)); ?>
                <?php if ($page > 1): ?>
                    <a href="?q=<?= urlencode($searchTerm) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>&page=<?= $page - 1 ?>">Anterior</a>
                <?php endif; ?>
                <span>Página <?= $page ?> de <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a href="?q=<?= urlencode($searchTerm) ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>&page=<?= $page + 1 ?>">Próxima</a>
                <?php endif; ?>
            </div>
        </section>

        <section class="admin-log">
            <h2>Histórico recente</h2>
            <ul>
                <?php foreach ($actionLog as $log): ?>
                    <li>
                        <strong><?= htmlspecialchars($log['actor_username']) ?></strong>
                        <span><?= htmlspecialchars($log['action']) ?></span>
                        <small><?= htmlspecialchars($log['detail'] ?? '') ?></small>
                        <time><?= htmlspecialchars($log['created_at']) ?></time>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </main>
</body>
</html>
