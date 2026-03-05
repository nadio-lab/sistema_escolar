<?php
require_once '../includes/conexao.php';
verificarLogin();
$titulo_pagina = 'Finanças';
$pagina_atual  = 'financas';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'criar') {
        $desc   = limpar($_POST['descricao'] ?? '');
        $valor  = (float)str_replace(',','.',($_POST['valor'] ?? 0));
        $tipo   = limpar($_POST['tipo'] ?? 'receita');
        $cat    = (int)($_POST['categoria_id'] ?? 0);
        $data   = limpar($_POST['data_transacao'] ?? date('Y-m-d'));
        $aluno  = (int)($_POST['aluno_id'] ?? 0);
        $estado = limpar($_POST['estado'] ?? 'pago');
        $obs    = limpar($_POST['observacoes'] ?? '');
        $uid    = (int)$_SESSION['uid'];
        $conn->query("INSERT INTO fin_transacoes (descricao,valor,tipo,categoria_id,data_transacao,aluno_id,estado,observacoes,criado_por)
            VALUES ('$desc',$valor,'$tipo'," . ($cat?"$cat":'NULL') . ",'$data'," . ($aluno?"$aluno":'NULL') . ",'$estado','$obs',$uid)");
        redir('financas.php','Transação registada com sucesso!');
    }
    if ($acao === 'apagar') {
        $id = (int)($_POST['id'] ?? 0);
        $conn->query("DELETE FROM fin_transacoes WHERE id=$id");
        redir('financas.php','Transação removida.','warning');
    }
    if ($acao === 'estado') {
        $id = (int)($_POST['id'] ?? 0);
        $estado = limpar($_POST['estado'] ?? 'pago');
        $conn->query("UPDATE fin_transacoes SET estado='$estado' WHERE id=$id");
        redir('financas.php','Estado atualizado!');
    }
}

$mes = $_GET['mes'] ?? date('Y-m');
$filtro_tipo = $_GET['tipo'] ?? '';
$where = "DATE_FORMAT(t.data_transacao,'%Y-%m')='$mes'";
if ($filtro_tipo) $where .= " AND t.tipo='$filtro_tipo'";

 // Substitua o bloco das consultas (aprox. linha 41) por este:

$r_rec = $conn->query("SELECT COALESCE(SUM(valor),0) v FROM fin_transacoes WHERE tipo='receita' AND estado='pago' AND data_transacao LIKE '$mes%'");
$receitas = ($r_rec) ? $r_rec->fetch_assoc()['v'] : 0;

$r_des = $conn->query("SELECT COALESCE(SUM(valor),0) v FROM fin_transacoes WHERE tipo='despesa' AND estado='pago' AND data_transacao LIKE '$mes%'");
$despesas = ($r_des) ? $r_des->fetch_assoc()['v'] : 0;

$saldo = $receitas - $despesas;

$sql_principal = "SELECT t.*, c.nome cat_nome, c.cor cat_cor, a.nome_completo aluno_nome
    FROM fin_transacoes t
    LEFT JOIN fin_categorias c ON t.categoria_id=c.id
    LEFT JOIN alunos a ON t.aluno_id=a.id
    WHERE $where ORDER BY t.data_transacao DESC, t.criado_em DESC";

$transacoes = $conn->query($sql_principal);

