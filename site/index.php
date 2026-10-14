<?php
    require_once './includes/head.php';
    $flashMessages = getFlash();
?>
<body>
    <?php require_once './includes/header.php'; ?>
    <main>
        <?php if ($flashMessages): ?>
            <div class="site-flash-messages" aria-live="polite">
                <?php foreach ($flashMessages as $flash): ?>
                    <div class="notice notice-<?= htmlspecialchars($flash['type']) ?>" role="<?= $flash['type'] === 'error' ? 'alert' : 'status' ?>"><?= htmlspecialchars($flash['message']) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <section class="hero" id="inicio" aria-labelledby="hero-title">
            <div class="hero-shade"></div>
            <div class="hero-content">
                <p class="eyebrow">Uma nova aventura</p>
                <h1 id="hero-title"><img class="hero-logo" src="./assents/img/logo-titulo.png" alt="Logo Elarion"></h1>
                <p class="hero-description">Quer embarcar em uma jornada única e descobrir um mundo cheio de possibilidades?</p>
                <div class="hero-actions">
                    <a class="button button-primary" href="#mundo">Sobre o Mundo</a>
                    <a class="button button-outline" href="#sobre">Sobre o Projeto</a>
                </div>
            </div>
            <a class="scroll-cue" href="#mundo" aria-label="Rolar para a próxima seção"><span></span></a>
            <span class="hero-caption">Jogo em Desenvolvimento</span>
        </section>
        <section class="content-section world-section" id="mundo" aria-labelledby="world-title">
            <div class="section-copy">
                <p class="eyebrow">Categoria ou chamada da seção</p>
                <h2 id="world-title">Título sobre o mundo</h2>
                <p>Descreva aqui o mundo, a história ou o tema do projeto.</p>
                <a class="text-link" href="#recursos">Texto do link para outra seção <span aria-hidden="true">→</span></a>
            </div>
            <div class="world-image" role="img" aria-label="Descreva a imagem desta seção"></div>
        </section>

        <section class="content-section features-section" id="recursos" aria-labelledby="features-title">
            <div class="section-heading">
                <p class="eyebrow"></p>
                <h2 id="features-title">Título dos destaques</h2>
                <p>Apresente aqui os assuntos ou recursos que deseja destacar.</p>
            </div>
            <div class="feature-grid">
                <article class="feature-card">
                    <span class="feature-number">01</span>
                    <h3>Título do destaque 1</h3>
                    <p>Descreva aqui o primeiro destaque.</p>
                </article>
                <article class="feature-card">
                    <span class="feature-number">02</span>
                    <h3>Título do destaque 2</h3>
                    <p>Descreva aqui o segundo destaque.</p>
                </article>
                <article class="feature-card">
                    <span class="feature-number">03</span>
                    <h3>Título do destaque 3</h3>
                    <p>Descreva aqui o terceiro destaque.</p>
                </article>
            </div>
        </section>

        <section class="news-section" id="novidades" aria-labelledby="news-title">
            <div class="news-content">
                <p class="eyebrow">Categoria ou chamada da seção</p>
                <h2 id="news-title">Título das novidades</h2>
                <p>Escreva aqui uma notícia, atualização ou informação importante.</p>
                <a class="button button-outline" href="#sobre">Texto do botão ou link</a>
            </div>
        </section>

        <section class="about-section" id="sobre" aria-labelledby="about-title">
            <p class="eyebrow">Categoria ou chamada da seção</p>
            <h2 id="about-title">Título sobre o projeto</h2>
            <p>Conte aqui sobre o projeto, seus objetivos ou quem está por trás dele.</p>
            <a class="button button-primary" href="#inicio">Texto do botão para voltar ao início</a>
        </section>
    </main>
    <footer class="site-footer">
        <a class="site-logo" href="#inicio">ELARION</a>
        <p>Um novo mundo está por vir.</p>
        <div class="footer-links">
            <a href="./termos/termos.php">Termos de Uso</a>
            <a href="./termos/politica.php">Política de Privacidade</a>
        </div>
    </footer>
</body>