 <?php
require_once '../includes/conexao.php';
verificarLogin();

$cfg = allCfg();
$ano_atual = $cfg['ano_letivo_atual'] ?? date('Y').'/'.(date('Y')+1);
$titulo_pagina = 'Gestão de Turmas';
$pagina_atual  = 'turmas';
// CONSULTA DOS PROFESSORES PARA O SELECT
$professores_res = $conn->query("SELECT nome FROM utilizadores WHERE tipo = 'professor' AND ativo = 1 ORDER BY nome ASC");
$profs_lista = [];
while($p = $professores_res->fetch_assoc()) {
    $profs_lista[] = $p['nome'];
}

// AÇÕES POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar' || $acao === 'editar') {
        $nome       = limpar($_POST['nome'] ?? '');
        $ano        = limpar($_POST['ano_letivo'] ?? $ano_atual);
        $professor  = limpar($_POST['professor'] ?? '');
        $capacidade = (int)($_POST['capacidade'] ?? 35);
        $sala       = limpar($_POST['sala'] ?? '');

        if ($acao === 'criar') {
            $sql = "INSERT INTO turmas (nome, ano_letivo, professor_responsavel, capacidade, sala, ativo) 
                    VALUES ('$nome', '$ano', '$professor', $capacidade, '$sala', 1)";
            if($conn->query($sql)) redir('turmas.php', 'Turma criada com sucesso!');
        } else {
            $id = (int)$_POST['id'];
            $sql = "UPDATE turmas SET nome='$nome', ano_letivo='$ano', professor_responsavel='$professor', capacidade=$capacidade, sala='$sala' WHERE id=$id";
            if($conn->query($sql)) redir('turmas.php', 'Turma atualizada!');
        }
    }

    if ($acao === 'apagar') {
        $id = (int)$_POST['id'];
        // Segurança: Não apaga se houver alunos vinculados
        $check = $conn->query("SELECT id FROM alunos WHERE turma_id = $id LIMIT 1");
        if($check->num_rows > 0) {
            redir('turmas.php', 'Erro: Esta turma possui alunos matriculados e não pode ser eliminada.', 'danger');
        } else {
            $conn->query("DELETE FROM turmas WHERE id = $id");
            redir('turmas.php', 'Turma eliminada.', 'warning');
        }
    }
}

// CONSULTA DAS TURMAS
$turmas = $conn->query("SELECT t.*, (SELECT COUNT(*) FROM alunos WHERE turma_id = t.id) as total_alunos FROM turmas t ORDER BY t.nome ASC");

require_once '../includes/cabecalho.php';
?>

<?php flash(); ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
  <div>
    <h2 style="font-family:'Playfair Display',serif;color:var(--p);font-size:1.5rem">Gestão de Turmas</h2>
    <p style="color:#94a3b8;font-size:.83rem">Ano letivo <?= htmlspecialchars($ano_atual) ?></p>
  </div>
  <button class="btn btn-p" onclick="novaTurma()"><i class="fas fa-plus"></i> Nova Turma</button>
</div>

<div class="grid-3" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap:20px;">
<?php if (!$turmas || $turmas->num_rows === 0): ?>
  <p style="color:#94a3b8;grid-column:span 3;text-align:center;padding:40px">Nenhuma turma criada ainda.</p>
