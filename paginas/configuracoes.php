<?php
require_once '../includes/conexao.php';
verificarLogin();
$titulo_pagina = 'Configurações';
$pagina_atual  = 'configuracoes';

// Guardar configurações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'identidade') {
        setCfg('escola_nome',   limpar($_POST['escola_nome']   ?? ''));
        setCfg('escola_slogan', limpar($_POST['escola_slogan'] ?? ''));
        setCfg('ano_letivo',    limpar($_POST['ano_letivo']    ?? ''));

        // Upload logo
        if (!empty($_FILES['escola_logo']['name'])) {
            $ext = strtolower(pathinfo($_FILES['escola_logo']['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg','jpeg','png','svg','webp'];
            if (in_array($ext, $permitidos) && $_FILES['escola_logo']['size'] < 2*1024*1024) {
                $nome_ficheiro = 'logo_' . time() . '.' . $ext;
                $destino = '../uploads/logos/' . $nome_ficheiro;
                if (move_uploaded_file($_FILES['escola_logo']['tmp_name'], $destino)) {
                    setCfg('escola_logo', 'uploads/logos/' . $nome_ficheiro);
                }
            }
        }
        redir('configuracoes.php?tab=identidade', 'Identidade da escola atualizada!');
    }

    if ($acao === 'visual') {
        setCfg('cor_primaria',   limpar($_POST['cor_primaria']   ?? '#1a3a5c'));
        setCfg('cor_secundaria', limpar($_POST['cor_secundaria'] ?? '#e8a020'));
        setCfg('cor_sidebar',    limpar($_POST['cor_sidebar']    ?? '#0f2540'));
        redir('configuracoes.php?tab=visual', 'Cores atualizadas com sucesso!');
    }

    if ($acao === 'contacto') {
        setCfg('escola_telefone', limpar($_POST['escola_telefone'] ?? ''));
        setCfg('escola_email',    limpar($_POST['escola_email']    ?? ''));
        setCfg('escola_website',  limpar($_POST['escola_website']  ?? ''));
        redir('configuracoes.php?tab=contacto', 'Contactos atualizados!');
    }

    if ($acao === 'localizacao') {
        setCfg('escola_morada',    limpar($_POST['escola_morada']    ?? ''));
        setCfg('escola_provincia', limpar($_POST['escola_provincia'] ?? ''));
        setCfg('escola_pais',      limpar($_POST['escola_pais']      ?? ''));
        setCfg('escola_latitude',  limpar($_POST['escola_latitude']  ?? ''));
        setCfg('escola_longitude', limpar($_POST['escola_longitude'] ?? ''));
        redir('configuracoes.php?tab=localizacao', 'Localização atualizada!');
    }

    if ($acao === 'financeiro') {
        setCfg('propina_valor',    limpar($_POST['propina_valor']    ?? '15000'));
        setCfg('propina_dia_venc', limpar($_POST['propina_dia_venc'] ?? '10'));
        setCfg('moeda',            limpar($_POST['moeda']            ?? 'Kz'));
        redir('configuracoes.php?tab=financeiro', 'Configurações financeiras atualizadas!');
    }

    if ($acao === 'sistema') {
        setCfg('notif_email',     isset($_POST['notif_email']) ? '1' : '0');
        setCfg('modo_manutencao', isset($_POST['modo_manutencao']) ? '1' : '0');
        redir('configuracoes.php?tab=sistema', 'Configurações do sistema atualizadas!');
    }

    if ($acao === 'senha') {
        $senha_atual = $_POST['senha_atual'] ?? '';
        $nova_senha  = $_POST['nova_senha']  ?? '';
        $confirmar   = $_POST['confirmar']   ?? '';
        $uid = (int)$_SESSION['uid'];
        $r = $conn->query("SELECT senha FROM utilizadores WHERE id=$uid");
        $u = $r->fetch_assoc();
        if (!password_verify($senha_atual, $u['senha'])) {
            redir('configuracoes.php?tab=sistema', 'Senha atual incorreta!', 'danger');
        } elseif ($nova_senha !== $confirmar) {
            redir('configuracoes.php?tab=sistema', 'As senhas não coincidem!', 'danger');
        } elseif (strlen($nova_senha) < 6) {
            redir('configuracoes.php?tab=sistema', 'A senha deve ter pelo menos 6 caracteres!', 'danger');
        } else {
            $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $conn->query("UPDATE utilizadores SET senha='$hash' WHERE id=$uid");
            redir('configuracoes.php?tab=sistema', 'Senha alterada com sucesso!');
        }
    }
}

