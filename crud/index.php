<?php
include 'conexao.php';

// Buscar dados de exemplo
$sql = "SELECT p.nome AS personagem, p.nivel, p.ouro, p.xp, p.vida, p.mana, a.nome AS area_atual 
        FROM personagem p 
        JOIN area a ON p.localAtual = a.nome";
$result = $conn->query($sql);

// Buscar total de personagens
$sql_total = "SELECT COUNT(*) as total FROM personagem";
$total_result = $conn->query($sql_total);
$total = $total_result->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELARION - Sistema de Aventura</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <h1>ELARION</h1>
            <div class="subtitle">O Reino dos Aventureiros</div>
            <div class="info-bar">
                <div class="info-item">Herois: <span><?php echo $total; ?></span></div>
                <div class="info-item">Reino: <span>Elarion</span></div>
                <div class="info-item">Era: <span>Terceira Idade</span></div>
            </div>
        </div>

        <!-- SEÇÃO PERSONAGENS -->
        <div class="section">
            <div class="section-title">
                Herois de Elarion
                <span class="badge"><?php echo $total; ?> aventureiros</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Nivel</th>
                            <th>XP</th>
                            <th>Ouro</th>
                            <th>Vida</th>
                            <th>Mana</th>
                            <th>Local</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><strong style="color: #D4A843;"><?php echo $row['personagem']; ?></strong></td>
                                <td>
                                    <span style="color: #FFD700;">Nv. <?php echo $row['nivel']; ?></span>
                                </td>
                                <td>
                                    <div class="stat-bar">
                                        <span style="font-size:0.8em;"><?php echo $row['xp']; ?></span>
                                        <div class="bar-bg">
                                            <div class="bar-fill xp" style="width: <?php echo min(($row['xp'] / 1000) * 100, 100); ?>%;"></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="color: #FFD700;"><?php echo $row['ouro']; ?></td>
                                <td>
                                    <div class="stat-bar">
                                        <span style="font-size:0.8em;"><?php echo $row['vida']; ?></span>
                                        <div class="bar-bg">
                                            <div class="bar-fill health" style="width: <?php echo $row['vida']; ?>%;"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="stat-bar">
                                        <span style="font-size:0.8em;"><?php echo $row['mana']; ?></span>
                                        <div class="bar-bg">
                                            <div class="bar-fill mana" style="width: <?php echo $row['mana']; ?>%;"></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="color: #C4A882;"><?php echo $row['area_atual']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #8B7355; padding: 30px;">
                                    Nenhum heroi encontrado nas terras de Elarion
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SEÇÃO MISSÕES -->
        <div class="section">
            <div class="section-title">
                Missoes Ativas
                <span class="badge">Aventuras</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Titulo</th>
                            <th>Area</th>
                            <th>Status</th>
                            <th>Data Conclusao</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql_missoes = "SELECT m.titulo, pm.status, pm.dataConclusao, a.nome AS area
                                        FROM personagem_missao pm
                                        JOIN missao m ON pm.idMissao = m.idMissao
                                        JOIN area a ON m.idArea = a.idArea
                                        WHERE pm.idPersonagem = 1";
                        $result_missoes = $conn->query($sql_missoes);
                        ?>
                        <?php if ($result_missoes && $result_missoes->num_rows > 0): ?>
                            <?php while($row = $result_missoes->fetch_assoc()): ?>
                            <tr>
                                <td style="color: #D4A843;"><?php echo $row['titulo']; ?></td>
                                <td style="color: #C4A882;"><?php echo $row['area']; ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower(str_replace(' ', '_', $row['status'])); ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td style="color: #8B7355; font-size:0.85em;">
                                    <?php echo $row['dataConclusao'] ? date('d/m/Y H:i', strtotime($row['dataConclusao'])) : '—'; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #8B7355; padding: 30px;">
                                    Nenhuma missao em andamento
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SEÇÃO SKINS -->
        <div class="section">
            <div class="section-title">
                Skins Desbloqueadas
                <span class="badge">Colecao</span>
            </div>
            <div class="skins-grid">
                <?php
                $sql_skins = "SELECT s.nome, s.nivel_visual, s.requisito_moedas
                               FROM personagem_skin ps
                               JOIN skin s ON ps.idSkin = s.idSkin
                               WHERE ps.idPersonagem = 1";
                $result_skins = $conn->query($sql_skins);
                ?>
                <?php if ($result_skins && $result_skins->num_rows > 0): ?>
                    <?php 
                    $icons = ['⚔', '🛡', '🗡', '🏹', '🔮', '💎', '👑', '⭐'];
                    $i = 0;
                    while($row = $result_skins->fetch_assoc()): 
                    ?>
                    <div class="skin-card">
                        <div class="skin-icon"><?php echo $icons[$i % count($icons)]; ?></div>
                        <div class="skin-name"><?php echo $row['nome']; ?></div>
                        <div class="skin-detail">Nivel Visual: <?php echo $row['nivel_visual']; ?></div>
                        <div class="skin-detail"><?php echo $row['requisito_moedas']; ?> moedas</div>
                    </div>
                    <?php 
                    $i++; 
                    endwhile; 
                    ?>
                <?php else: ?>
                    <div style="grid-column: 1/-1; text-align: center; color: #8B7355; padding: 30px;">
                        Nenhuma skin desbloqueada
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <div>ELARION — O Reino dos Aventureiros</div>
            <div class="credits">
                Alunos: <span>Ana</span> · <span>Evandro</span> · <span>Juliana</span> · <span>Luiza</span>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>