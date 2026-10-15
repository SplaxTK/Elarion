<?php

declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$message = '';
$sqlState = '';
$hasError = false;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $expectedToken = getenv('DB_CHECK_TOKEN');
    $submittedToken = $_POST['token'] ?? null;

    if (!is_string($expectedToken) || $expectedToken === '') {
        http_response_code(503);
        $hasError = true;
        $message = 'Diagnóstico indisponível: configure DB_CHECK_TOKEN no ambiente Production da Vercel.';
    } elseif (!is_string($submittedToken) || !hash_equals($expectedToken, $submittedToken)) {
        http_response_code(403);
        $hasError = true;
        $message = 'Token inválido.';
    } else {
        require_once dirname(__DIR__, 2) . '/config/database.php';

        if (!$pdo) {
            $hasError = true;
            if ($dbConnectionError === 'CA do banco não encontrada (defina DB_SSL_CA ou config/certs/aiven-ca.pem)') {
                $message = 'Falha na configuração TLS: a CA do Aiven não foi encontrada. Verifique DB_SSL_CA no ambiente Production.';
            } else {
                $connectionError = is_string($dbConnectionError) ? $dbConnectionError : '';
                $connectionSqlState = '';
                $driverCode = '';
                if (preg_match('/SQLSTATE\[([A-Z0-9]+)\](?:\s+\[(\d+)\])?/i', $connectionError, $matches) === 1) {
                    $connectionSqlState = strtoupper($matches[1]);
                    $driverCode = $matches[2] ?? '';
                }

                $message = match ($driverCode) {
                    '1045' => 'O Aiven recusou a autenticação. Confira DB_USER e DB_PASSWORD.',
                    '1049' => 'O MySQL não encontrou o banco configurado. Confira DB_NAME.',
                    '1130' => 'O Aiven recusou a origem da conexão. Confira as regras de acesso do serviço.',
                    '2002', '2003', '2005' => 'Não foi possível alcançar o servidor MySQL. Confira DB_HOST, DB_PORT e as regras de acesso do Aiven.',
                    '2026' => 'A conexão TLS falhou. Confira DB_SSL_CA e o certificado CA do Aiven.',
                    default => 'O PHP não conseguiu estabelecer conexão com o banco. Confira as variáveis DB_* e o acesso do Aiven.',
                };
                $sqlState = implode(' / ', array_filter([
                    $connectionSqlState,
                    $driverCode !== '' ? 'MySQL ' . $driverCode : '',
                ]));
            }
        } else {
            try {
                if ((string) $pdo->query('SELECT 1')->fetchColumn() !== '1') {
                    throw new RuntimeException('A consulta de teste não retornou o resultado esperado.');
                }

                $tables = $pdo->query('SHOW TABLES');
                $userTableExists = false;
                while (($tableName = $tables->fetchColumn()) !== false) {
                    if ((string) $tableName === 'usuarios') {
                        $userTableExists = true;
                        break;
                    }
                }

                if (!$userTableExists) {
                    $hasError = true;
                    $message = 'Conexão com o banco OK, mas a tabela usuarios não existe no banco selecionado.';
                } else {
                    $columns = $pdo->query('SHOW COLUMNS FROM usuarios')->fetchAll(PDO::FETCH_COLUMN);
                    $requiredColumns = ['id', 'username', 'display_name', 'email', 'password_hash', 'role', 'status', 'last_login_at'];
                    $missingColumns = array_values(array_diff($requiredColumns, $columns));

                    if ($missingColumns) {
                        $hasError = true;
                        $message = 'Conexão OK e tabela usuarios encontrada, mas faltam colunas: ' . implode(', ', $missingColumns) . '.';
                    } else {
                        $message = 'Conexão com o banco OK. A tabela usuarios e as colunas necessárias foram encontradas.';
                    }
                }
            } catch (PDOException $e) {
                $hasError = true;
                $sqlState = is_array($e->errorInfo) ? (string) ($e->errorInfo[0] ?? $e->getCode()) : (string) $e->getCode();
                error_log('[elarion][db-check] consulta de diagnóstico falhou: ' . $e->getMessage());
                $message = 'A conexão foi estabelecida, mas uma consulta de diagnóstico falhou.';
            } catch (RuntimeException $e) {
                $hasError = true;
                error_log('[elarion][db-check] consulta de teste retornou resultado inesperado.');
                $message = 'A conexão foi estabelecida, mas a consulta de teste retornou um resultado inesperado.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico do banco - Elarion</title>
</head>
<body>
    <main>
        <h1>Diagnóstico do banco</h1>
        <p>Este teste não altera dados. Use o token DB_CHECK_TOKEN configurado na Vercel.</p>
        <form method="post">
            <label for="token">Token de diagnóstico</label>
            <input id="token" name="token" type="password" autocomplete="off" required>
            <button type="submit">Testar conexão</button>
        </form>
        <?php if ($message !== ''): ?>
            <p role="<?= $hasError ? 'alert' : 'status' ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
            <?php if ($sqlState !== ''): ?>
                <p>SQLSTATE: <?= htmlspecialchars($sqlState, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</body>
</html>