$cfg = allCfg();
$tab_ativa = $_GET['tab'] ?? 'identidade';

require_once '../includes/cabecalho.php';
?>
<?php flash(); ?>

<div style="margin-bottom:22px">
  <h2 style="font-family:'Playfair Display',serif;color:var(--p);font-size:1.5rem">Configurações da Escola</h2>
  <p style="color:#94a3b8;font-size:.83rem">Personalize todas as informações do sistema</p>
</div>

<!-- Tabs -->
<div style="display:flex;gap:7px;margin-bottom:24px;flex-wrap:wrap">
<?php
$tabs = [
    ['id'=>'identidade', 'label'=>'Identidade', 'icone'=>'fas fa-school'],
    ['id'=>'visual',     'label'=>'Visual & Cores','icone'=>'fas fa-palette'],
    ['id'=>'contacto',   'label'=>'Contactos',  'icone'=>'fas fa-phone'],
    ['id'=>'localizacao','label'=>'Localização','icone'=>'fas fa-map-marker-alt'],
    ['id'=>'financeiro', 'label'=>'Financeiro', 'icone'=>'fas fa-coins'],
    ['id'=>'sistema',    'label'=>'Sistema',    'icone'=>'fas fa-cog'],
];
foreach ($tabs as $tab):
  $ativo = $tab_ativa === $tab['id'];
  $estilos = $ativo
    ? 'background:var(--p);color:#fff;border-color:var(--p)'
    : 'background:#fff;color:#64748b;border-color:var(--borda)';
?>
<a href="?tab=<?= $tab['id'] ?>" style="display:inline-flex;align-items:center;gap:7px;padding:8px 16px;border-radius:9px;font-size:.8rem;font-weight:600;border:1.5px solid;text-decoration:none;transition:all .2s;<?= $estilos ?>">
  <i class="<?= $tab['icone'] ?>"></i><?= $tab['label'] ?>
</a>
<?php endforeach; ?>
</div>

<!-- ===== IDENTIDADE ===== -->
<?php if ($tab_ativa === 'identidade'): ?>
<div class="card">
  <div class="card-body">
    <!-- Preview ao vivo -->
    <div style="background:linear-gradient(135deg,<?= $cfg['cor_sidebar']??'#0f2540' ?>,<?= $cfg['cor_primaria']??'#1a3a5c' ?>);border-radius:12px;padding:24px;display:flex;align-items:center;gap:18px;margin-bottom:24px">
      <div style="width:60px;height:60px;background:<?= $cfg['cor_secundaria']??'#e8a020' ?>;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:24px;color:#fff;overflow:hidden;flex-shrink:0">
        <?php if (!empty($cfg['escola_logo'])): ?><img src="../<?= htmlspecialchars($cfg['escola_logo']) ?>" style="width:100%;height:100%;object-fit:cover"><?php else: ?><i class="fas fa-graduation-cap"></i><?php endif; ?>
      </div>
      <div>
        <div style="font-family:'Playfair Display',serif;color:#fff;font-size:1.3rem" id="prev-nome"><?= htmlspecialchars($cfg['escola_nome'] ?? '') ?></div>
        <div style="color:rgba(255,255,255,0.55);font-size:.85rem" id="prev-slogan"><?= htmlspecialchars($cfg['escola_slogan'] ?? '') ?></div>
      </div>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="acao" value="identidade">
      <div class="form-row">
        <div class="form-group">
          <label>Nome da Escola *</label>
          <input type="text" name="escola_nome" class="form-control" value="<?= htmlspecialchars($cfg['escola_nome'] ?? '') ?>" oninput="document.getElementById('prev-nome').textContent=this.value" required>
        </div>
        <div class="form-group">
          <label>Slogan / Lema</label>
          <input type="text" name="escola_slogan" class="form-control" value="<?= htmlspecialchars($cfg['escola_slogan'] ?? '') ?>" oninput="document.getElementById('prev-slogan').textContent=this.value">
        </div>
      </div>
      <div class="form-group">
        <label>Ano Letivo Atual</label>
        <input type="text" name="ano_letivo" class="form-control" value="<?= htmlspecialchars($cfg['ano_letivo'] ?? '') ?>" placeholder="ex: 2024/2025">
      </div>
      <div class="form-group">
        <label>Logo da Escola</label>
        <div style="display:flex;align-items:center;gap:16px">
          <div style="width:64px;height:64px;background:<?= $cfg['cor_secundaria']??'#e8a020' ?>;border-radius:13px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0">
            <?php if (!empty($cfg['escola_logo'])): ?><img src="../<?= htmlspecialchars($cfg['escola_logo']) ?>" style="width:100%;height:100%;object-fit:cover"><?php else: ?><i class="fas fa-graduation-cap" style="color:#fff;font-size:24px"></i><?php endif; ?>
          </div>
          <div>
            <input type="file" name="escola_logo" id="inp-logo" accept="image/*" style="display:none" onchange="prevLogo(this)">
            <button type="button" class="btn btn-out btn-sm" onclick="document.getElementById('inp-logo').click()"><i class="fas fa-upload"></i> Carregar Logo</button>
            <div class="form-hint">PNG, JPG, SVG ou WEBP · Máx. 2MB · Recomendado 200×200px</div>
          </div>
        </div>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:9px;margin-top:8px">
        <button type="submit" class="btn btn-p"><i class="fas fa-save"></i> Guardar Identidade</button>
      </div>
    </form>
  </div>
