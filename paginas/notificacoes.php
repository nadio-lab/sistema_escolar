<?php
require_once '../includes/conexao.php';
verificarLogin();
$titulo_pagina = 'Notificações';
$pagina_atual  = 'notificacoes';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $uid = (int)$_SESSION['uid'];
    if ($acao === 'marcar_lida') {
        $id = (int)($_POST['id'] ?? 0);
        $conn->query("UPDATE notificacoes SET lida=1 WHERE id=$id AND utilizador_id=$uid");
    }
    if ($acao === 'marcar_todas') {
        $conn->query("UPDATE notificacoes SET lida=1 WHERE utilizador_id=$uid");
    }
    if ($acao === 'apagar') {
        $id = (int)($_POST['id'] ?? 0);
        $conn->query("DELETE FROM notificacoes WHERE id=$id AND utilizador_id=$uid");
    }
    redir('notificacoes.php');
}

$uid = (int)$_SESSION['uid'];
$notifs = $conn->query("SELECT * FROM notificacoes WHERE utilizador_id=$uid ORDER BY criado_em DESC");
require_once '../includes/cabecalho.php';
?>
<?php flash(); ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
  <div>
    <h2 style="font-family:'Playfair Display',serif;color:var(--p);font-size:1.5rem">Notificações</h2>
    <p style="color:#94a3b8;font-size:.83rem"><?= $notifs->num_rows ?> notificação(ões)</p>
  </div>
  <form method="POST">
    <input type="hidden" name="acao" value="marcar_todas">
    <button type="submit" class="btn btn-out btn-sm"><i class="fas fa-check-double"></i> Marcar todas como lidas</button>
  </form>
</div>

<div class="card">
  <div class="card-body" style="padding:0">
  <?php if ($notifs->num_rows === 0): ?>
    <p style="text-align:center;padding:40px;color:#94a3b8">Sem notificações.</p>
  <?php else: ?>
  <?php while ($n = $notifs->fetch_assoc()):
    $estilos = ['info'=>'alerta-info','sucesso'=>'alerta-ok','aviso'=>'alerta-aviso','erro'=>'alerta-erro'];
    $icons = ['info'=>'info-circle','sucesso'=>'check-circle','aviso'=>'exclamation-triangle','erro'=>'times-circle'];
    $ec = $estilos[$n['tipo']] ?? 'alerta-info';
    $ic = $icons[$n['tipo']] ?? 'info-circle';
  ?>
  <div style="display:flex;gap:12px;align-items:flex-start;padding:16px 20px;border-bottom:1px solid #f1f5f9;<?= $n['lida']?'opacity:.55':'' ?>">
    <div style="width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;
      <?= $n['tipo']==='aviso'?'background:#fffbeb;color:#92400e':($n['tipo']==='erro'?'background:#fef2f2;color:#991b1b':($n['tipo']==='sucesso'?'background:#f0fdf4;color:#166534':'background:#eff6ff;color:#1e40af')) ?>">
      <i class="fas fa-<?= $ic ?>"></i>
    </div>
    <div style="flex:1">
      <div style="font-weight:700;font-size:.87rem;color:#1e293b"><?= htmlspecialchars($n['titulo']) ?></div>
      <div style="font-size:.82rem;color:#64748b;margin-top:3px"><?= htmlspecialchars($n['mensagem']) ?></div>
      <div style="font-size:.72rem;color:#94a3b8;margin-top:5px"><?= date('d/m/Y H:i',strtotime($n['criado_em'])) ?></div>
    </div>
    <div style="display:flex;gap:5px">
      <?php if (!$n['lida']): ?>
      <form method="POST" style="display:inline">
        <input type="hidden" name="acao" value="marcar_lida">
        <input type="hidden" name="id" value="<?= $n['id'] ?>">
        <button type="submit" class="btn-acao btn-editar" title="Marcar como lida"><i class="fas fa-check"></i></button>
      </form>
      <?php endif; ?>
      <form method="POST" style="display:inline">
        <input type="hidden" name="acao" value="apagar">
        <input type="hidden" name="id" value="<?= $n['id'] ?>">
        <button type="submit" class="btn-acao btn-apagar"><i class="fas fa-trash"></i></button>
      </form>
    </div>
  </div>
  <?php endwhile; endif; ?>
  </div>
</div>
<?php require_once '../includes/rodape.php'; ?>
