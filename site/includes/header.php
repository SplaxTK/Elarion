<?php
$currentUser = currentUser();
$userUsername = $currentUser['username'] ?? '';
$logoutToken = csrfToken();
$authFormState = $_SESSION['auth_form'] ?? [];
$authFormValues = is_array($authFormState) && is_array($authFormState['values'] ?? null) ? $authFormState['values'] : [];
$authFormErrors = is_array($authFormState) && is_array($authFormState['errors'] ?? null) ? $authFormState['errors'] : [];
$loginFormValues = ($authFormState['panel'] ?? '') === 'login' ? $authFormValues : [];
$registerFormValues = ($authFormState['panel'] ?? '') === 'register' ? $authFormValues : [];
$authOpenPanel = $_SESSION['auth_open_panel'] ?? null;
unset($_SESSION['auth_open_panel'], $_SESSION['auth_form']);
$homeUrl = relativeSiteUrl('index.php');
$isHomePage = basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')) === 'index.php';
$homeLink = static fn (string $section): string => $isHomePage ? '#' . $section : $homeUrl . '#' . $section;
?>
<header class="topbar">
    <div class="topbar-branding">
        <a class="site-logo" href="<?= htmlspecialchars($homeLink('inicio'), ENT_QUOTES, 'UTF-8') ?>" aria-label="Elarion, início">
            <img class="site-logo-icon" src="<?= htmlspecialchars(relativeSiteUrl('assents/img/logo-elarion.png'), ENT_QUOTES, 'UTF-8') ?>" alt="">
            <span>ELARION</span>
        </a>
    </div>
    <nav class="site-navigation" aria-label="Navegação principal">
        <a href="<?= htmlspecialchars($homeLink('inicio'), ENT_QUOTES, 'UTF-8') ?>">Início</a>
        <a href="<?= htmlspecialchars($homeLink('mundo'), ENT_QUOTES, 'UTF-8') ?>">O mundo</a>
        <a href="<?= htmlspecialchars($homeLink('recursos'), ENT_QUOTES, 'UTF-8') ?>">Recursos</a>
        <a href="<?= htmlspecialchars($homeLink('novidades'), ENT_QUOTES, 'UTF-8') ?>">Novidades</a>
        <a href="<?= htmlspecialchars($homeLink('sobre'), ENT_QUOTES, 'UTF-8') ?>">Sobre</a>
    </nav>
    <div class="topbar-actions">
        <?php if ($currentUser): ?>
            <div class="user-menu-wrapper">
                <button class="user-menu-button" type="button" aria-haspopup="menu" aria-expanded="false" data-user-menu-button>
                    <span class="user-menu-label"><?= htmlspecialchars($userUsername) ?></span>
                    <span class="user-menu-avatar" aria-hidden="true">
                        <img src="<?= htmlspecialchars(relativeSiteUrl('assents/img/user-icon.png')) ?>" alt="" width="34" height="34">
                    </span>
                    <span class="user-menu-caret" aria-hidden="true">▾</span>
                </button>
                <div class="user-menu" data-user-menu hidden role="menu">
                    <p class="user-menu-name"><?= htmlspecialchars($userUsername) ?></p>
                    <span class="user-menu-role"><?= htmlspecialchars($currentUser['role']) ?></span>
                    <a href="<?= htmlspecialchars(relativeSiteUrl('profile.php')) ?>" role="menuitem">Meu perfil</a>
                    <a href="<?= htmlspecialchars(relativeSiteUrl('configuracoes.php')) ?>" role="menuitem">Configurações da conta</a>
                    <?php if (isAdmin()): ?>
                        <a href="<?= htmlspecialchars(relativeSiteUrl('crud/index.php')) ?>" rel="noopener noreferrer" role="menuitem">Painel de admin</a>
                    <?php endif; ?>
                    <button type="button" class="user-menu-logout" data-logout-open role="menuitem">Sair da conta</button>
                </div>
            </div>
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

