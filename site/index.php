<?php
    require_once './includes/head.php';
?>
<body>
    <?php require_once './includes/header.php'; ?>
    <main>
        <section class="hero" id="inicio" aria-labelledby="hero-title">
            <div class="hero-shade"></div>
            <div class="hero-content">
                <p class="eyebrow">Frase curta de destaque</p>
                <h1 id="hero-title">Título principal<br>do negoço</h1>
                <p class="hero-description">breve descrição do projeto.</p>
                <div class="hero-actions">
                    <a class="button button-primary" href="#mundo">Texto do botão 1</a>
                    <a class="button button-outline" href="#sobre">Texto do botão 2</a>
                </div>
            </div>
            <a class="scroll-cue" href="#mundo" aria-label="Rolar para a próxima seção"><span></span></a>
            <span class="hero-caption">Frase complementar de destaque</span>
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
                <p class="eyebrow">Categoria ou chamada da seção</p>
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