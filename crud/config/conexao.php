<?php
// Configuração de conexão com o banco de dados MySQL
$servidor = "localhost";  // Seu servidor MySQL
$usuario = "root";        // Usuário MySQL (padrão: root)
$senha = "";              // Senha MySQL (deixe em branco se não tiver)
$banco = "elarion";       // Nome do banco de dados

// Criar conexão
$conexao = mysqli_connect($servidor, $usuario, $senha, $banco);

// Verificar conexão
if (!$conexao) {
    die("Erro na conexão: " . mysqli_connect_error() . 
        "<br><br>Se é a primeira vez, acesse: <a href='setup.php'>setup.php</a>");
}

// Define charset UTF-8
mysqli_set_charset($conexao, "utf8");

?>
