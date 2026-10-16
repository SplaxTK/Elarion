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
                    <a class="button button-outline" href="#equipe">Equipe</a>
                </div>
            </div>
            <a class="scroll-cue" href="#mundo" aria-label="Rolar para a próxima seção"><span></span></a>
            <span class="hero-caption">Jogo em Desenvolvimento</span>
        </section>
        <section class="content-section world-section" id="mundo" aria-labelledby="world-title">
            <div class="section-copy">
                <p class="eyebrow">Sobre o Mundo</p>
                <h2 id="world-title">O Mundo de Elarion</h2>
                <p>Explore o vasto universo de Elarion, onde mitos e realidade se entrelaçam em uma experiência inesquecível, um mundo repleto de aventuras e descobertas nesse universo medieval e mágico.</p>
                <a class="text-link" href="#recursos">Mais Sobre o Projeto <span aria-hidden="true">→</span></a>
            </div>
            <div class="world-image" role="img" aria-label="Descreva a imagem desta seção"></div>
        </section>

        <section class="content-section features-section" id="recursos" aria-labelledby="features-title">
            <div class="section-heading">
                <p class="eyebrow">Projeto Elarion</p>
                <h2 id="features-title">Características Principais</h2>
                </div>
            <div class="feature-grid">
                <article class="feature-card">
                    <span class="feature-number">01</span>
                    <h3>Engine</h3>
                    <p>Foi desenvolvido com GameMaker Studio 2, uma plataforma de desenvolvimento de jogos com a linguagem de programação GML.</p>
                </article>
                <article class="feature-card">
                    <span class="feature-number">02</span>
                    <h3>Ferramentas</h3>
                    <p>Para a criação da arte e animações, foram utilizadas ferramentas de edição como Cupcut e gerações de imagem por IA.</p>
                </article>
                <article class="feature-card">
                    <span class="feature-number">03</span>
                    <h3>Temática</h3>
                    <p>O jogo aborda temas relacionados às ODS 1 e 4, promovendo conscientização sobre a pobreza e a educação.</p>
                </article>
            </div>
        </section>

        <section class="news-section" id="novidades" aria-labelledby="news-title">
            <div class="news-content">
                <p class="eyebrow">Jogo</p>
                <h2 id="news-title">Novidades sobre o Jogo</h2>
                <p>Versão 1.0 disponível para download!</p>
                <a class="button button-outline" href="index.php" target="_blank">Sobre o Jogo</a>
            </div>
        </section>

        <section class="about-section" id="equipe" aria-labelledby="about-title">
            <p class="eyebrow">Equipe</p>
            <h2 id="about-title">Desenvolvedores</h2>
            <p>Ana Clara Narloch Meiners</p>
            <p>Juliana da Silva Bastos</p>
            <p>Luiza Vittória Cardoso</p>
            <a class="button button-primary" href="#inicio">Voltar ao Início</a>
        </section>
    </main>
    <footer class="site-footer">
        <a class="site-logo" href="#inicio">ELARION</a>
        <p>&copy; 2026 Elarion. Todos os direitos reservados.</p>
        <div class="botoes-redes">
            <a class="insta-link" href="https://www.instagram.com/2infosnc/" target="_blank" rel="noopener noreferrer" aria-label="Instagram do SENAC">
                <img src="<?= htmlspecialchars(relativeSiteUrl('assents\img\Instagram-icon.jfif'), ENT_QUOTES, 'UTF-8') ?>" alt="Instagram">
            </a>
            <a class="senac-link" href="https://portal.sc.senac.br/" target="_blank" rel="noopener noreferrer" aria-label="Portal do SENAC">
                <img src="<?= htmlspecialchars(relativeSiteUrl('assents/img/senac-logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt="">
            </a>
        </div>
        <div class="footer-links">
            <a href="./termos/termos.php">Termos de Uso</a>
            <a href="./termos/politica.php">Política de Privacidade</a>
        </div>
    </footer>
</body>