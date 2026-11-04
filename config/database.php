<?php

// =====================================================
// CARREGAMENTO DAS VARIÁVEIS DE AMBIENTE
// =====================================================

$env = [];

$envPaths = [
    dirname(__DIR__) . '/.env',
    dirname(__DIR__, 2) . '/.env'
];

foreach ($envPaths as $path) {
    if (is_file($path) && is_readable($path)) {
        $parsedEnv = @parse_ini_file(
            $path,
            false,
            INI_SCANNER_RAW
        );

        if (is_array($parsedEnv)) {
            $env = array_merge($env, $parsedEnv);
        }
    }
}

// Busca primeiro nas variáveis do servidor,
// depois nas variáveis disponíveis no PHP e no .env.

$readEnv = static function (
    array $names,
    ?string $default = null
) use ($env): ?string {

    foreach ($names as $name) {

        $value = getenv($name);

        if ($value !== false && trim((string) $value) !== '') {
            return trim((string) $value);
        }

        $value = $_ENV[$name] ?? null;

        if ($value !== null && trim((string) $value) !== '') {
            return trim((string) $value);
        }

        $value = $_SERVER[$name] ?? null;

        if ($value !== null && trim((string) $value) !== '') {
            return trim((string) $value);
        }

        $value = $env[$name] ?? null;

        if ($value !== null && trim((string) $value) !== '') {
            return trim((string) $value);
        }
    }

    return $default;
};


// =====================================================
// JWT
// =====================================================

$jwtSecret = $readEnv(['JWT_SECRET_KEY']);

if ($jwtSecret !== null) {
    putenv('JWT_SECRET_KEY=' . $jwtSecret);
    $_ENV['JWT_SECRET_KEY'] = $jwtSecret;
    $_SERVER['JWT_SECRET_KEY'] = $jwtSecret;
}


// =====================================================
// CONFIGURAÇÃO DO MYSQL - CLEVER CLOUD
// =====================================================

// Prioriza as variáveis oficiais do Clever Cloud.
// Mantém DB_* como alternativa.

$host = $readEnv([
    'MYSQL_ADDON_HOST',
    'DB_HOST'
]);

$port = $readEnv([
    'MYSQL_ADDON_PORT',
    'DB_PORT'
], '3306');

$db = $readEnv([
    'MYSQL_ADDON_DB',
    'DB_NAME'
]);

$user = $readEnv([
    'MYSQL_ADDON_USER',
    'DB_USER'
]);

$pass = $readEnv([
    'MYSQL_ADDON_PASSWORD',
    'DB_PASSWORD',
    'SENHA'
]);

$charset = 'utf8mb4';


// =====================================================
// CONEXÃO PDO
// =====================================================

$pdo = null;
$dbConnectionError = null;

try {

    // Verifica se todas as credenciais foram configuradas.

    $missing = [];

    if (!$host) {
        $missing[] = 'MYSQL_ADDON_HOST / DB_HOST';
    }

    if (!$port) {
        $missing[] = 'MYSQL_ADDON_PORT / DB_PORT';
    }

    if (!$db) {
        $missing[] = 'MYSQL_ADDON_DB / DB_NAME';
    }

    if (!$user) {
        $missing[] = 'MYSQL_ADDON_USER / DB_USER';
    }

    if (!$pass) {
        $missing[] = 'MYSQL_ADDON_PASSWORD / DB_PASSWORD';
    }

    if (!empty($missing)) {
        throw new RuntimeException(
            'Variáveis do banco não configuradas: ' .
            implode(', ', $missing)
        );
    }

    // Monta o DSN do MySQL.

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $host,
        $port,
        $db,
        $charset
    );

    // Opções do PDO.

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

        PDO::ATTR_EMULATE_PREPARES => false,

        PDO::ATTR_TIMEOUT => 10
    ];

    // Estabelece a conexão.

    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        $options
    );

} catch (Throwable $e) {

    $pdo = null;

    $dbConnectionError = $e->getMessage();

    error_log(
        '[elarion][db] Erro de conexão: ' .
        $dbConnectionError
    );
}