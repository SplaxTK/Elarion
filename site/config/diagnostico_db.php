<?php

header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/config.php';

echo "===== DIAGNOSTICO DO ELARION =====\n\n";

// 1. PHP e driver MySQL

echo "PHP: " . PHP_VERSION . "\n";

echo "PDO instalado: ";
var_export(class_exists('PDO'));
echo "\n";

echo "Driver PDO MySQL: ";
var_export(extension_loaded('pdo_mysql'));
echo "\n\n";

// 2. Variáveis recebidas pelo config.php

echo "===== CREDENCIAIS =====\n";

$variables = [
    'DB_HOST' => $host ?? null,
    'DB_PORT' => $port ?? null,
    'DB_NAME' => $db ?? null,
    'DB_USER' => $user ?? null,
    'DB_PASSWORD' => $pass ?? null
];

foreach ($variables as $name => $value) {
    echo $name . ": ";
    echo ($value !== null && $value !== '') ? "CONFIGURADA" : "AUSENTE";
    echo "\n";
}

// 3. Verifica se as variáveis oficiais do Clever Cloud existem

echo "\n===== CLEVER CLOUD =====\n";

$cleverVariables = [
    'MYSQL_ADDON_HOST',
    'MYSQL_ADDON_PORT',
    'MYSQL_ADDON_DB',
    'MYSQL_ADDON_USER',
    'MYSQL_ADDON_PASSWORD'
];

foreach ($cleverVariables as $name) {
    echo $name . ": ";
    echo getenv($name) !== false ? "DISPONIVEL" : "AUSENTE";
    echo "\n";
}

// 4. Resultado da conexão

echo "\n===== CONEXAO =====\n";

if ($pdo instanceof PDO) {

    echo "CONEXAO: FUNCIONOU!\n";

    try {
        $count = $pdo->query(
            "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE()"
        )->fetchColumn();

        echo "Quantidade de tabelas: " . $count . "\n";

        if ((int) $count === 0) {
            echo "AVISO: O banco está vazio.\n";
        }

    } catch (Throwable $e) {
        echo "Erro ao verificar tabelas.\n";
    }

} else {

    echo "CONEXAO: FALHOU!\n";

    $error = $dbConnectionError ?? 'Erro não identificado.';

    // Evita mostrar o nome do usuário na mensagem de erro.
    $error = preg_replace(
        "/Access denied for user '.*?'/i",
        "Access denied for configured user",
        $error
    );

    echo "ERRO: " . $error . "\n";
}

echo "\n===== FIM DO DIAGNOSTICO =====\n";