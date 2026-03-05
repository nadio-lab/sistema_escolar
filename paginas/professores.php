 <?php
require_once '../includes/conexao.php';
verificarLogin();
$titulo_pagina = 'Gestão de Professores';
$pagina_atual  = 'professores';

// AÇÕES POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar' || $acao === 'editar') {
        $nome  = limpar($_POST['nome'] ?? '');
        $email = limpar($_POST['email'] ?? '');
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        $turma_id = (int)($_POST['turma_id'] ?? 0);
        $disciplinas = $_POST['disciplina_id'] ?? []; // Recebe array de disciplinas

        if ($acao === 'criar') {
            $senha = password_hash($_POST['senha'] ?? 'Professor@2026', PASSWORD_DEFAULT);
            $sql = "INSERT INTO utilizadores (nome, email, senha, tipo, ativo) 
                    VALUES ('$nome', '$email', '$senha', 'professor', $ativo)";
            
            if($conn->query($sql)) {
                $professor_id = $conn->insert_id;
                vincularDisciplinas($conn, $professor_id, $turma_id, $disciplinas);
                redir('professores.php', 'Professor cadastrado com sucesso!');
            }
        } else {
            $id = (int)$_POST['id'];
            $sql = "UPDATE utilizadores SET nome='$nome', email='$email', ativo=$ativo WHERE id=$id";
            
            if (!empty($_POST['senha'])) {
                $nova_senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                $conn->query("UPDATE utilizadores SET senha='$nova_senha' WHERE id=$id");
            }

            if($conn->query($sql)) {
                vincularDisciplinas($conn, $id, $turma_id, $disciplinas);
                redir('professores.php', 'Dados do professor atualizados!');
            }
        }
    }

    if ($acao === 'apagar') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM turmas_disciplinas WHERE professor_id=$id");
        $conn->query("DELETE FROM utilizadores WHERE id=$id AND tipo='professor'");
        redir('professores.php', 'Professor removido.', 'warning');
    }
}

// Função auxiliar para gerir os múltiplos vínculos
function vincularDisciplinas($conn, $prof_id, $turma_id, $disciplinas) {
    $conn->query("DELETE FROM turmas_disciplinas WHERE professor_id = $prof_id AND turma_id = $turma_id");
    
    if (in_array('todas', $disciplinas)) {
        // Se for professor primário, vincula a todas as disciplinas da base de dados
        $res = $conn->query("SELECT id FROM disciplinas");
        while($d = $res->fetch_assoc()) {
            $did = $d['id'];
            $conn->query("INSERT INTO turmas_disciplinas (turma_id, disciplina_id, professor_id) VALUES ($turma_id, $did, $prof_id)");
        }
    } else {
        // Vínculo individual (I Ciclo)
        foreach ($disciplinas as $did) {
            $did = (int)$did;
            if($did > 0) $conn->query("INSERT INTO turmas_disciplinas (turma_id, disciplina_id, professor_id) VALUES ($turma_id, $did, $prof_id)");
        }
    }
}

// BUSCAR DADOS PARA O MODAL
$turmas_list = $conn->query("SELECT id, nome FROM turmas ORDER BY nome");
$discs_list = $conn->query("SELECT id, nome FROM disciplinas ORDER BY nome");

// LISTAGEM
 // LISTAGEM ATUALIZADA