<?php else: ?>
  <?php while ($t = $turmas->fetch_assoc()): 
    $total = $t['total_alunos'] ?? 0;
    $cap = $t['capacidade'] > 0 ? $t['capacidade'] : 1;
    $pct = min(100, round(($total / $cap) * 100));
    $cor_prog = $pct >= 95 ? '#ef4444' : ($pct >= 80 ? '#f59e0b' : 'var(--p)');
  ?>
  <div class="card">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
      <div class="card-titulo"><i class="fas fa-chalkboard"></i> <?= htmlspecialchars($t['nome']) ?></div>
      <form method="POST" onsubmit="return confirm('Deseja eliminar esta turma?');" style="margin:0">
          <input type="hidden" name="acao" value="apagar">
          <input type="hidden" name="id" value="<?= $t['id'] ?>">
          <button type="submit" style="background:none; border:none; color:#ef4444; cursor:pointer;"><i class="fas fa-trash-alt"></i></button>
      </form>
    </div>
    <div class="card-body">
      <div style="font-size:.82rem;color:#64748b;margin-bottom:6px"><i class="fas fa-user me-1"></i><?= htmlspecialchars($t['professor_responsavel'] ?: 'Professor não atribuído') ?></div>
      <div style="font-size:.82rem;color:#64748b;margin-bottom:14px"><i class="fas fa-door-open me-1"></i>Sala: <?= htmlspecialchars($t['sala'] ?: '—') ?> &nbsp;·&nbsp; <?= $total ?> / <?= $t['capacidade'] ?> alunos</div>
      
      <div class="prog" style="background:#e2e8f0; height:8px; border-radius:4px; overflow:hidden; margin-bottom:4px">
          <div class="prog-bar" style="width:<?= $pct ?>%; height:100%; background:<?= $cor_prog ?>; transition:0.3s"></div>
      </div>
      <div style="font-size:.7rem;color:#94a3b8;margin-bottom:14px"><?= $pct ?>% da capacidade preenchida</div>
      
      <div style="display:flex;gap:8px">
        <a href="alunos.php?turma=<?= $t['id'] ?>" class="btn btn-out btn-sm" style="flex:1;justify-content:center;text-decoration:none">Ver Alunos</a>
        <button class="btn btn-p btn-sm" style="flex:1;justify-content:center" onclick='editarTurma(<?= json_encode($t) ?>)'>Editar</button>
      </div>
    </div>
  </div>
  <?php endwhile; ?>
<?php endif; ?>
</div>

<!-- Modal Turma -->
<div class="modal-overlay" id="modalTurma">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-titulo" id="titulo-mod-turma"><i class="fas fa-plus me-2"></i>Nova Turma</div>
      <button class="modal-fechar" onclick="fecharModal('modalTurma')">&times;</button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="acao" id="acao-turma" value="criar">
        <input type="hidden" name="id" id="turma-id">
        <div class="form-row">
          <div class="form-group"><label>Nome da Turma *</label><input type="text" name="nome" id="t-nome" class="form-control" required placeholder="Ex: 10ª A - Manhã"></div>
          <div class="form-group"><label>Ano Letivo</label><input type="text" name="ano_letivo" id="t-al" class="form-control" value="<?= htmlspecialchars($ano_atual) ?>"></div>
        </div>
         <div class="form-group">
    <label>Professor Responsável</label>
    <select name="professor" id="t-prof" class="form-control">
        <option value="">-- Selecione um Professor --</option>
        <?php foreach ($profs_lista as $prof): ?>
            <option value="<?= htmlspecialchars($prof) ?>"><?= htmlspecialchars($prof) ?></option>
        <?php endforeach; ?>
    </select>
</div>

        <div class="form-row">
          <div class="form-group"><label>Capacidade Máxima</label><input type="number" name="capacidade" id="t-cap" class="form-control" value="35"></div>
          <div class="form-group"><label>Número da Sala</label><input type="text" name="sala" id="t-sala" class="form-control" placeholder="Ex: Sala 04"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-p" style="width:100%"><i class="fas fa-save"></i> Guardar Turma</button>
      </div>
    </form>
  </div>
</div>

<script>
function novaTurma() {
  document.getElementById('acao-turma').value='criar';
  document.getElementById('turma-id').value='';
  document.getElementById('t-nome').value='';
  document.getElementById('t-prof').value='';
  document.getElementById('t-cap').value='35';
  document.getElementById('t-sala').value='';
  document.getElementById('titulo-mod-turma').innerHTML='<i class="fas fa-plus me-2"></i>Nova Turma';
  abrirModal('modalTurma');
}

function editarTurma(t){
  document.getElementById('acao-turma').value='editar';
  document.getElementById('turma-id').value=t.id;
  document.getElementById('t-nome').value=t.nome||'';
  document.getElementById('t-al').value=t.ano_letivo||'';
  document.getElementById('t-prof').value=t.professor_responsavel||'';
  document.getElementById('t-cap').value=t.capacidade||35;
  document.getElementById('t-sala').value=t.sala||'';
  document.getElementById('titulo-mod-turma').innerHTML='<i class="fas fa-edit me-2"></i>Editar Turma';
  abrirModal('modalTurma');
}
</script>

<?php require_once '../includes/rodape.php'; ?>