</div>
<script>
function prevLogo(inp){
  if(inp.files&&inp.files[0]){
    const r=new FileReader();
    r.onload=e=>{ document.querySelectorAll('.logo-prev').forEach(el=>{ el.innerHTML='<img src="'+e.target.result+'" style="width:100%;height:100%;object-fit:cover">'; }); };
    r.readAsDataURL(inp.files[0]);
  }
}
</script>

<!-- ===== VISUAL ===== -->
<?php elseif ($tab_ativa === 'visual'): ?>
<div class="card">
  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="acao" value="visual">
      <p style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--borda)">Esquema de Cores</p>
      <div class="form-row-3">
        <div class="form-group">
          <label>Cor Primária</label>
          <input type="color" name="cor_primaria" class="form-control" value="<?= $cfg['cor_primaria'] ?? '#1a3a5c' ?>" id="cp" oninput="aplicarCores()">
          <div class="form-hint">Cabeçalhos, botões principais, destaque</div>
        </div>
        <div class="form-group">
          <label>Cor de Destaque</label>
          <input type="color" name="cor_secundaria" class="form-control" value="<?= $cfg['cor_secundaria'] ?? '#e8a020' ?>" id="cs" oninput="aplicarCores()">
          <div class="form-hint">Badges, ícones de logo, acentos visuais</div>
        </div>
        <div class="form-group">
          <label>Cor da Sidebar</label>
          <input type="color" name="cor_sidebar" class="form-control" value="<?= $cfg['cor_sidebar'] ?? '#0f2540' ?>" id="csb" oninput="aplicarCores()">
          <div class="form-hint">Fundo da barra lateral de navegação</div>
        </div>
      </div>
      <p style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;margin:20px 0 14px;padding-bottom:8px;border-bottom:1px solid var(--borda)">Pré-visualização</p>
      <div style="display:flex;gap:12px;margin-bottom:20px">
        <div id="prev-p" style="flex:1;height:54px;border-radius:10px;background:<?= $cfg['cor_primaria']??'#1a3a5c' ?>;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.78rem;font-weight:600">Cor Primária</div>
        <div id="prev-s" style="flex:1;height:54px;border-radius:10px;background:<?= $cfg['cor_secundaria']??'#e8a020' ?>;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.78rem;font-weight:600">Cor de Destaque</div>
        <div id="prev-sb" style="flex:1;height:54px;border-radius:10px;background:<?= $cfg['cor_sidebar']??'#0f2540' ?>;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.78rem;font-weight:600">Sidebar</div>
      </div>
      <p style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--borda)">Temas Pré-definidos</p>
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px">
        <?php
        $temas = [
            ['Azul Oceano','#1a3a5c','#e8a020','#0f2540'],
            ['Verde Natureza','#15803d','#f59e0b','#14532d'],
            ['Roxo Criativo','#7c3aed','#ec4899','#4c1d95'],
            ['Vermelho Energia','#dc2626','#f97316','#7f1d1d'],
            ['Azul Celeste','#0369a1','#06b6d4','#0c4a6e'],
            ['Cinzento Elegante','#1e293b','#64748b','#0f172a'],
        ];
        foreach ($temas as $t):
        ?>
        <div onclick="aplicarTema('<?= $t[1] ?>','<?= $t[2] ?>','<?= $t[3] ?>')"
             style="width:50px;height:50px;border-radius:10px;cursor:pointer;background:linear-gradient(135deg,<?= $t[1] ?>,<?= $t[2] ?>);border:3px solid transparent;transition:all .2s"
             title="<?= $t[0] ?>"></div>
        <?php endforeach; ?>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:9px">
        <button type="submit" class="btn btn-p"><i class="fas fa-save"></i> Guardar Cores</button>
      </div>
    </form>
  </div>
