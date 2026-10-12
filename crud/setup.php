<?php
// Configuração de conexão para criar o banco de dados
$servidor = "localhost";
$usuario = "root";
$senha = "";

// Conectar sem banco de dados primeiro
$conexao = mysqli_connect($servidor, $usuario, $senha);

if (!$conexao) {
    die("Erro na conexão: " . mysqli_connect_error());
}

// Criar banco de dados
$sql_banco = "CREATE DATABASE IF NOT EXISTS elarion";
if (mysqli_query($conexao, $sql_banco)) {
    $msg = "Banco de dados 'elarion' criado/verificado com sucesso!<br>";
} else {
    $msg = "Erro ao criar banco: " . mysqli_error($conexao) . "<br>";
}

// Selecionar banco de dados
mysqli_select_db($conexao, "elarion");

// Criar tabela usuarios
$sql_tabela = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nick VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conexao, $sql_tabela)) {
    $msg .= "Tabela 'usuarios' criada/verificada com sucesso!<br>";
} else {
    $msg .= "Erro ao criar tabela: " . mysqli_error($conexao) . "<br>";
}

// Processar cadastro se for POST
$mensagem = "";
$tipo_mensagem = ""; // sucesso, erro
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
    $email = mysqli_real_escape_string($conexao, $_POST['email']);
    $senha = mysqli_real_escape_string($conexao, $_POST['senha']);
    
    if (empty($nome) || empty($email) || empty($senha)) {
        $mensagem = "Todos os campos são obrigatórios!";
        $tipo_mensagem = "erro";
    } else if (strlen($senha) < 6) {
        $mensagem = "A senha deve ter no mínimo 6 caracteres!";
        $tipo_mensagem = "erro";
    } else {
        // Verificar se nick já existe
        $verificar_nick = "SELECT * FROM usuarios WHERE nick = '$nome'";
        $resultado_nick = mysqli_query($conexao, $verificar_nick);
        
        // Verificar se email já existe
        $verificar_email = "SELECT * FROM usuarios WHERE email = '$email'";
        $resultado_email = mysqli_query($conexao, $verificar_email);
        
        if (mysqli_num_rows($resultado_nick) > 0) {
            $mensagem = "Este nick já está cadastrado!";
            $tipo_mensagem = "erro";
        } else if (mysqli_num_rows($resultado_email) > 0) {
            $mensagem = "Este email já está cadastrado!";
            $tipo_mensagem = "erro";
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nick, email, senha) VALUES ('$nome', '$email', '$senha_hash')";
            if (mysqli_query($conexao, $sql)) {
                $mensagem = "Usuário cadastrado com sucesso!";
                $tipo_mensagem = "sucesso";
            } else {
                $mensagem = "Erro ao cadastrar: " . mysqli_error($conexao);
                $tipo_mensagem = "erro";
            }
        }
    }
}

mysqli_close($conexao);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Setup - Elarion</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Cadastro - Elarion</h1>
        
        <h2>Cadastrar Novo Player</h2>
        <form method="POST">
            <div class="form-group">
                <label for="nome">Nick:</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
                <small style="color: #666; margin-top: 5px; display: block;">Mínimo de 6 caracteres</small>
            </div>
            
            <button type="submit">Cadastrar Usuário</button>
        </form>
        
        <?php if (!empty($mensagem)): ?>
            <div class="mensagem <?php echo $tipo_mensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
