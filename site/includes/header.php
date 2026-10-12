<header class="topbar">
    <div class="topbar-branding">
        <a class="site-logo" href="#inicio" aria-label="Elarion, início">ELARION</a>
    </div>
    <nav class="site-navigation" aria-label="Navegação principal">
        <a href="#inicio">Início</a>
        <a href="#mundo">O mundo</a>
        <a href="#recursos">Recursos</a>
        <a href="#novidades">Novidades</a>
        <a href="#sobre">Sobre</a>
    </nav>
    <div class="topbar-actions">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="configuracoes.php" class="pill active">
                <?php echo ($_SESSION['user_username'] ?? 'Perfil'); ?>
            </a>
        <?php else: ?>
            <button class="login-trigger" type="button" data-auth-open aria-haspopup="dialog" aria-controls="auth-dialog">
                <span>Entrar</span>
                <svg aria-hidden="true" viewBox="0 0 24 24" class="login-trigger-icon">
                    <path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 4a3.25 3.25 0 1 1 0 6.5A3.25 3.25 0 0 1 12 6Zm0 12.5a8.45 8.45 0 0 1-5.8-2.3 5.9 5.9 0 0 1 11.6 0 8.45 8.45 0 0 1-5.8 2.3Z"/>
                </svg>
            </button>
        <?php endif; ?>
    </div>
</header>

<?php if (!isset($_SESSION['user_id'])): ?>
<div class="auth-overlay" data-auth-overlay hidden>
    <section class="auth-dialog" id="auth-dialog" role="dialog" aria-modal="true" aria-labelledby="auth-title" tabindex="-1">
        <button class="auth-close" type="button" data-auth-close aria-label="Fechar">×</button>
        <div class="auth-brand" aria-label="Elarion">ELARION</div>

        <form class="auth-form" data-auth-panel="login" action="./login/login.php" method="post">
            <h2 id="auth-title">Iniciar sessão</h2>
            <label class="sr-only" for="login-identity">Usuário ou e-mail</label>
            <input id="login-identity" name="identificador" type="text" autocomplete="username" placeholder="Usuário/E-mail" required>
            <label class="sr-only" for="login-password">Senha</label>
            <div class="auth-password">
                <input id="login-password" name="senha" type="password" autocomplete="current-password" placeholder="Senha" required>
                <button class="password-toggle" type="button" data-password-toggle="login-password" aria-label="Mostrar senha">◉</button>
            </div>
            <button class="auth-submit" type="submit">Login</button>
            <div class="auth-links">
                <button type="button" class="auth-link" data-auth-show="reset">Redefinir senha</button>
                <button type="button" class="auth-link" data-auth-show="register">Registre-se agora</button>
            </div>
        </form>

        <form class="auth-form" data-auth-panel="register" action="./login/cadastro.php" method="post" hidden>
            <h2 id="register-title">Criar conta</h2>
            <label class="sr-only" for="register-email">E-mail</label>
            <input id="register-email" name="email" type="email" autocomplete="email" placeholder="E-mail" required>
            <label class="sr-only" for="register-username">Usuário</label>
            <input id="register-username" name="usuario" type="text" autocomplete="username" placeholder="Usuário" required>
            <label class="sr-only" for="register-password">Senha</label>
            <input id="register-password" name="senha" type="password" autocomplete="new-password" placeholder="Senha" required>
            <label class="sr-only" for="register-password-confirm">Confirmar senha</label>
            <input id="register-password-confirm" name="confirmar_senha" type="password" autocomplete="new-password" placeholder="Confirmar senha" required>
            <label class="auth-terms">
                <input type="checkbox" name="aceite_termos" required>
                <span>Li e concordo com os <a href="./termos/termos.php" target="_blank" rel="noopener">Termos de Uso</a> e a <a href="./termos/politica.php" target="_blank" rel="noopener">Política de Privacidade</a>.</span>
            </label>
            <button class="auth-submit" type="submit">Criar conta</button>
            <div class="auth-links auth-links-single">
                <button type="button" class="auth-link" data-auth-show="login">Já tenho uma conta</button>
            </div>
        </form>

        <form class="auth-form" data-auth-panel="reset" action="./login/login.php?acao=recuperar" method="post" hidden>
            <h2 id="reset-title">Redefinir senha</h2>
            <p class="auth-description">Informe seu e-mail para continuar a recuperação da conta.</p>
            <label class="sr-only" for="reset-email">E-mail</label>
            <input id="reset-email" name="email" type="email" autocomplete="email" placeholder="E-mail" required>
            <button class="auth-submit" type="submit">Enviar instruções</button>
            <div class="auth-links auth-links-single">
                <button type="button" class="auth-link" data-auth-show="login">Voltar para login</button>
            </div>
        </form>
    </section>
</div>
<?php endif; ?>