$professores = $conn->query("SELECT u.*, 
    (SELECT td.turma_id FROM turmas_disciplinas td WHERE td.professor_id = u.id LIMIT 1) as turma_id,
    (SELECT COUNT(*) FROM turmas_disciplinas WHERE professor_id = u.id) as total_discs,
    (SELECT t.nome FROM turmas_disciplinas td JOIN turmas t ON td.turma_id = t.id WHERE td.professor_id = u.id LIMIT 1) as turma_nome
    FROM utilizadores u 
    WHERE u.tipo='professor' ORDER BY u.nome ASC");

require_once '../includes/cabecalho.php';
?>

<?php flash(); ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
  <div>
    <h2 style="font-family:'Playfair Display',serif;color:var(--p);font-size:1.5rem">Gestão de Professores</h2>
    <p style="color:#94a3b8;font-size:.83rem">Configuração de turmas e disciplinas</p>
  </div>
  <button class="btn btn-p" onclick="novoProf()"><i class="fas fa-user-plus"></i> Novo Professor</button>
</div>

<div class="card">
  <div class="card-body" style="padding:0;overflow-x:auto">
    <table class="tabela">
      <thead>
        <tr>
          <th>Professor</th>
          <th>Vínculo Principal</th>
          <th>Estado</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
      <?php while($p = $professores->fetch_assoc()): ?>
      <tr>
        <td>
            <strong><?= htmlspecialchars($p['nome']) ?></strong><br>
            <small style="color:#64748b"><?= htmlspecialchars($p['email']) ?></small>
        </td>
        <td>
            <span class="badge badge-azul"><?= $p['turma_nome'] ?? 'Sem Turma' ?></span><br>
            <small><?= $p['total_discs'] ?> disciplinas atribuídas</small>
        </td>
        <td><span class="badge <?= $p['ativo']?'badge-verde':'badge-vermelho' ?>"><?= $p['ativo']?'Ativo':'Inativo' ?></span></td>
        <td>
          <button class="btn-acao btn-editar" onclick='editarProf(<?= json_encode($p) ?>)'><i class="fas fa-edit"></i></button>
          <form method="POST" style="display:inline" onsubmit="return confirm('Remover este professor?')">
            <input type="hidden" name="acao" value="apagar"><input type="hidden" name="id" value="<?= $p['id'] ?>">
            <button type="submit" class="btn-acao btn-apagar"><i class="fas fa-trash"></i></button>
          </form>
        </td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Professor -->
<div class="modal-overlay" id="modalProf">
  <div class="modal" style="max-width:480px">
    <div class="modal-header">
      <div class="modal-titulo" id="m-tit">Configurar Professor</div>
      <button onclick="fecharModal('modalProf')">&times;</button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="acao" id="p-acao" value="criar">
        <input type="hidden" name="id" id="p-id">
        
        <div class="form-group">
          <label>Nome Completo</label>
          <input type="text" name="nome" id="p-nome" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" id="p-email" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Turma</label>
            <select name="turma_id" id="p-turma" class="form-control" required>
                <?php $turmas_list->data_seek(0); while($t = $turmas_list->fetch_assoc()): ?>
                    <option value="<?= $t['id'] ?>"><?= $t['nome'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Disciplinas (Segure Ctrl para selecionar várias)</label>
            <select name="disciplina_id[]" id="p-disc" class="form-control" multiple style="height:120px" required>
                <option value="todas" style="font-weight:bold; color:var(--p)">-- TODAS AS DISCIPLINAS (Ensino Primário) --</option>
                <?php $discs_list->data_seek(0); while($d = $discs_list->fetch_assoc()): ?>
                    <option value="<?= $d['id'] ?>"><?= $d['nome'] ?></option>
                <?php endwhile; ?>
            </select>
            <small style="color:#64748b">Para o I Ciclo, selecione apenas a disciplina específica.</small>
        </div>

        <div class="form-group">
          <label>Senha <small id="aviso-senha" style="display:none;color:var(--s)">(Opcional na edição)</small></label>
          <input type="password" name="senha" id="p-senha" class="form-control">
        </div>
        <div class="form-group">
          <label><input type="checkbox" name="ativo" id="p-ativo" checked> Conta Ativa</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-p" style="width:100%">Guardar Configurações</button>
      </div>
    </form>
  </div>
</div>
 <script>
function novoProf() {
    document.getElementById('m-tit').innerText = 'Novo Professor';
    document.getElementById('p-acao').value = 'criar';
    document.getElementById('p-id').value = '';
    document.getElementById('p-nome').value = '';
    document.getElementById('p-email').value = '';
    document.getElementById('p-senha').required = true;
    document.getElementById('aviso-senha').style.display = 'none';
    
    // Limpa seleções de turma e disciplina
    document.getElementById('p-turma').selectedIndex = -1;
    document.getElementById('p-disc').selectedIndex = -1;
    
    abrirModal('modalProf');
}

function editarProf(d) {
    document.getElementById('m-tit').innerText = 'Editar Professor';
    document.getElementById('p-acao').value = 'editar';
    document.getElementById('p-id').value = d.id;
    document.getElementById('p-nome').value = d.nome;
    document.getElementById('p-email').value = d.email;
    document.getElementById('p-ativo').checked = d.ativo == 1;
    document.getElementById('p-senha').required = false;
    document.getElementById('aviso-senha').style.display = 'inline';

    // PREENCHE A TURMA AO EDITAR
    if(d.turma_id) {
        document.getElementById('p-turma').value = d.turma_id;
    }

    abrirModal('modalProf');
}
</script>


<?php require_once '../includes/rodape.php'; ?>
