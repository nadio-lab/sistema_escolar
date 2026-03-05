<?php
require_once '../includes/conexao.php';
verificarLogin();

$aluno_id = $_SESSION['aluno_id'] ?? (int)$_GET['id'];
$cfg = allCfg();
$logo = $cfg['escola_logo'] ?? '';
// Busca dados detalhados do aluno
$sql = "SELECT a.*, t.nome as turma_nome, t.ano_letivo 
        FROM alunos a 
        LEFT JOIN turmas t ON a.turma_id = t.id 
        WHERE a.id = $aluno_id LIMIT 1";
$res = $conn->query($sql);
$al = $res->fetch_assoc();

if (!$al) die("Aluno não encontrado.");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Cartão de Estudante - <?= $al['nome_completo'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com">
        <?php if ($logo): ?>
<link rel="icon" href="/betilson/sistema_escolar/<?= htmlspecialchars($logo) ?>">
<?php endif; ?>
 
    <style>
        body { background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; font-family: 'Segoe UI', sans-serif; }
        .cartao { width: 350px; height: 520px; background: white; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); overflow: hidden; position: relative; border: 1px solid #ddd; }
        .topo-cartao { height: 120px; background: #1a3a5c; color: white; text-align: center; padding: 20px; box-sizing: border-box; }
        .logo-escola { font-size: 0.8rem; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; }
        .foto-container { width: 140px; height: 140px; background: #eee; border: 5px solid white; border-radius: 50%; margin: -70px auto 0; overflow: hidden; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .foto-container img { width: 100%; height: 100%; object-fit: cover; }
        .info-aluno { text-align: center; padding: 20px; }
        .nome { font-size: 1.3rem; color: #1a3a5c; font-weight: bold; margin-bottom: 5px; }
        .detalhes { font-size: 0.9rem; color: #64748b; margin-bottom: 20px; }
        .validade { background: #fef3c7; color: #92400e; padding: 5px 15px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; display: inline-block; }
        .footer-cartao { position: absolute; bottom: 0; width: 100%; height: 60px; background: #e8a020; display: flex; justify-content: center; align-items: center; color: #1a3a5c; font-weight: bold; }
        .qr-code { margin-top: 15px; opacity: 0.8; }
        @media print { .btn-print { display: none; } body { background: white; } }
    </style>
</head>
<body>

<div style="position: fixed; top: 20px; text-align:center; width:100%;">
    <button class="btn-print" onclick="window.print()" style="padding: 10px 20px; background: #1a3a5c; color: white; border: none; border-radius: 5px; cursor: pointer;">
        <i class="fas fa-print"></i> Imprimir Cartão
    </button>
</div>

<div class="cartao">
    <div class="topo-cartao">
        <div class="logo-escola"><?= $cfg['escola_nome'] ?></div>
        <small>CARTÃO DO ESTUDANTE</small>
    </div>
    
    <div class="foto-container">
        <?php if ($al['foto']): ?>
            <img src="../<?= $al['foto'] ?>" alt="Foto Aluno">
        <?php else: ?>
            <img src="https://via.placeholder.com" alt="Sem Foto">
        <?php endif; ?>
    </div>

    <div class="info-aluno">
        <div class="nome"><?= $al['nome_completo'] ?></div>
        <div class="detalhes">
            ID: <strong>#<?= str_pad($al['id'], 5, '0', STR_PAD_LEFT) ?></strong><br>
            Turma: <strong><?= $al['turma_nome'] ?></strong><br>
            Gênero: <?= $al['genero'] == 'M' ? 'Masculino' : 'Feminino' ?>
        </div>

        <div class="validade">ANO LETIVO: <?= $al['ano_letivo'] ?></div>
        
        <div class="qr-code">
            <i class="fas fa-qrcode fa-4x"></i><br>
            <small style="font-size: 0.6rem;">VALIDAÇÃO DIGITAL</small>
        </div>
    </div>

    <div class="footer-cartao">
        ESTUDANTE ATIVO
    </div>
</div>

</body>
</html>
