<?php
declare(strict_types=1);
 
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Origin, User-Agent');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
 
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
 
function respond(array $data, int $code = 200): never
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['status' => 'error', 'message' => 'Método inválido.'], 405);
}
 
require_once dirname(__DIR__, 2) . '/config/database.php';
 
if (!$pdo) {
    respond(['status' => 'error', 'message' => 'Servidor indisponível. Tente novamente.'], 503);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['status' => 'error', 'message' => 'Método inválido.'], 405);
}

require_once dirname(__DIR__, 2) . '/config/database.php';

if (!$pdo) {
    respond(['status' => 'error', 'message' => 'Servidor indisponível. Tente novamente.'], 503);
}

// Aceita form-urlencoded ($_POST) ou JSON no corpo da requisição
$input = $_POST;
if (!$input) {
    $raw = file_get_contents('php://input') ?: '';
    $json = json_decode($raw, true);
    if (is_array($json)) {
        $input = $json;
    } else {
        parse_str($raw, $input);
    }
}

$identifier = trim((string) ($input['username'] ?? ''));
$password   = (string) ($input['password'] ?? '');

if ($identifier === '' || $password === '') {
    respond(['status' => 'error', 'message' => 'Preencha usuário e senha.'], 400);
}

try {
    $stmt = $pdo->prepare(
        'SELECT id, username, display_name, password_hash, status
         FROM usuarios
         WHERE LOWER(username) = LOWER(:u) OR LOWER(email) = LOWER(:e)
         LIMIT 1'
    );
    $stmt->execute([':u' => $identifier, ':e' => $identifier]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log('[elarion][api_login] ' . $e->getMessage());
    respond(['status' => 'error', 'message' => 'Erro interno no servidor.'], 500);
}

// Hash falso para o tempo de resposta ser parecido quando o usuário não existe
$hash = $user['password_hash'] ?? '$2y$10$usesomesillystringforeuseabcdefghijklmnopqrstuvwxyz12';

if (!$user || !password_verify($password, $hash)) {
    usleep(400000); // atrasa tentativas de força bruta
    respond(['status' => 'error', 'message' => 'Usuário ou senha inválidos.'], 401);
}

if ($user['status'] !== 'ACTIVE') {
    respond(['status' => 'error', 'message' => 'Esta conta está inativa.'], 403);
}

try {
    $pdo->prepare('UPDATE usuarios SET last_login_at = CURRENT_TIMESTAMP WHERE id = :id')
        ->execute([':id' => $user['id']]);
} catch (PDOException $e) {
    error_log('[elarion][api_login] last_login: ' . $e->getMessage());
}

respond([
    'status'   => 'success',
    'message'  => 'Login efetuado com sucesso!',
    'user_id'  => (int) $user['id'],
    'username' => $user['display_name'] ?: $user['username'],
]);