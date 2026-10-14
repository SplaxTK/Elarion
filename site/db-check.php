<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

$expected = getenv('DB_CHECK_TOKEN');
if (!$expected || !hash_equals($expected, (string) ($_GET['token'] ?? ''))) {
    http_response_code(404);
    exit;
}

echo 'pdo_mysql: ' . (extension_loaded('pdo_mysql') ? 'OK' : 'AUSENTE') . "\n";
foreach (['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_SSL_CA'] as $variable) {
    $value = getenv($variable);
    echo $variable . ': ' . (($value !== false && $value !== '') ? 'definida' : 'VAZIA') . "\n";
}
echo 'config/certs/aiven-ca.pem: ' . (is_file(__DIR__ . '/../config/certs/aiven-ca.pem') ? 'existe' : 'nao existe') . "\n\n";

require __DIR__ . '/includes/auth.php';

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
foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_NUM) as $table) {
    echo ' - ' . $table[0] . "\n";
}