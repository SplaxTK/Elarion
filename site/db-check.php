<?php
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

$expected = getenv('DB_CHECK_TOKEN');
if (!$expected) {
    http_response_code(403);
    echo "db-check.php esta no deploy, mas a variavel DB_CHECK_TOKEN esta VAZIA/ausente neste ambiente.\n";
    echo "Defina em Settings > Environment Variables (Production) e faca redeploy.\n";
    exit;
}
if (!hash_equals(trim($expected), trim((string) ($_GET['token'] ?? '')))) {
    http_response_code(403);
    echo "db-check.php esta no deploy, mas o token da URL nao confere com DB_CHECK_TOKEN.\n";
    exit;
}

echo 'pdo_mysql: ' . (extension_loaded('pdo_mysql') ? 'OK' : 'AUSENTE') . "\n";
foreach (['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_SSL_CA'] as $v) {
    $val = getenv($v);
    echo $v . ': ' . (($val !== false && $val !== '') ? 'definida' : 'VAZIA') . "\n";
}
echo 'config/certs/aiven-ca.pem: ' . (is_file(__DIR__ . '/../config/certs/aiven-ca.pem') ? 'existe' : 'nao existe') . "\n\n";

require __DIR__ . '/includes/auth.php'; // carrega database.php e roda ensureSchema()

if (!$pdo) {
    echo "Conexao: FALHOU\n";
    echo 'Motivo (resumido): ' . preg_replace('/\s+/', ' ', substr((string) $dbConnectionError, 0, 200)) . "\n";
    exit;
}

echo "Conexao: OK\n";
$cipher = $pdo->query("SHOW SESSION STATUS LIKE 'Ssl_cipher'")->fetch();
echo 'TLS cifra: ' . (($cipher['Value'] ?? '') !== '' ? $cipher['Value'] : 'NENHUMA (sem criptografia!)') . "\n";
echo 'Banco atual: ' . $pdo->query('SELECT DATABASE()')->fetchColumn() . "\n";
echo "Tabelas:\n";
foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_NUM) as $t) {
    echo ' - ' . $t[0] . "\n";
}