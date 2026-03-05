<?php
require_once '../includes/conexao.php';
verificarLogin();

$id = (int)($_GET['id'] ?? 0);

// Consulta os dados da transação, aluno e turma
$sql = "SELECT t.*, a.nome_completo, a.responsavel_nome, tur.nome as turma_nome 
        FROM fin_transacoes t 
        JOIN alunos a ON t.aluno_id = a.id 
        LEFT JOIN turmas tur ON a.turma_id = tur.id 
        WHERE t.id = $id LIMIT 1";

$res = $conn->query($sql);
if (!$res || $res->num_rows === 0) die("Recibo não encontrado.");
$r = $res->fetch_assoc();

$cfg = allCfg();
$logo = $cfg['escola_logo'] ?? '';
// Pega o ID do aluno da sessão (garante que ele só veja os próprios recibos)
$aluno_sessao = $_SESSION['aluno_id'] ?? 0;

// Consulta os dados validando o dono da transação
$sql = "SELECT t.*, a.nome_completo, a.responsavel_nome, tur.nome as turma_nome 
        FROM fin_transacoes t 
        JOIN alunos a ON t.aluno_id = a.id 
        LEFT JOIN turmas tur ON a.turma_id = tur.id 
        WHERE t.id = $id AND t.aluno_id = $aluno_sessao LIMIT 1";

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Recibo #<?= $id ?></title>
    <?php if ($logo): ?>
<link rel="icon" href="/betilson/sistema_escolar/<?= htmlspecialchars($logo) ?>">
<?php endif; ?>
 
    <style>
        .topo { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #1a3a5c; padding-bottom: 15px; margin-bottom: 20px; }
.logo-recibo img { width: 100px; height: 100px; object-fit: contain; margin-right: 20px; }
.escola-container { display: flex; align-items: center; }
.escola-info h1 { margin: 0; font-size: 1.3rem; color: #1a3a5c; text-transform: uppercase; }

        body { font-family: 'Courier New', Courier, monospace; color: #333; padding: 20px; background: #f5f5f5; }
        .recibo { background: #fff; max-width: 800px; margin: 0 auto; padding: 30px; border: 1px solid #ddd; box-shadow: 0 0 10px rgba(0,0,0,0.1); position: relative; }
        .topo { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
        .escola-info h1 { margin: 0; font-size: 1.5rem; text-transform: uppercase; }
        .n-recibo { text-align: right; }
        .n-recibo div { font-size: 1.2rem; font-weight: bold; color: #d32f2f; }
        .corpo { line-height: 1.8; font-size: 1.1rem; }
        .valor-extenso { font-weight: bold; text-decoration: underline; }
        .footer { margin-top: 50px; display: flex; justify-content: space-between; text-align: center; }
        .assinatura { border-top: 1px solid #333; width: 250px; padding-top: 5px; }
        @media print { 
            body { background: none; padding: 0; }
            .recibo { border: none; box-shadow: none; }
            .btn-print { display: none; }
        }
        .btn-print { background: #1a3a5c; color: #fff; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div style="text-align: center;">
        <button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Imprimir Recibo</button>
    </div>

    <div class="recibo">
      <div class="topo">
    <div class="escola-container">
        <!-- Logo filtrado da Base de Dados -->
        <div class="logo-recibo">
            <?php if (!empty($cfg['escola_logo'])): ?>
                <img src="/betilson/sistema_escolar/<?= htmlspecialchars($cfg['escola_logo']) ?>" alt="Logo">
            <?php else: ?>
                <i class="fas fa-graduation-cap" style="font-size: 50px; color: #1a3a5c;"></i>
            <?php endif; ?>
        </div>
        
        <div class="escola-info">
            <h1><?= htmlspecialchars($cfg['escola_nome'] ?? 'Colégio Visão do Futuro') ?></h1>
            <p style="margin: 5px 0 0 0; font-size: 0.9rem;">
                <?= htmlspecialchars($cfg['escola_morada'] ?? 'Luanda, Angola') ?><br>
                Tel: <?= htmlspecialchars($cfg['escola_telefone'] ?? '+244 900 000 000') ?>
            </p>
        </div>
    </div>

    <div class="n-recibo">
        <small>RECIBO DE PAGAMENTO</small>
        <div style="color: #1a3a5c;">Nº <?= str_pad($r['id'], 6, '0', STR_PAD_LEFT) ?></div>
        <small><?= date('d/m/Y H:i', strtotime($r['criado_em'])) ?></small>
    </div>
</div>


        <div class="corpo">
            <p>Recebemos de <strong><?= htmlspecialchars($r['responsavel_nome'] ?: $r['nome_completo']) ?></strong>, 
            a quantia de <span class="valor-extenso">Kz <?= number_format($r['valor'], 2, ',', '.') ?></span> 
            referente a: <strong><?= htmlspecialchars($r['descricao']) ?></strong>.</p>
            
            <p>Aluno: <strong><?= htmlspecialchars($r['nome_completo']) ?></strong> | 
            Turma: <strong><?= htmlspecialchars($r['turma_nome'] ?? '—') ?></strong></p>
        </div>

        <div class="footer">
            <div>
                <p>Data: <?= date('d/m/Y') ?></p>
            </div>
            <div class="assinatura">
                <small>O Responsável pela Tesouraria</small>
            </div>
        </div>
        
        <div style="margin-top: 30px; border-top: 1px dashed #ccc; padding-top: 10px; font-size: 0.8rem; color: #777; text-align: center;">
            Este documento serve de comprovativo de pagamento. Conserve-o.
        </div>
    </div>
</body>
</html>
