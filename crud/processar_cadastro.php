<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Receber dados do formulário
    $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
    $email = mysqli_real_escape_string($conexao, $_POST['email']);
    $senha = mysqli_real_escape_string($conexao, $_POST['senha']);
    
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    
    if (empty($nome) || empty($email) || empty($senha)) {
        echo "Erro: Todos os campos são obrigatórios!";
        exit;
    }
    
    $verificar = "SELECT * FROM usuarios WHERE email = '$email'";
    $resultado = mysqli_query($conexao, $verificar);
    
    if (mysqli_num_rows($resultado) > 0) {
        echo "Erro: Este email já está cadastrado!";
        exit;
    }
    
    $sql = "INSERT INTO usuarios (nick, email, senha) VALUES ('$nome', '$email', '$senha_hash')";
    
    if (mysqli_query($conexao, $sql)) {
        echo "✓ Cadastro realizado com sucesso!";
        // Redirecionar após 2 segundos
        header("refresh:2;url=index.html");
    } else {
        echo "✗ Erro ao cadastrar: " . mysqli_error($conexao);
    }
    
    // Fechar conexão
    mysqli_close($conexao);
}
?>
