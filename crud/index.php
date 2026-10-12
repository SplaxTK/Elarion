<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <link rel="stylesheet" href="./style.css">
    <meta charset="UTF-8">
    <title>Cadastro - Elarion</title>

</head>
<body>

    <header>
        <h1>Bem-Vindo a Elarion</h1>
    </header>

    <main>
        <h2>Cadastro do Jogo</h2>
        <form action="processar_cadastro.php" method="POST">

            <label for="nome">Nick:</label>
            <input type="text" id="nome" name="nome" required>

            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required>

            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>

<button class="button-name" role="button">Cadastrar</button>

</form>
    </main>

    <footer>
        &copy; 2026 Elarion - Todos os direitos reservados.
    </footer>

</body>
</html>
