-- Drop tables if they exist (para evitar conflitos)
DROP TABLE IF EXISTS personagem_area;
DROP TABLE IF EXISTS personagem_missao;
DROP TABLE IF EXISTS personagem_skin;
DROP TABLE IF EXISTS missao;
DROP TABLE IF EXISTS area;
DROP TABLE IF EXISTS skin;
DROP TABLE IF EXISTS personagem;

-- 1. Primeiro, tabelas sem dependências (não têm FK para outras tabelas)
CREATE TABLE personagem (
    idPersonagem INT(11) PRIMARY KEY,
    idUsuario INT(11),
    nome VARCHAR(100),
    nivel INT(11),
    xp INT(11),
    ouro INT(11),
    vida INT(11),
    mana INT(11),
    localAtual VARCHAR(100)
);

CREATE TABLE skin (
    idSkin INT(11) PRIMARY KEY,
    nome VARCHAR(100),
    requisito_moedas INT(11),
    requisito_missao_requisito_m VARCHAR(100),
    nivel_visual INT(11)
);

CREATE TABLE area (
    idArea INT(11) PRIMARY KEY,
    nome VARCHAR(100),
    temaFinanceiro VARCHAR(100)
);

-- 2. Depois, tabelas que dependem das anteriores (com FK)
CREATE TABLE missao (
    idMissao INT(11) PRIMARY KEY,
    idArea INT(11),
    titulo VARCHAR(100),
    recompensa_ouro INT(11),
    recompensa_xp INT(11),
    FOREIGN KEY (idArea) REFERENCES area(idArea)
);

CREATE TABLE personagem_skin (
    idPersonagem INT(11),
    idSkin INT(11),
    PRIMARY KEY (idPersonagem, idSkin),
    FOREIGN KEY (idPersonagem) REFERENCES personagem(idPersonagem),
    FOREIGN KEY (idSkin) REFERENCES skin(idSkin)
);

CREATE TABLE personagem_missao (
    idPersonagem INT(11),
    idMissao INT(11),
    status VARCHAR(50),
    dataConclusao TIMESTAMP,
    PRIMARY KEY (idPersonagem, idMissao),
    FOREIGN KEY (idPersonagem) REFERENCES personagem(idPersonagem),
    FOREIGN KEY (idMissao) REFERENCES missao(idMissao)
);

CREATE TABLE personagem_area (
    idPersonagem INT(11),
    idArea INT(11),
    desbloqueado BOOLEAN,
    PRIMARY KEY (idPersonagem, idArea),
    FOREIGN KEY (idPersonagem) REFERENCES personagem(idPersonagem),
    FOREIGN KEY (idArea) REFERENCES area(idArea)
);

-- Inserir dados de exemplo
INSERT INTO area (idArea, nome, temaFinanceiro) VALUES
(1, 'Floresta Inicial', 'Economia Básica'),
(2, 'Caverna do Tesouro', 'Investimentos'),
(3, 'Vila dos Mercadores', 'Comércio');

INSERT INTO personagem (idPersonagem, idUsuario, nome, nivel, xp, ouro, vida, mana, localAtual) VALUES
(1, 1, 'Aventureiro01', 5, 1200, 500, 100, 50, 'Floresta Inicial');

INSERT INTO missao (idMissao, idArea, titulo, recompensa_ouro, recompensa_xp) VALUES
(1, 1, 'Coletar Moedas', 100, 50),
(2, 2, 'Derrotar o Dragão', 500, 200);

INSERT INTO skin (idSkin, nome, requisito_moedas, requisito_missao_requisito_m, nivel_visual) VALUES
(1, 'Armadura de Bronze', 100, 'Missão 1', 1),
(2, 'Armadura de Prata', 500, 'Missão 2', 2);

INSERT INTO personagem_skin (idPersonagem, idSkin) VALUES (1, 1);
INSERT INTO personagem_missao (idPersonagem, idMissao, status, dataConclusao) VALUES 
(1, 1, 'Concluída', CURRENT_TIMESTAMP);
INSERT INTO personagem_area (idPersonagem, idArea, desbloqueado) VALUES 
(1, 1, TRUE),
(1, 2, FALSE);