<?php if (!$currentUser): ?>
<?php if (in_array($authOpenPanel, ['login', 'register'], true)): ?>
<script>
    window.ELARION_AUTH_OPEN = <?= json_encode($authOpenPanel, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<?php endif; ?>
<div class="auth-overlay" data-auth-overlay hidden>
    <section class="auth-dialog" id="auth-dialog" role="dialog" aria-modal="true" aria-labelledby="auth-title" tabindex="-1">
        <button class="auth-close" type="button" data-auth-close aria-label="Fechar">×</button>
        <div class="auth-brand" aria-label="Elarion">ELARION</div>

        <form class="auth-form" data-auth-panel="login" action="<?= htmlspecialchars(relativeSiteUrl('login/login.php')) ?>" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <h2 id="auth-title">Iniciar sessão</h2>
            <?php if (($authFormState['panel'] ?? '') === 'login' && isset($authFormErrors['_form'])): ?>
                <p class="auth-field-error" role="alert"><?= htmlspecialchars($authFormErrors['_form']) ?></p>
            <?php endif; ?>
            <label class="sr-only" for="login-identity">Usuário ou e-mail</label>
            <input id="login-identity" name="identificador" type="text" autocomplete="username" placeholder="Usuário/E-mail" value="<?= htmlspecialchars($loginFormValues['identificador'] ?? '') ?>" required>
            <?php if (($authFormState['panel'] ?? '') === 'login' && isset($authFormErrors['identificador'])): ?>
                <p class="auth-field-error" role="alert"><?= htmlspecialchars($authFormErrors['identificador']) ?></p>
            <?php endif; ?>
            <label class="sr-only" for="login-password">Senha</label>
            <div class="auth-password">
                <input id="login-password" name="senha" type="password" autocomplete="current-password" placeholder="Senha" required>
                <button class="password-toggle" type="button" data-password-toggle="login-password" aria-label="Mostrar senha">◉</button>
            </div>
            <?php if (($authFormState['panel'] ?? '') === 'login' && isset($authFormErrors['senha'])): ?>
                <p class="auth-field-error" role="alert"><?= htmlspecialchars($authFormErrors['senha']) ?></p>
            <?php endif; ?>
            <button class="auth-submit" type="submit">Login</button>
            <div class="auth-links">
                <button type="button" class="auth-link" data-auth-show="reset">Redefinir senha</button>
                <button type="button" class="auth-link" data-auth-show="register">Registre-se agora</button>
            </div>
        </form>

        <form class="auth-form" data-auth-panel="register" action="<?= htmlspecialchars(relativeSiteUrl('login/cadastro.php')) ?>" method="post" hidden>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <h2 id="register-title">Criar conta</h2>
            <?php if (($authFormState['panel'] ?? '') === 'register' && isset($authFormErrors['_form'])): ?>
                <p class="auth-field-error" role="alert"><?= htmlspecialchars($authFormErrors['_form']) ?></p>
            <?php endif; ?>
            <label class="sr-only" for="register-email">E-mail</label>
            <input id="register-email" name="email" type="email" autocomplete="email" placeholder="E-mail" value="<?= htmlspecialchars($registerFormValues['email'] ?? '') ?>" required>
            <?php if (($authFormState['panel'] ?? '') === 'register' && isset($authFormErrors['email'])): ?>
                <p class="auth-field-error" role="alert"><?= htmlspecialchars($authFormErrors['email']) ?></p>
            <?php endif; ?>
            <label class="sr-only" for="register-username">Usuário</label>
            <input id="register-username" name="usuario" type="text" autocomplete="username" placeholder="Usuário" value="<?= htmlspecialchars($registerFormValues['usuario'] ?? '') ?>" required>
            <?php if (($authFormState['panel'] ?? '') === 'register' && isset($authFormErrors['usuario'])): ?>
                <p class="auth-field-error" role="alert"><?= htmlspecialchars($authFormErrors['usuario']) ?></p>
            <?php endif; ?>
            <label class="sr-only" for="register-password">Senha</label>
            <input id="register-password" name="senha" type="password" autocomplete="new-password" placeholder="Senha" required>
            <?php if (($authFormState['panel'] ?? '') === 'register' && isset($authFormErrors['senha'])): ?>
                <p class="auth-field-error" role="alert"><?= htmlspecialchars($authFormErrors['senha']) ?></p>
            <?php endif; ?>
            <label class="sr-only" for="register-password-confirm">Confirmar senha</label>
            <input id="register-password-confirm" name="confirmar_senha" type="password" autocomplete="new-password" placeholder="Confirmar senha" required>
            <?php if (($authFormState['panel'] ?? '') === 'register' && isset($authFormErrors['confirmar_senha'])): ?>
                <p class="auth-field-error" role="alert"><?= htmlspecialchars($authFormErrors['confirmar_senha']) ?></p>
            <?php endif; ?>
            <label class="auth-terms">
                <input type="checkbox" name="aceite_termos" required>
                <span>Li e concordo com os <a href="<?= htmlspecialchars(relativeSiteUrl('termos/termos.php'), ENT_QUOTES, 'UTF-8') ?>">Termos de Uso</a> e a <a href="<?= htmlspecialchars(relativeSiteUrl('termos/politica.php'), ENT_QUOTES, 'UTF-8') ?>">Política de Privacidade</a>.</span>
            </label>
            <?php if (($authFormState['panel'] ?? '') === 'register' && isset($authFormErrors['aceite_termos'])): ?>
                <p class="auth-field-error" role="alert"><?= htmlspecialchars($authFormErrors['aceite_termos']) ?></p>
            <?php endif; ?>
            <button class="auth-submit" type="submit">Criar conta</button>
            <div class="auth-links auth-links-single">
                <button type="button" class="auth-link" data-auth-show="login">Já tenho uma conta</button>
            </div>
        </form>

        <form class="auth-form" data-auth-panel="reset" action="<?= htmlspecialchars(relativeSiteUrl('login/login.php') . '?acao=recuperar') ?>" method="post" hidden>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
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

<?php if ($currentUser): ?>
<div class="logout-modal" data-logout-dialog hidden>
    <div class="logout-dialog" role="dialog" aria-modal="true" aria-labelledby="logout-title">
        <h2 id="logout-title">Sair da conta</h2>
        <p>Tem certeza de que deseja sair da sua conta?</p>
        <form action="<?= htmlspecialchars(relativeSiteUrl('login/logout.php')) ?>" method="post" class="logout-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($logoutToken) ?>">
            <div class="logout-actions">
                <button type="button" class="button button-outline" data-logout-close>Cancelar</button>
                <button type="submit" class="button button-primary" data-logout-submit>Sair da conta</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>