</div>
<script>
function aplicarCores(){
  const p=document.getElementById('cp').value,s=document.getElementById('cs').value,sb=document.getElementById('csb').value;
  document.documentElement.style.setProperty('--p',p);
  document.documentElement.style.setProperty('--s',s);
  document.documentElement.style.setProperty('--sb',sb);
  document.getElementById('prev-p').style.background=p;
  document.getElementById('prev-s').style.background=s;
  document.getElementById('prev-sb').style.background=sb;
}
function aplicarTema(p,s,sb){
  document.getElementById('cp').value=p;
  document.getElementById('cs').value=s;
  document.getElementById('csb').value=sb;
  aplicarCores();
}
</script>

<!-- ===== CONTACTOS ===== -->
<?php elseif ($tab_ativa === 'contacto'): ?>
<div class="card">
  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="acao" value="contacto">
      <div class="form-row">
        <div class="form-group"><label>Telefone Principal</label><input type="tel" name="escola_telefone" class="form-control" value="<?= htmlspecialchars($cfg['escola_telefone']??'') ?>" placeholder="+244 900 000 000"></div>
        <div class="form-group"><label>Email Institucional</label><input type="email" name="escola_email" class="form-control" value="<?= htmlspecialchars($cfg['escola_email']??'') ?>" placeholder="info@escola.ao"></div>
      </div>
      <div class="form-group"><label>Website</label><input type="text" name="escola_website" class="form-control" value="<?= htmlspecialchars($cfg['escola_website']??'') ?>" placeholder="www.escola.ao"></div>
      <div style="display:flex;justify-content:flex-end;margin-top:8px">
        <button type="submit" class="btn btn-p"><i class="fas fa-save"></i> Guardar Contactos</button>
      </div>
    </form>
  </div>
</div>

<!-- ===== LOCALIZAÇÃO ===== -->
<?php elseif ($tab_ativa === 'localizacao'): ?>
<div class="card">
  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="acao" value="localizacao">
      <div class="form-group"><label>Morada Completa</label><textarea name="escola_morada" class="form-control" rows="2"><?= htmlspecialchars($cfg['escola_morada']??'') ?></textarea></div>
      <div class="form-row-3">
        <div class="form-group"><label>Município / Cidade</label><input type="text" name="escola_provincia" class="form-control" value="<?= htmlspecialchars($cfg['escola_provincia']??'') ?>"></div>
        <div class="form-group"><label>Província</label><input type="text" name="escola_provincia" class="form-control" value="<?= htmlspecialchars($cfg['escola_provincia']??'') ?>"></div>
        <div class="form-group"><label>País</label><input type="text" name="escola_pais" class="form-control" value="<?= htmlspecialchars($cfg['escola_pais']??'Angola') ?>"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Latitude GPS</label><input type="number" name="escola_latitude" step="any" class="form-control" value="<?= htmlspecialchars($cfg['escola_latitude']??'') ?>" placeholder="-8.8147"></div>
        <div class="form-group"><label>Longitude GPS</label><input type="number" name="escola_longitude" step="any" class="form-control" value="<?= htmlspecialchars($cfg['escola_longitude']??'') ?>" placeholder="13.2302"></div>
      </div>
      <?php if (!empty($cfg['escola_latitude']) && !empty($cfg['escola_longitude'])): ?>
      <div style="margin-bottom:16px">
        <div style="height:220px;border-radius:12px;overflow:hidden;border:1.5px solid var(--borda)">
          <iframe width="100%" height="100%" frameborder="0" style="border:0"
            src="https://maps.google.com/maps?q=<?= $cfg['escola_latitude'] ?>,<?= $cfg['escola_longitude'] ?>&output=embed"
            allowfullscreen loading="lazy"></iframe>
        </div>
      </div>
      <?php endif; ?>
      <div style="display:flex;justify-content:flex-end;margin-top:8px">
        <button type="submit" class="btn btn-p"><i class="fas fa-save"></i> Guardar Localização</button>
      </div>
    </form>
  </div>
