<?php
// Configurações do banco de dados
$host = 'localhost';
$user = 'root'; // Seu usuário do MySQL
$password = ''; // Sua senha do MySQL
$database = 'pi'; // Nome do banco que você criou no phpMyAdmin

// Criar conexão
$conn = new mysqli($host, $user, $password, $database);

// Verificar conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Definir charset para UTF-8
$conn->set_charset("utf8");
?>