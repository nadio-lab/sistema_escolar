 <?php
require_once '../includes/conexao.php';
verificarLogin();
$titulo_pagina = 'Gestão de Alunos';
$pagina_atual  = 'alunos';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar' || $acao === 'editar') {
        $nome        = limpar($_POST['nome_completo'] ?? '');
        $email_aluno = limpar($_POST['email_aluno'] ?? ''); // Novo campo
        $senha_acesso = limpar($_POST['senha_acesso'] ?? ''); // Novo campo
        $nasc        = limpar($_POST['data_nascimento'] ?? '');
        $genero      = limpar($_POST['genero'] ?? 'M');
        $turma       = (int)($_POST['turma_id'] ?? 0);
        $resp_nome   = limpar($_POST['responsavel_nome'] ?? '');
        $resp_tel    = limpar($_POST['responsavel_telefone'] ?? '');
        $resp_email  = limpar($_POST['responsavel_email'] ?? '');
        $morada      = limpar($_POST['morada'] ?? '');
        $data_m      = limpar($_POST['data_matricula'] ?? date('Y-m-d'));

        if ($acao === 'criar') {
            // Criptografia da senha
            $senha_plana = $_POST['senha_acesso'] ?? 'Aluno123'; 
            $senha_hash  = password_hash($senha_plana, PASSWORD_DEFAULT);
            
            // Query atualizada com email_aluno e senha
            $sql = "INSERT INTO alunos (nome_completo, email_aluno, senha_acesso, data_nascimento, genero, turma_id, responsavel_nome, responsavel_telefone, responsavel_email, morada, data_matricula, estado)
                    VALUES ('$nome', '$email_aluno', '$senha_hash', '$nasc', '$genero', " . ($turma?$turma:'NULL') . ", '$resp_nome', '$resp_tel', '$resp_email', '$morada', '$data_m', 'ativo')";
            
            if($conn->query($sql)) {
                redir('alunos.php', 'Aluno matriculado e senha de acesso configurada!');
            }
        } else {
            $id = (int)($_POST['id'] ?? 0);
            
            // Lógica de senha na edição (opcional: só altera se preencherem nova senha)
            $update_senha = "";
            if (!empty($_POST['senha_acesso'])) {
                $nova_senha = password_hash($_POST['senha_acesso'], PASSWORD_DEFAULT);
                $update_senha = ", senha_acesso='$nova_senha'";
            }

            $sql = "UPDATE alunos SET 
                    nome_completo='$nome', 
                    email_aluno='$email_aluno', 
                    data_nascimento='$nasc', 
                    genero='$genero', 
                    turma_id=" . ($turma?$turma:'NULL') . ", 
                    responsavel_nome='$resp_nome', 
                    responsavel_telefone='$resp_tel', 
                    responsavel_email='$resp_email', 
                    morada='$morada' 
                    $update_senha 
                    WHERE id=$id";
            
            if($conn->query($sql)) {
                redir('alunos.php', 'Dados do aluno atualizados!');
            }
        }
    }

    if ($acao === 'apagar') {
        $id = (int)($_POST['id'] ?? 0);
        $conn->query("DELETE FROM alunos WHERE id=$id");
        redir('alunos.php', 'Aluno removido.', 'warning');
    }

    if ($acao === 'estado') {
        $id = (int)($_POST['id'] ?? 0);
        $est = limpar($_POST['estado'] ?? 'ativo');
        $conn->query("UPDATE alunos SET estado='$est' WHERE id=$id");
        redir('alunos.php', 'Estado atualizado.');
    }
}

// LISTAGEM (Removida referência a 'matricula' no WHERE)
$pesquisa = limpar($_GET['q'] ?? '');
$filtro_t = (int)($_GET['turma'] ?? 0);
$where = '1=1';
if ($pesquisa) $where .= " AND a.nome_completo LIKE '%$pesquisa%'";
 // 1. Capturar os filtros da URL
$pesquisa = limpar($_GET['q'] ?? '');
$filtro_turma = (int)($_GET['turma'] ?? 0); // Certifica-te que o nome aqui é 'turma'

$where = '1=1';
if ($pesquisa) {
    $where .= " AND a.nome_completo LIKE '%$pesquisa%'";
}
if ($filtro_turma > 0) {
    $where .= " AND a.turma_id = $filtro_turma";
}

// 2. Consulta dos Alunos com o filtro aplicado
$alunos = $conn->query("SELECT a.*, t.nome as turma_nome FROM alunos a LEFT JOIN turmas t ON a.turma_id = t.id WHERE $where ORDER BY a.nome_completo ASC");