// VERIFICAÇÃO DE ERRO: Se a consulta falhar, isto dirá o porquê
if (!$transacoes) {
    die("<div style='color:red;padding:20px;background:#fff;border:2px solid red;'>
            <strong>Erro no SQL:</strong> " . $conn->error . "<br>
            <strong>Consulta:</strong> " . $sql_principal . "
         </div>");
}

$transacoes = $conn->query("SELECT t.*, c.nome cat_nome, c.cor cat_cor, a.nome_completo aluno_nome
    FROM fin_transacoes t
    LEFT JOIN fin_categorias c ON t.categoria_id=c.id
    LEFT JOIN alunos a ON t.aluno_id=a.id
    WHERE $where ORDER BY t.data_transacao DESC, t.criado_em DESC");

$categorias  = $conn->query("SELECT * FROM fin_categorias WHERE ativo=1 ORDER BY tipo,nome");
$cats_arr = [];
while($c = $categorias->fetch_assoc()) $cats_arr[] = $c;

$alunos_sel = $conn->query("SELECT id,nome_completo FROM alunos WHERE estado='ativo' ORDER BY nome_completo");
$alunos_arr = [];
while($a = $alunos_sel->fetch_assoc()) $alunos_arr[] = $a;

require_once '../includes/cabecalho.php';
?>
<?php flash(); ?>
<style type="text/css">
  .btn-recibo {
    background-color: #1a3a5c; /* Azul do logo */
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-recibo:hover {
    background-color: #e8a020; /* Dourado do logo ao passar o rato */
    color: #1a3a5c;
}

</style>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
  <div>
    <h2 style="font-family:'Playfair Display',serif;color:var(--p);font-size:1.5rem">Gestão Financeira</h2>
    <p style="color:#94a3b8;font-size:.83rem">Receitas e despesas · <?= $mes ?></p>
  </div>
  <div style="display:flex;gap:10px;align-items:center">
    <form method="GET" style="display:flex;gap:8px;align-items:center">
      <input type="month" name="mes" class="form-control" value="<?= $mes ?>" style="width:auto" onchange="this.form.submit()">
      <select name="tipo" class="form-control" style="width:auto" onchange="this.form.submit()">
        <option value="">Todos</option>
        <option value="receita" <?= $filtro_tipo==='receita'?'selected':'' ?>>Receitas</option>
        <option value="despesa" <?= $filtro_tipo==='despesa'?'selected':'' ?>>Despesas</option>
      </select>
    </form>
    <button class="btn btn-p" onclick="abrirModal('modalTrans')"><i class="fas fa-plus"></i> Nova Transação</button>
  </div>
</div>

<!-- Resumo -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px">
  <div style="border-radius:var(--raio);padding:20px;background:linear-gradient(135deg,#ecfdf5,#d1fae5);border:1px solid #a7f3d0">
    <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#065f46;margin-bottom:8px">Total Receitas</div>
    <div style="font-family:'Playfair Display',serif;font-size:1.7rem;font-weight:700;color:#059669">Kz <?= number_format($receitas,0,',','.') ?></div>
  </div>
  <div style="border-radius:var(--raio);padding:20px;background:linear-gradient(135deg,#fef2f2,#fee2e2);border:1px solid #fca5a5">
    <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#991b1b;margin-bottom:8px">Total Despesas</div>
    <div style="font-family:'Playfair Display',serif;font-size:1.7rem;font-weight:700;color:#dc2626">Kz <?= number_format($despesas,0,',','.') ?></div>
  </div>
  <div style="border-radius:var(--raio);padding:20px;background:linear-gradient(135deg,rgba(26,58,92,0.06),rgba(26,58,92,0.1));border:1px solid rgba(26,58,92,0.15)">
    <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--p);margin-bottom:8px">Saldo do Período</div>
    <div style="font-family:'Playfair Display',serif;font-size:1.7rem;font-weight:700;color:<?= $saldo>=0?'#059669':'#dc2626' ?>">Kz <?= number_format($saldo,0,',','.') ?></div>
  </div>
</div>

<div class="card">
  <div class="card-header"><div class="card-titulo"><i class="fas fa-list"></i> Transações</div></div>
  <div class="card-body" style="padding:0;overflow-x:auto">
    <table class="tabela">
      <thead><tr><th>Descrição</th><th>Categoria</th><th>Aluno</th><th>Data</th><th>Valor</th><th>Estado</th><th>Print</th><th>Ações</th></tr></thead>
      <tbody>
      <?php if ($transacoes->num_rows === 0): ?>
        <tr><td colspan="7" style="text-align:center;padding:40px;color:#94a3b8">Nenhuma transação neste período.</td></tr>
      <?php else: ?>
      <?php while ($tr = $transacoes->fetch_assoc()):
        $cor_val = $tr['tipo']==='receita' ? '#059669' : '#dc2626';
        $sinal   = $tr['tipo']==='receita' ? '+' : '−';
        $estados = ['pago'=>'badge-verde','pendente'=>'badge-amarelo','cancelado'=>'badge-vermelho'];
        $bc = $estados[$tr['estado']] ?? 'badge-azul';
      ?>
      <tr>
        <td>
          <div style="font-weight:600;font-size:.85rem"><?= htmlspecialchars($tr['descricao']) ?></div>
          <span class="badge <?= $tr['tipo']==='receita'?'badge-verde':'badge-vermelho' ?>" style="margin-top:3px;font-size:.62rem"><?= ucfirst($tr['tipo']) ?></span>
        </td>
        <td>
          <?php if($tr['cat_nome']): ?>
          <span style="display:inline-flex;align-items:center;gap:5px;font-size:.82rem">
            <span style="width:8px;height:8px;border-radius:50%;background:<?= htmlspecialchars($tr['cat_cor']??'#888') ?>;flex-shrink:0"></span>
            <?= htmlspecialchars($tr['cat_nome']) ?>
          </span>
          <?php else: ?>—<?php endif; ?>
        </td>
        <td style="font-size:.82rem"><?= htmlspecialchars($tr['aluno_nome'] ?? '—') ?></td>
        <td style="font-size:.82rem;white-space:nowrap"><?= date('d/m/Y',strtotime($tr['data_transacao'])) ?></td>
        <td style="font-weight:700;color:<?= $cor_val ?>;white-space:nowrap"><?= $sinal ?> Kz <?= number_format($tr['valor'],0,',','.') ?></td>
        <td>
          <form method="POST" style="display:inline">
            <input type="hidden" name="acao" value="estado">
            <input type="hidden" name="id" value="<?= $tr['id'] ?>">
            <select name="estado" class="form-control" style="padding:4px 8px;font-size:.75rem;width:auto" onchange="this.form.submit()">
              <option value="pago" <?= $tr['estado']==='pago'?'selected':'' ?>>Pago</option>
              <option value="pendente" <?= $tr['estado']==='pendente'?'selected':'' ?>>Pendente</option>
              <option value="cancelado" <?= $tr['estado']==='cancelado'?'selected':'' ?>>Cancelado</option>
            </select>
          </form>
        </td>
        <td>
            <!-- BOTÃO DO RECIBO (Adiciona este bloco) -->
            <?php if ($tr['tipo'] === 'receita' && $tr['estado'] === 'pago'): ?>
              <a href="recibo.php?id=<?= $tr['id'] ?>" target="_blank" class="btn" 
                 style="background:#e8a020; color:#1a3a5c; padding:5px 8px; border-radius:4px;" title="Imprimir Recibo">
                <i class="fas fa-print"></i>
              </a>
            <?php endif; ?>
          </td>
        <td>
          <form method="POST" style="display:inline" onsubmit="return confirm('Remover transação?')">
            <input type="hidden" name="acao" value="apagar">
            <input type="hidden" name="id" value="<?= $tr['id'] ?>">
            <button type="submit" class="btn-acao btn-apagar"><i class="fas fa-trash"></i></button>
          </form>
        </td>
      </tr>
      <?php endwhile; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Transação -->
<div class="modal-overlay" id="modalTrans">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-titulo"><i class="fas fa-coins me-2"></i>Nova Transação</div>
      <button class="modal-fechar" onclick="fecharModal('modalTrans')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="acao" value="criar">
        <div class="form-group"><label>Descrição *</label><input type="text" name="descricao" class="form-control" placeholder="Ex: Propina de Março — Nome do Aluno" required></div>
        <div class="form-row">
          <div class="form-group">
            <label>Tipo *</label>
            <select name="tipo" id="tipo-trans" class="form-control" onchange="filtrarCats()">
              <option value="receita">Receita</option>
              <option value="despesa">Despesa</option>
            </select>
          </div>
          <div class="form-group">
            <label>Categoria</label>
            <select name="categoria_id" id="cat-trans" class="form-control">
              <option value="">Sem categoria</option>
              <?php foreach ($cats_arr as $c): ?>
              <option value="<?= $c['id'] ?>" data-tipo="<?= $c['tipo'] ?>"><?= htmlspecialchars($c['nome']) ?> (<?= $c['tipo'] ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Valor (Kz) *</label><input type="number" name="valor" class="form-control" placeholder="15000" step="0.01" required></div>
          <div class="form-group"><label>Data *</label><input type="date" name="data_transacao" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Aluno (opcional)</label>
            <select name="aluno_id" class="form-control">
              <option value="">Nenhum</option>
              <?php foreach ($alunos_arr as $a): ?>
              <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nome_completo']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Estado</label>
            <select name="estado" class="form-control">
              <option value="pago">Pago</option>
              <option value="pendente">Pendente</option>
              <option value="cancelado">Cancelado</option>
            </select>
          </div>
        </div>
        <div class="form-group"><label>Observações</label><textarea name="observacoes" class="form-control" rows="2" placeholder="Notas adicionais..."></textarea></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-out" onclick="fecharModal('modalTrans')">Cancelar</button>
        <button type="submit" class="btn btn-p"><i class="fas fa-save"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>
<script>
function filtrarCats(){
  const tipo=document.getElementById('tipo-trans').value;
  document.querySelectorAll('#cat-trans option[data-tipo]').forEach(o=>{
    o.style.display=o.dataset.tipo===tipo?'':'none';
  });
}
filtrarCats();
</script>
<?php require_once '../includes/rodape.php'; ?>
