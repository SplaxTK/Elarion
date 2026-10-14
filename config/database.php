<?php

$envPath = dirname(__DIR__) . '/.env';
$env = [];

if (is_file($envPath) && is_readable($envPath)) {
    $parsedEnv = @parse_ini_file($envPath, true, INI_SCANNER_TYPED);
    if (is_array($parsedEnv)) {
        $env = $parsedEnv;
    }
}

if (getenv('JWT_SECRET_KEY') === false && !empty($env['JWT_SECRET_KEY'])) {
    putenv('JWT_SECRET_KEY=' . $env['JWT_SECRET_KEY']);
}

$host = getenv('DB_HOST') ?: ($env['DB_HOST'] ?? 'localhost');
$port = getenv('DB_PORT') ?: ($env['DB_PORT'] ?? '3306');
$db   = getenv('DB_NAME') ?: ($env['DB_NAME'] ?? 'elarion');
$user = getenv('DB_USER') ?: ($env['DB_USER'] ?? 'root');

$pass = getenv('DB_PASSWORD');

if ($pass === false || $pass === '') {
    $pass = $env['DB_PASSWORD'] ?? ($env['SENHA'] ?? '');
}

$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_TIMEOUT => 10,
];

$caFile = null;
$caPem = getenv('DB_SSL_CA');
if (($caPem === false || trim($caPem) === '') && !empty($env['DB_SSL_CA'])) {
    $caPem = (string) $env['DB_SSL_CA'];
}

if (is_string($caPem) && trim($caPem) !== '') {
    $caFile = sys_get_temp_dir() . '/elarion-db-ca.pem';
    $pem = str_replace('\\n', "\n", trim($caPem)) . "\n";
    if (!is_file($caFile) || file_get_contents($caFile) !== $pem) {
        @file_put_contents($caFile, $pem, LOCK_EX);
        @chmod($caFile, 0600);
    }
} elseif (is_file(__DIR__ . '/certs/aiven-ca.pem')) {
    $caFile = __DIR__ . '/certs/aiven-ca.pem';
}

$isLocalHost = in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'], true);
if (!$isLocalHost && $caFile !== null && is_file($caFile)) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = $caFile;
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
}

$pdo = null;
$dbConnectionError = null;

if (!$isLocalHost && ($caFile === null || !is_file($caFile))) {
    $dbConnectionError = 'CA do banco não encontrada (defina DB_SSL_CA ou config/certs/aiven-ca.pem)';
    error_log(sprintf('[elarion][db] %s host=%s port=%s db=%s', $dbConnectionError, $host, $port, $db));
} else {
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        $pdo = null;
        $dbConnectionError = $e->getMessage();
        error_log(sprintf(
            '[elarion][db] falha de conexão host=%s port=%s db=%s user=%s tls_ca=%s code=%s msg=%s',
            $host,
            $port,
            $db,
            $user,
            $caFile !== null ? 'sim' : 'não',
            (string) $e->getCode(),
            $e->getMessage(),
        ));
    }
}