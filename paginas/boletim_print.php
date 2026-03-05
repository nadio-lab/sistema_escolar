 <?php
require_once '../includes/conexao.php';
verificarLogin();

$aluno_id = (int)($_GET['id'] ?? 0);
if (!$aluno_id) die("ID do aluno não fornecido.");

$cfg = allCfg();
$logo = $cfg['escola_logo'] ?? '';
$nome_escola = $cfg['escola_nome'] ?? 'Colégio Visão do Futuro';

$aluno = $conn->query("SELECT a.*, t.nome as turma_nome FROM alunos a 
                       LEFT JOIN turmas t ON a.turma_id = t.id 
                       WHERE a.id = $aluno_id")->fetch_assoc();

if (!$aluno) die("Aluno não encontrado.");

$sql_notas = "SELECT n.*, d.nome as disc_nome 
              FROM notas n 
              JOIN disciplinas d ON n.disciplina_id = d.id 
              WHERE n.aluno_id = $aluno_id 
              ORDER BY d.nome, n.trimestre";
$res_notas = $conn->query($sql_notas);

$boletim = [];
while($row = $res_notas->fetch_assoc()){
    $boletim[$row['disc_nome']][$row['trimestre']] = $row['nota'];
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Boletim — <?= htmlspecialchars($aluno['nome_completo']) ?></title>
       <?php if ($logo): ?>
<link rel="icon" href="/betilson/sistema_escolar/<?= htmlspecialchars($logo) ?>">
<?php endif; ?>
 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com">
    <style>
        @import url('https://fonts.googleapis.com');
        
        body { font-family: 'DM Sans', sans-serif; color: #333; margin: 0; padding: 0; background: #f4f7f6; }
        .folha { width: 210mm; min-height: 297mm; padding: 15mm; margin: 20px auto; background: white; border: 1px solid #ddd; position: relative; box-sizing: border-box; }
        
        .cabecalho { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #1a3a5c; padding-bottom: 10px; margin-bottom: 25px; }
        .logo img { width: 130px; height: auto; }
        .escola-info { text-align: right; }
        .escola-info h1 { margin: 0; color: #1a3a5c; font-size: 1.5rem; text-transform: uppercase; }
        .escola-info p { margin: 2px 0; font-size: 0.8rem; color: #666; }

        .titulo-doc { text-align: center; text-transform: uppercase; font-weight: bold; margin-bottom: 20px; color: #1a3a5c; letter-spacing: 2px; font-size: 1.2rem; }

        .dados-aluno { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; background: #f8fafc; padding: 12px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #e2e8f0; font-size: 0.85rem; }
        .dados-aluno strong { color: #1a3a5c; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #1a3a5c; color: #fff; padding: 8px; font-size: 0.75rem; text-transform: uppercase; border: 1px solid #1a3a5c; }
        td { padding: 8px; border: 1px solid #cbd5e1; text-align: center; font-size: 0.9rem; }
        .disc-name { text-align: left; font-weight: bold; background: #fdfdfd; }
        .media-final { font-weight: bold; background: #f1f5f9; }

        .assinaturas { display: flex; justify-content: space-between; margin-top: 60px; text-align: center; }
        .assinatura-box { width: 200px; border-top: 1px solid #333; padding-top: 5px; font-size: 0.8rem; }

        @media print {
            body { background: none; }
            .folha { border: none; margin: 0; padding: 10mm; width: 100%; }
            .no-print { display: none !important; }
        }
        
        .btn-print { position: fixed; bottom: 30px; right: 30px; background: #e8a020; color: #1a3a5c; border: none; padding: 15px 25px; border-radius: 50px; font-weight: bold; cursor: pointer; box-shadow: 0 5px 15px rgba(0,0,0,0.3); z-index: 9999; display: flex; align-items: center; gap: 10px; transition: 0.3s; }
        .btn-print:hover { transform: scale(1.05); background: #d4911a; }
    </style>
</head>
<body>

    <button class="btn-print no-print" onclick="window.print()">
        <i class="fas fa-print"></i> IMPRIMIR BOLETIM
    </button>

    <div class="folha">
        <div class="cabecalho">
            <div class="logo">
                <img src="/betilson/sistema_escolar/<?= htmlspecialchars($logo) ?>" alt="Logo">
            </div>
            <div class="escola-info">
                <h1><?= htmlspecialchars($nome_escola) ?></h1>
                <p><?= htmlspecialchars($cfg['escola_morada'] ?? 'Endereço não configurado') ?></p>
                <p>Tel: <?= htmlspecialchars($cfg['escola_telefone'] ?? 'Sem telefone') ?></p>
            </div>
        </div>

        <div class="titulo-doc">Boletim de Aproveitamento Escolar</div>

        <div class="dados-aluno">
            <div><strong>ALUNO:</strong> <?= htmlspecialchars($aluno['nome_completo']) ?></div>
            <div><strong>TURMA:</strong> <?= htmlspecialchars($aluno['turma_nome']) ?></div>
            <div><strong>PROCESSO:</strong> <?= str_pad($aluno['id'], 5, '0', STR_PAD_LEFT) ?></div>
            <div><strong>ANO LECTIVO:</strong> 2024/2025</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="40%">Disciplina</th>
                    <th>1º Trim.</th>
                    <th>2º Trim.</th>
                    <th>3º Trim.</th>
                    <th>Média Final</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($boletim as $disciplina => $notas): 
                    $n1 = $notas[1] ?? null;
                    $n2 = $notas[2] ?? null;
                    $n3 = $notas[3] ?? null;
                    $valores = array_filter([$n1, $n2, $n3], function($v){ return $v !== null && $v !== ''; });
                    $media = count($valores) > 0 ? array_sum($valores) / count($valores) : 0;
                ?>
                <tr>
                    <td class="disc-name"><?= htmlspecialchars($disciplina) ?></td>
                    <td><?= ($n1 !== null) ? number_format($n1, 1) : '—' ?></td>
                    <td><?= ($n2 !== null) ? number_format($n2, 1) : '—' ?></td>
                    <td><?= ($n3 !== null) ? number_format($n3, 1) : '—' ?></td>
                    <td class="media-final" style="color: <?= $media >= 10 ? '#059669' : '#dc2626' ?>">
                        <?= number_format($media, 1) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="assinaturas">
            <div class="assinatura-box">Encarregado de Educação</div>
            <div class="assinatura-box">Director de Turma</div>
            <div class="assinatura-box">Direcção Pedagógica</div>
        </div>
        
        <div style="position: absolute; bottom: 15px; left: 0; right: 0; text-align: center; font-size: 0.65rem; color: #999;">
            Documento Oficial - Colégio Visão do Futuro | Emitido em <?= date('d/m/Y H:i') ?>
        </div>
    </div>

</body>
</html>