// 3. Consulta das Turmas para preencher o SELECT (IMPORTANTE)
$res_turmas = $conn->query("SELECT id, nome FROM turmas WHERE ativo=1 ORDER BY nome");
$turmas_arr = [];
if ($res_turmas) {
    while ($t = $res_turmas->fetch_assoc()) {
        $turmas_arr[] = $t;
    }
}

require_once '../includes/cabecalho.php';
?>
 

<?php flash(); ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
  <div>
    <h2 style="font-family:'Playfair Display',serif;color:var(--p);font-size:1.5rem">Gestão de Alunos</h2>
    <p style="color:#94a3b8;font-size:.83rem"><?= $alunos->num_rows ?> aluno(s) encontrado(s)</p>
  </div>
  <button class="btn btn-p" onclick="abrirModal('modalAluno')"><i class="fas fa-plus"></i> Novo Aluno</button>
</div>

<!-- Filtros -->
<form method="GET" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap">
  <input type="text" name="q" class="form-control" placeholder="Pesquisar por nome ou matrícula..." value="<?= htmlspecialchars($pesquisa) ?>" style="flex:1;min-width:200px">
  <select name="turma" class="form-control" style="width:auto">
    <option value="0">Todas as turmas</option>
    <?php foreach ($turmas_arr as $t): ?>
    <option value="<?= $t['id'] ?>" <?= $filtro_turma==$t['id']?'selected':'' ?>><?= htmlspecialchars($t['nome']) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-p"><i class="fas fa-search"></i></button>
  <?php if ($pesquisa || $filtro_turma): ?><a href="alunos.php" class="btn btn-out"><i class="fas fa-times"></i></a><?php endif; ?>
</form>

<div class="card">
  <div class="card-body" style="padding:0;overflow-x:auto">
    <table class="tabela">
      <thead>
        <tr><th>#</th><th>Nome Completo</th><th>Matrícula</th><th>Turma</th><th>Encarregado</th><th>Contacto</th><th>Estado</th><th>Ações</th></tr>
      </thead>
      <tbody>
      <?php if ($alunos->num_rows === 0): ?>
        <tr><td colspan="8" style="text-align:center;padding:40px;color:#94a3b8">Nenhum aluno encontrado.</td></tr>
      <?php else: ?>
      <?php $i=1; while($al = $alunos->fetch_assoc()):
        $inicial = strtoupper(substr($al['nome_completo'],0,1));
        $cores = ['A'=>'#1a3a5c','B'=>'#e8a020','C'=>'#22c55e','D'=>'#6f42c1','E'=>'#ef4444','F'=>'#0d6efd','G'=>'#f59e0b','H'=>'#06b6d4'];
        $cor_av = $cores[$inicial] ?? '#1a3a5c';
        $estados = ['ativo'=>'badge-verde','inativo'=>'badge-vermelho','transferido'=>'badge-amarelo'];
        $bc = $estados[$al['estado']] ?? 'badge-azul';
      ?>
      <tr>
        <td style="color:#94a3b8;font-size:.78rem"><?= $i++ ?></td>
        <td>
          <div style="display:flex;align-items:center;gap:10px">
            <div style="width:36px;height:36px;border-radius:50%;background:<?= $cor_av ?>;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.82rem;flex-shrink:0"><?= $inicial ?></div>
            <div>
              <div style="font-weight:600;font-size:.87rem"><?= htmlspecialchars($al['nome_completo']) ?></div>
              <?php if($al['data_nascimento']): ?><div style="font-size:.72rem;color:#94a3b8"><?= date('d/m/Y',strtotime($al['data_nascimento'])) ?></div><?php endif; ?>
            </div>
          </div>
        </td>
         <td><span class="badge badge-fundo">AL-<?= str_pad($al['id'], 4, '0', STR_PAD_LEFT) ?></span></td>
        <td><?= htmlspecialchars($al['turma_nome'] ?? '—') ?></td>
        <td style="font-size:.83rem"><?= htmlspecialchars($al['responsavel_nome'] ?? '—') ?></td>
        <td style="font-size:.83rem"><?= htmlspecialchars($al['responsavel_telefone'] ?? '—') ?></td>
        <td><span class="badge <?= $bc ?>"><?= ucfirst($al['estado']) ?></span></td>
        <td>
          <div style="display:flex;gap:5px">
            <button class="btn-acao btn-editar" onclick="editarAluno(<?= htmlspecialchars(json_encode($al)) ?>)" title="Editar"><i class="fas fa-edit"></i></button>
            <form method="POST" style="display:inline" onsubmit="return confirm('Remover este aluno?')">
              <input type="hidden" name="acao" value="apagar">
              <input type="hidden" name="id" value="<?= $al['id'] ?>">
              <button type="submit" class="btn-acao btn-apagar" title="Apagar"><i class="fas fa-trash"></i></button>
            </form>
          </div>
        </td>
      </tr>
      <?php endwhile; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Aluno -->
