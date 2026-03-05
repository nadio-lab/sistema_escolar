<?php
require_once '../includes/conexao.php';
verificarLogin();

// SEGURANÇA: Só Admins entram
if ($_SESSION['user_tipo'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

$turma_id = (int)($_GET['turma_id'] ?? 0);
$aluno_id = (int)($_GET['aluno_id'] ?? 0);

// 1. Puxar Listas para Filtros
$turmas = $conn->query("SELECT id, nome FROM turmas ORDER BY nome");
$alunos = [];
if ($turma_id) {
    $alunos = $conn->query("SELECT id, nome_completo FROM alunos WHERE turma_id = $turma_id ORDER BY nome_completo");
}

// 2. Lógica de Consulta de Notas
$notas_boletim = [];
if ($aluno_id) {
    $sql = "SELECT n.*, d.nome as disc_nome, p.nome as prof_nome 
            FROM notas n 
            JOIN disciplinas d ON n.disciplina_id = d.id 
            JOIN utilizadores p ON n.professor_id = p.id 
            WHERE n.aluno_id = $aluno_id 
            ORDER BY d.nome, n.trimestre";
    $res = $conn->query($sql);
    
    while($row = $res->fetch_assoc()) {
        $notas_boletim[$row['disc_nome']][$row['trimestre']] = $row['nota'];
    }
}

require_once '../includes/cabecalho.php';
?>

<div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h2 style="font-family:'Playfair Display',serif; color:var(--p); margin:0;">Consulta de Boletins</h2>
    <?php if($aluno_id): ?>
        <a href="boletim_print.php?id=<?= $aluno_id ?>" target="_blank" class="btn btn-p">
            <i class="fas fa-print"></i> Imprimir Boletim Oficial
        </a>
    <?php endif; ?>
</div>

<!-- Filtros -->
<div class="card" style="margin-bottom: 25px; padding: 20px;">
    <form method="GET" style="display: flex; gap: 15px; align-items: flex-end;">
        <div style="flex: 1;">
            <label style="display:block; font-size:.8rem; margin-bottom:5px;">Selecionar Turma:</label>
            <select name="turma_id" class="form-control" onchange="this.form.submit()">
                <option value="">Escolha a Turma...</option>
                <?php while($t = $turmas->fetch_assoc()): ?>
                    <option value="<?= $t['id'] ?>" <?= $turma_id == $t['id'] ? 'selected' : '' ?>><?= $t['nome'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div style="flex: 1;">
            <label style="display:block; font-size:.8rem; margin-bottom:5px;">Selecionar Aluno:</label>
            <select name="aluno_id" class="form-control" onchange="this.form.submit()" <?= !$turma_id ? 'disabled' : '' ?>>
                <option value="">Escolha o Aluno...</option>
                <?php if($alunos): while($a = $alunos->fetch_assoc()): ?>
                    <option value="<?= $a['id'] ?>" <?= $aluno_id == $a['id'] ? 'selected' : '' ?>><?= $a['nome_completo'] ?></option>
                <?php endwhile; endif; ?>
            </select>
        </div>
    </form>
</div>

<!-- Tabela de Notas -->
<?php if($aluno_id && !empty($notas_boletim)): ?>
<div class="card">
    <div class="card-body" style="padding:0;">
        <table class="tabela">
            <thead>
                <tr>
                    <th style="text-align:left">Disciplina</th>
                    <th>1º Trim</th>
                    <th>2º Trim</th>
                    <th>3º Trim</th>
                    <th>Média Final</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($notas_boletim as $disciplina => $trimestres): 
                    $n1 = $trimestres[1] ?? null;
                    $n2 = $trimestres[2] ?? null;
                    $n3 = $trimestres[3] ?? null;
                    $media = array_filter([$n1, $n2, $n3]);
                    $final = count($media) > 0 ? array_sum($media) / count($media) : 0;
                ?>
                <tr>
                    <td style="text-align:left"><strong><?= $disciplina ?></strong></td>
                    <td><?= $n1 ?? '—' ?></td>
                    <td><?= $n2 ?? '—' ?></td>
                    <td><?= $n3 ?? '—' ?></td>
                    <td style="font-weight:bold; color: <?= $final >= 10 ? '#059669' : '#dc2626' ?>"><?= number_format($final, 1) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php elseif($aluno_id): ?>
    <div class="alert alert-amarelo">Nenhuma nota lançada para este aluno até ao momento.</div>
<?php endif; ?>

<?php require_once '../includes/rodape.php'; ?>