</div>

<!-- ===== FINANCEIRO ===== -->
<?php elseif ($tab_ativa === 'financeiro'): ?>
<div class="card">
  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="acao" value="financeiro">
      <div class="form-row">
        <div class="form-group"><label>Valor Padrão da Propina</label><input type="number" name="propina_valor" class="form-control" value="<?= htmlspecialchars($cfg['propina_valor']??'15000') ?>"><div class="form-hint">Valor em Kwanzas (Kz)</div></div>
        <div class="form-group"><label>Dia de Vencimento</label><input type="number" name="propina_dia_venc" class="form-control" min="1" max="28" value="<?= htmlspecialchars($cfg['propina_dia_venc']??'10') ?>"><div class="form-hint">Dia do mês em que vence</div></div>
      </div>
      <div class="form-group">
        <label>Moeda</label>
        <select name="moeda" class="form-control">
          <option value="Kz" <?= ($cfg['moeda']??'Kz')==='Kz'?'selected':'' ?>>Kwanza (Kz)</option>
          <option value="EUR" <?= ($cfg['moeda']??'')==='EUR'?'selected':'' ?>>Euro (€)</option>
          <option value="USD" <?= ($cfg['moeda']??'')==='USD'?'selected':'' ?>>Dólar ($)</option>
        </select>
      </div>
      <div style="display:flex;justify-content:flex-end;margin-top:8px">
        <button type="submit" class="btn btn-p"><i class="fas fa-save"></i> Guardar Configurações</button>
      </div>
    </form>
  </div>
</div>

<!-- ===== SISTEMA ===== -->
<?php elseif ($tab_ativa === 'sistema'): ?>
<div class="grid-2" style="align-items:start">
  <div class="card">
    <div class="card-header"><div class="card-titulo"><i class="fas fa-cog"></i> Preferências do Sistema</div></div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="acao" value="sistema">
        <div style="display:flex;flex-direction:column;gap:14px">
          <div style="display:flex;align-items:center;justify-content:space-between;padding:14px;border:1.5px solid var(--borda);border-radius:10px">
            <div><div style="font-weight:700;font-size:.87rem">Notificações por Email</div><div style="font-size:.75rem;color:#94a3b8;margin-top:2px">Alertas de propinas e eventos</div></div>
            <label class="tg"><input type="checkbox" name="notif_email" <?= ($cfg['notif_email']??'0')==='1'?'checked':'' ?>><span class="tg-sl"></span></label>
          </div>
          <div style="display:flex;align-items:center;justify-content:space-between;padding:14px;border:1.5px solid var(--borda);border-radius:10px">
            <div><div style="font-weight:700;font-size:.87rem">Modo de Manutenção</div><div style="font-size:.75rem;color:#94a3b8;margin-top:2px">Bloqueia acesso a utilizadores comuns</div></div>
            <label class="tg"><input type="checkbox" name="modo_manutencao" <?= ($cfg['modo_manutencao']??'0')==='1'?'checked':'' ?>><span class="tg-sl"></span></label>
          </div>
        </div>
        <div style="margin-top:16px;display:flex;justify-content:flex-end">
          <button type="submit" class="btn btn-p"><i class="fas fa-save"></i> Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><div class="card-titulo"><i class="fas fa-key"></i> Alterar Senha</div></div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="acao" value="senha">
        <div class="form-group"><label>Senha Atual</label><input type="password" name="senha_atual" class="form-control" required></div>
        <div class="form-group"><label>Nova Senha</label><input type="password" name="nova_senha" class="form-control" required minlength="6"><div class="form-hint">Mínimo 6 caracteres</div></div>
        <div class="form-group"><label>Confirmar Nova Senha</label><input type="password" name="confirmar" class="form-control" required></div>
        <div style="display:flex;justify-content:flex-end">
          <button type="submit" class="btn btn-p"><i class="fas fa-key"></i> Alterar Senha</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require_once '../includes/rodape.php'; ?>