<div class="modal-overlay" id="modalAluno">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-titulo" id="modal-titulo-aluno"><i class="fas fa-user-plus me-2"></i>Matricular Aluno</div>
      <button class="modal-fechar" onclick="fecharModal('modalAluno')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="acao" id="acao-aluno" value="criar">
        <input type="hidden" name="id" id="aluno-id" value="">
        <div class="form-group">
          <label>Nome Completo *</label>
          <input type="text" name="nome_completo" id="al-nome" class="form-control" placeholder="Nome completo do aluno" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Data de Nascimento</label>
            <input type="date" name="data_nascimento" id="al-nasc" class="form-control">
          </div>
          <div class="form-group">
            <label>Género</label>
            <select name="genero" id="al-genero" class="form-control">
              <option value="M">Masculino</option>
              <option value="F">Feminino</option>
              <option value="Outro">Outro</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Turma</label>
            <select name="turma_id" id="al-turma" class="form-control">
              <option value="">Sem turma</option>
              <?php foreach ($turmas_arr as $t): ?>
              <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Data de Matrícula</label>
            <input type="date" name="data_matricula" id="al-mat-data" class="form-control" value="<?= date('Y-m-d') ?>">
          </div>
        </div>
        <div class="form-group">
          <label>Nome do Encarregado</label>
          <input type="text" name="responsavel_nome" id="al-resp-nome" class="form-control" placeholder="Nome do pai/mãe/encarregado">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Telefone do Encarregado</label>
            <input type="tel" name="responsavel_telefone" id="al-resp-tel" class="form-control" placeholder="+244 ...">
          </div>
          <div class="form-group">
            <label>Email do Encarregado</label>
            <input type="email" name="responsavel_email" id="al-resp-email" class="form-control" placeholder="email@...">
          </div>
        </div>
        <div class="form-group">
          <label>Morada</label>
          <input type="text" name="morada" id="al-morada" class="form-control" placeholder="Rua, bairro, município...">
        </div>
 <!-- Dentro do modal-body do seu formulário -->
 <!-- Seção de Acesso (Login) -->
<div class="form-group" style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed #ccc;">
    <label><strong>E-mail de Login do Aluno *</strong></label>
    <input type="email" name="email_aluno" id="al-email-login" class="form-control" placeholder="exemplo@escola.ao" required>
    <small class="text-muted">Este email será usado para o aluno entrar no portal.</small>
</div>

<div class="form-group" id="bloco-senha">
    <label>Senha de Acesso</label>
    <input type="password" name="senha_acesso" id="al-senha" class="form-control" placeholder="Deixe vazio para manter a atual ou Aluno123">
    <small style="color: #e8a020;">Sugestão: Use uma senha segura ou a padrão do colégio.</small>
</div>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-out" onclick="fecharModal('modalAluno')">Cancelar</button>
        <button type="submit" class="btn btn-p"><i class="fas fa-save"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>

<script>
function editarAluno(al){
  document.getElementById('acao-aluno').value='editar';
  document.getElementById('aluno-id').value=al.id;
  document.getElementById('al-nome').value=al.nome_completo||'';
  document.getElementById('al-nasc').value=al.data_nascimento||'';
  document.getElementById('al-genero').value=al.genero||'M';
  document.getElementById('al-turma').value=al.turma_id||'';
  document.getElementById('al-mat-data').value=al.data_matricula||'';
  document.getElementById('al-resp-nome').value=al.responsavel_nome||'';
      document.getElementById('al-email-login').value = al.email_aluno||'';
  document.getElementById('al-resp-tel').value=al.responsavel_telefone||'';
  document.getElementById('al-resp-email').value=al.responsavel_email||'';
  document.getElementById('al-morada').value=al.morada||'';
  document.getElementById('modal-titulo-aluno').innerHTML='<i class="fas fa-edit me-2"></i>Editar Aluno';
  abrirModal('modalAluno');
  function editarAluno(al) {
    // ... seus campos de preenchimento de nome, id, etc ...
    document.getElementById('bloco-senha').style.display = 'none'; // Esconde a senha na edição
    document.getElementById('acao-aluno').value = 'editar';
    abrirModal('modalAluno');
}

// Lembre-se de voltar a mostrar na função de criar:
function novoAluno() {
    document.getElementById('bloco-senha').style.display = 'block';
    // ... resetar campos ...
}

}
</script>

<?php require_once '../includes/rodape.php'; ?>
