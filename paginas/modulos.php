<?php
require_once '../includes/conexao.php';
verificarLogin();
$titulo_pagina = 'Módulos do Sistema';
$pagina_atual  = 'modulos';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id    = (int)($_POST['id'] ?? 0);
    $ativo = (int)($_POST['ativo'] ?? 0);
    $conn->query("UPDATE modulos SET ativo=$ativo WHERE id=$id");
    echo json_encode(['ok'=>true]); exit;
}

$modulos = $conn->query("SELECT * FROM modulos ORDER BY ordem ASC");
require_once '../includes/cabecalho.php';
?>
<?php flash(); ?>

<div style="margin-bottom:22px">
  <h2 style="font-family:'Playfair Display',serif;color:var(--p);font-size:1.5rem">Módulos do Sistema</h2>
  <p style="color:#94a3b8;font-size:.83rem">Ative ou desative funcionalidades do sistema</p>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px">
<?php while ($m = $modulos->fetch_assoc()):
  $ativo = $m['ativo'] == 1;
  $ic_bg  = $ativo ? 'var(--p)' : '#f1f5f9';
  $ic_clr = $ativo ? '#fff'     : '#94a3b8';
  $borda  = $ativo ? 'rgba(26,58,92,0.2)' : 'var(--borda)';
  $bg_card= $ativo ? 'rgba(26,58,92,0.02)' : '#fff';
?>
<div style="border:2px solid <?= $borda ?>;border-radius:13px;padding:18px;display:flex;align-items:center;justify-content:space-between;background:<?= $bg_card ?>;transition:all .2s" id="mod-<?= $m['id'] ?>">
  <div style="display:flex;align-items:center;gap:12px">
    <div style="width:42px;height:42px;border-radius:10px;background:<?= $ic_bg ?>;color:<?= $ic_clr ?>;display:flex;align-items:center;justify-content:center;font-size:.95rem;transition:all .3s" id="ic-<?= $m['id'] ?>">
      <i class="<?= htmlspecialchars($m['icone']) ?>"></i>
    </div>
    <div>
      <div style="font-weight:700;font-size:.87rem;color:#1e293b"><?= htmlspecialchars($m['nome']) ?></div>
      <div style="font-size:.73rem;color:<?= $ativo?'#22c55e':'#94a3b8' ?>;margin-top:2px" id="estado-<?= $m['id'] ?>"><?= $ativo?'Ativo':'Inativo' ?></div>
    </div>
  </div>
  <label class="tg">
    <input type="checkbox" <?= $ativo?'checked':'' ?> onchange="toggleMod(<?= $m['id'] ?>,this.checked)">
    <span class="tg-sl"></span>
  </label>
</div>
<?php endwhile; ?>
</div>

<script>
function toggleMod(id, ativo){
  fetch('modulos.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'id='+id+'&ativo='+(ativo?1:0)})
  .then(()=>{
    const card=document.getElementById('mod-'+id);
    const ic=document.getElementById('ic-'+id);
    const est=document.getElementById('estado-'+id);
    if(ativo){
      card.style.borderColor='rgba(26,58,92,0.2)';
      card.style.background='rgba(26,58,92,0.02)';
      ic.style.background='var(--p)'; ic.style.color='#fff';
      est.textContent='Ativo'; est.style.color='#22c55e';
    } else {
      card.style.borderColor='var(--borda)';
      card.style.background='#fff';
      ic.style.background='#f1f5f9'; ic.style.color='#94a3b8';
      est.textContent='Inativo'; est.style.color='#94a3b8';
    }
  });
}
</script>

<?php require_once '../includes/rodape.php'; ?>
