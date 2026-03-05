<?php
require_once '../includes/conexao.php';
verificarLogin();

if ($_SESSION['user_tipo'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

$titulo_pagina = 'Estatísticas de Desempenho';
$pagina_atual = 'estatisticas';

// 1. Ranking de Turmas (Média Geral por Turma)
$sql_ranking = "SELECT t.nome as turma_nome, AVG(n.nota) as media_turma, 
                COUNT(DISTINCT n.aluno_id) as total_alunos_avaliados
                FROM notas n
                JOIN turmas t ON n.turma_id = t.id
                GROUP BY t.id
                ORDER BY media_turma DESC";
$ranking = $conn->query($sql_ranking);

// 2. Disciplinas com Melhores/Piores Notas
$sql_disciplinas = "SELECT d.nome, AVG(n.nota) as media_disc
                    FROM notas n
                    JOIN disciplinas d ON n.disciplina_id = d.id
                    GROUP BY d.id
                    ORDER BY media_disc DESC";
$estat_disc = $conn->query($sql_disciplinas);

require_once '../includes/cabecalho.php';
?>

<div style="margin-bottom: 25px;">
    <h2 style="font-family:'Playfair Display',serif; color:var(--p); margin:0;">Análise de Desempenho Escolar</h2>
    <p style="color:#64748b; font-size:.9rem">Visão geral do aproveitamento do Colégio Visão do Futuro</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
    
    <!-- Ranking de Turmas -->
    <div class="card">
        <div class="card-header" style="background: var(--p); color:white; padding:15px; border-radius:8px 8px 0 0;">
            <h3 style="margin:0; font-size:1.1rem;"><i class="fas fa-trophy" style="color:var(--s)"></i> Ranking de Turmas</h3>
        </div>
        <div class="card-body" style="padding:0;">
            <table class="tabela">
                <thead>
                    <tr>
                        <th style="text-align:left">Turma</th>
                        <th>Alunos</th>
                        <th>Média Geral</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $posicao = 1;
                    while($r = $ranking->fetch_assoc()): 
                        $cor_pos = ($posicao == 1) ? '#e8a020' : '#1a3a5c';
                    ?>
                    <tr>
                        <td style="text-align:left">
                            <span style="background:<?= $cor_pos ?>; color:white; width:25px; height:25px; display:inline-flex; align-items:center; justify-content:center; border-radius:50%; font-size:.8rem; margin-right:10px;">
                                <?= $posicao++ ?>º
                            </span>
                            <strong><?= $r['turma_nome'] ?></strong>
                        </td>
                        <td><?= $r['total_alunos_avaliados'] ?></td>
                        <td style="font-weight:bold; font-size:1.1rem; color:<?= $r['media_turma'] >= 10 ? '#059669' : '#dc2626' ?>">
                            <?= number_format($r['media_turma'], 1) ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Desempenho por Disciplina -->
    <div class="card">
        <div class="card-header" style="background: var(--p); color:white; padding:15px; border-radius:8px 8px 0 0;">
            <h3 style="margin:0; font-size:1.1rem;"><i class="fas fa-book"></i> Médias por Disciplina</h3>
        </div>
        <div class="card-body">
            <?php while($d = $estat_disc->fetch_assoc()): 
                $percentual = ($d['media_disc'] / 20) * 100; // Assumindo escala 0-20
            ?>
                <div style="margin-bottom:15px;">
                    <div style="display:flex; justify-content:space-between; font-size:.85rem; margin-bottom:5px;">
                        <span><?= $d['nome'] ?></span>
                        <strong><?= number_format($d['media_disc'], 1) ?></strong>
                    </div>
                    <div style="background:#e2e8f0; height:8px; border-radius:4px; overflow:hidden;">
                        <div style="background:<?= $d['media_disc'] >= 10 ? '#1a3a5c' : '#dc2626' ?>; width:<?= $percentual ?>%; height:100%;"></div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

</div>

<?php require_once '../includes/rodape.php'; ?>
