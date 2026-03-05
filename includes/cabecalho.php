<?php
// includes/cabecalho.php
// Requer que $pagina_atual esteja definida antes de incluir
if (!isset($pagina_atual)) $pagina_atual = '';
$cfg = allCfg();
$cor_p  = $cfg['cor_primaria']   ?? '#1a3a5c';
$cor_s  = $cfg['cor_secundaria'] ?? '#e8a020';
$cor_sb = $cfg['cor_sidebar']    ?? '#0f2540';
$nome_escola  = $cfg['escola_nome']   ?? 'Escola Nova Geração';
$slogan_escola= $cfg['escola_slogan'] ?? 'Painel Admin';
$logo_escola  = $cfg['escola_logo']   ?? '';
$user_nome    = $_SESSION['user_nome'] ?? 'Admin';
$user_tipo    = $_SESSION['user_tipo'] ?? 'admin';
$user_inicial = strtoupper(substr($user_nome,0,1));

// Notificações não lidas
$notif_count = contagem('notificacoes',"lida=0 AND utilizador_id=".(int)($_SESSION['uid']??0));

$nav = [
    ['slug'=>'dashboard',    'label'=>'Dashboard',        'icone'=>'fas fa-th-large',      'url'=>'dashboard.php'],
    ['slug'=>'alunos',       'label'=>'Alunos',           'icone'=>'fas fa-user-graduate',  'url'=>'paginas/alunos.php',       'mod'=>'alunos'],
    ['slug'=>'turmas',       'label'=>'Turmas',           'icone'=>'fas fa-chalkboard',     'url'=>'paginas/turmas.php',       'mod'=>'turmas'],
    ['slug'=>'agenda',       'label'=>'Agenda',           'icone'=>'fas fa-calendar-alt',   'url'=>'paginas/agenda.php',       'mod'=>'agenda'],
    ['slug'=>'financas',     'label'=>'Finanças',         'icone'=>'fas fa-coins',          'url'=>'paginas/financas.php',     'mod'=>'financas'],
    ['slug'=>'configuracoes','label'=>'Configurações',    'icone'=>'fas fa-cog',            'url'=>'paginas/configuracoes.php'],
    ['slug'=>'modulos',      'label'=>'Módulos',          'icone'=>'fas fa-puzzle-piece',   'url'=>'paginas/modulos.php'],
];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($titulo_pagina ?? 'Painel') ?> — <?= htmlspecialchars($nome_escola) ?></title>
<?php if ($logo_escola): ?>
<link rel="icon" href="/betilson/sistema_escolar/<?= htmlspecialchars($logo_escola) ?>">
<?php endif; ?>
 
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --p:<?= $cor_p ?>;
  --s:<?= $cor_s ?>;
  --sb:<?= $cor_sb ?>;
  --fundo:#f0f4f8;
  --branco:#fff;
  --borda:#e2e8f0;
  --sombra:0 4px 24px rgba(0,0,0,0.07);
  --raio:14px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'DM Sans',sans-serif;background:var(--fundo);min-height:100vh;display:flex;}

/* SIDEBAR */
.sidebar{width:256px;min-height:100vh;background:var(--sb);display:flex;flex-direction:column;position:fixed;left:0;top:0;bottom:0;z-index:100;}
.sb-logo{padding:24px 20px 18px;border-bottom:1px solid rgba(255,255,255,0.07);display:flex;align-items:center;gap:12px;}
.sb-logo-ic{width:42px;height:42px;background:var(--s);border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:18px;color:#fff;flex-shrink:0;overflow:hidden;}
.sb-logo-ic img{width:100%;height:100%;object-fit:cover;}
.sb-logo-txt .nome{font-family:'Playfair Display',serif;color:#fff;font-size:0.92rem;line-height:1.2;}
.sb-logo-txt .sub{color:rgba(255,255,255,0.4);font-size:0.7rem;}
.sb-nav{flex:1;padding:12px 0;overflow-y:auto;}
.sb-grupo{padding:0 14px;margin-bottom:2px;}
.sb-grupo-label{font-size:0.63rem;text-transform:uppercase;letter-spacing:1.2px;color:rgba(255,255,255,0.28);padding:10px 8px 5px;font-weight:700;}
.sb-item{display:flex;align-items:center;gap:11px;padding:9px 12px;border-radius:9px;color:rgba(255,255,255,0.6);font-size:0.85rem;font-weight:500;cursor:pointer;transition:all .2s;text-decoration:none;margin-bottom:2px;}
.sb-item:hover{background:rgba(255,255,255,0.07);color:#fff;}
.sb-item.ativo{background:var(--s);color:#fff;box-shadow:0 4px 14px rgba(232,160,32,0.3);}
.sb-item i{width:17px;text-align:center;font-size:0.88rem;}
.sb-user{padding:14px 18px;border-top:1px solid rgba(255,255,255,0.07);display:flex;align-items:center;gap:11px;}
.sb-avatar{width:36px;height:36px;background:var(--s);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;color:#fff;font-weight:700;flex-shrink:0;}
.sb-user-info .nome{color:#fff;font-size:0.82rem;font-weight:600;}
.sb-user-info .cargo{color:rgba(255,255,255,0.38);font-size:0.7rem;}
.sb-sair{margin-left:auto;color:rgba(255,255,255,0.35);font-size:0.85rem;cursor:pointer;transition:color .2s;text-decoration:none;}
.sb-sair:hover{color:#f87171;}

/* MAIN */
.main{margin-left:256px;min-height:100vh;display:flex;flex-direction:column;width:calc(100% - 256px);}
.topbar{background:var(--branco);padding:0 28px;height:64px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--borda);position:sticky;top:0;z-index:50;}
.topbar-titulo{font-family:'Playfair Display',serif;font-size:1.3rem;color:var(--p);}
.topbar-acoes{display:flex;align-items:center;gap:10px;}
.btn-ic{width:36px;height:36px;border-radius:9px;border:1.5px solid var(--borda);background:var(--fundo);display:flex;align-items:center;justify-content:center;cursor:pointer;color:#64748b;transition:all .2s;position:relative;text-decoration:none;}
.btn-ic:hover{background:var(--p);color:#fff;border-color:var(--p);}
.notif-badge{position:absolute;top:-5px;right:-5px;width:17px;height:17px;background:#ef4444;border-radius:50%;font-size:0.6rem;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;}
.conteudo{flex:1;padding:24px 28px;}

/* CARDS */
.card{background:var(--branco);border-radius:var(--raio);box-shadow:var(--sombra);}
.card-header{padding:18px 22px 14px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--borda);}
.card-titulo{font-weight:700;color:#1e293b;font-size:0.9rem;display:flex;align-items:center;gap:9px;}
.card-titulo i{color:var(--p);}
.card-body{padding:18px 22px;}

/* BOTÕES */
.btn{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:9px;font-family:'DM Sans',sans-serif;font-size:0.82rem;font-weight:600;cursor:pointer;border:none;transition:all .2s;text-decoration:none;}
.btn-p{background:var(--p);color:#fff;}
.btn-p:hover{filter:brightness(1.1);color:#fff;}
.btn-s{background:var(--s);color:#fff;}
.btn-s:hover{filter:brightness(1.1);color:#fff;}
.btn-out{background:transparent;border:1.5px solid var(--borda);color:#64748b;}
.btn-out:hover{border-color:var(--p);color:var(--p);}
.btn-danger{background:#ef4444;color:#fff;}
.btn-danger:hover{background:#dc2626;color:#fff;}
.btn-success{background:#22c55e;color:#fff;}
.btn-sm{padding:6px 13px;font-size:0.78rem;}

/* STATS */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:24px;}
.stat-card{background:var(--branco);border-radius:var(--raio);padding:22px;box-shadow:var(--sombra);position:relative;overflow:hidden;}
.stat-card::after{content:'';position:absolute;bottom:0;left:0;right:0;height:3px;}
.stat-azul::after{background:var(--p);}
.stat-ouro::after{background:var(--s);}
.stat-verde::after{background:#22c55e;}
.stat-red::after{background:#ef4444;}
.stat-ic{width:46px;height:46px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;margin-bottom:12px;}
.stat-azul .stat-ic{background:rgba(26,58,92,0.1);color:var(--p);}
.stat-ouro .stat-ic{background:rgba(232,160,32,0.15);color:var(--s);}
.stat-verde .stat-ic{background:rgba(34,197,94,0.1);color:#22c55e;}
.stat-red .stat-ic{background:rgba(239,68,68,0.1);color:#ef4444;}
.stat-val{font-size:1.9rem;font-weight:700;color:#1e293b;font-family:'Playfair Display',serif;line-height:1;}
.stat-label{font-size:0.78rem;color:#94a3b8;font-weight:500;margin-top:4px;}

/* TABELA */
.tabela{width:100%;border-collapse:collapse;font-size:0.84rem;}
.tabela th{padding:11px 14px;text-align:left;font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;background:#f8fafc;border-bottom:1px solid var(--borda);}
.tabela td{padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#374151;vertical-align:middle;}
.tabela tr:last-child td{border-bottom:none;}
.tabela tbody tr:hover td{background:#f8fafc;}

/* BADGES */
.badge{padding:3px 10px;border-radius:6px;font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;}
.badge-verde{background:#dcfce7;color:#166534;}
.badge-amarelo{background:#fef9c3;color:#854d0e;}
.badge-vermelho{background:#fee2e2;color:#991b1b;}
.badge-azul{background:#dbeafe;color:#1e40af;}
.badge-roxo{background:#f3e8ff;color:#6b21a8;}

/* FORM */
.form-group{margin-bottom:18px;}
.form-group label{display:block;font-size:0.78rem;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.3px;}
.form-control{width:100%;padding:10px 13px;border:2px solid var(--borda);border-radius:9px;font-family:'DM Sans',sans-serif;font-size:0.88rem;color:#111827;background:#fff;outline:none;transition:border-color .2s;}
.form-control:focus{border-color:var(--p);}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.form-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;}
.form-hint{font-size:0.72rem;color:#94a3b8;margin-top:4px;}

/* TOGGLE */
.tg{position:relative;width:42px;height:22px;flex-shrink:0;}
.tg input{opacity:0;width:0;height:0;}
.tg-sl{position:absolute;cursor:pointer;inset:0;background:#e2e8f0;border-radius:22px;transition:.3s;}
.tg-sl::before{content:'';position:absolute;width:16px;height:16px;background:#fff;border-radius:50%;left:3px;top:3px;transition:.3s;box-shadow:0 1px 3px rgba(0,0,0,0.2);}
input:checked+.tg-sl{background:var(--p);}
input:checked+.tg-sl::before{transform:translateX(20px);}

/* MODAL */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:1000;display:none;align-items:center;justify-content:center;}
.modal-overlay.aberto{display:flex;}
.modal{background:#fff;border-radius:18px;width:100%;max-width:500px;max-height:90vh;overflow-y:auto;box-shadow:0 24px 80px rgba(0,0,0,0.2);}
.modal-header{padding:22px 26px 16px;border-bottom:1px solid var(--borda);display:flex;align-items:center;justify-content:space-between;}
.modal-titulo{font-family:'Playfair Display',serif;font-size:1.15rem;color:var(--p);}
.modal-fechar{cursor:pointer;color:#94a3b8;font-size:1rem;background:none;border:none;}
.modal-body{padding:22px 26px;}
.modal-footer{padding:14px 26px 22px;display:flex;gap:9px;justify-content:flex-end;border-top:1px solid var(--borda);}

/* ACOES */
.btn-acao{width:28px;height:28px;border-radius:7px;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:0.75rem;transition:all .2s;}
.btn-editar{background:rgba(26,58,92,0.1);color:var(--p);}
.btn-editar:hover{background:var(--p);color:#fff;}
.btn-apagar{background:rgba(239,68,68,0.1);color:#ef4444;}
.btn-apagar:hover{background:#ef4444;color:#fff;}

/* GRID */
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:24px;}
.grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-bottom:24px;}

/* PROGRESSO */
.prog{height:7px;background:#e2e8f0;border-radius:4px;overflow:hidden;}
.prog-bar{height:100%;border-radius:4px;}

/* ALERTA */
.alerta{padding:12px 16px;border-radius:10px;display:flex;align-items:center;gap:10px;font-size:0.86rem;margin-bottom:14px;}
.alerta-info{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;}
.alerta-aviso{background:#fffbeb;border:1px solid #fde68a;color:#92400e;}
.alerta-erro{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;}
.alerta-ok{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;}

@media(max-width:900px){
  .sidebar{width:60px;}
  .sb-logo-txt,.sb-item span,.sb-user-info{display:none;}
  .main{margin-left:60px;width:calc(100% - 60px);}
  .stats-grid{grid-template-columns:repeat(2,1fr);}
  .grid-2,.grid-3,.form-row,.form-row-3{grid-template-columns:1fr;}
}
/* Estilo Base do Menu Lateral/Navegação */
.nav-admin {
    display: flex;
    flex-direction: column;
    gap: 5px;
    padding: 15px;
}

.nav-admin a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    color: #ffffff; /* Texto branco sobre fundo azul */
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

/* Efeito ao passar o rato (Hover) */
.nav-admin a:hover {
    background: rgba(232, 160, 32, 0.1); /* Fundo dourado muito suave */
    color: #e8a020; /* Texto muda para dourado */
    padding-left: 25px; /* Pequeno movimento */
}

/* Estilo para a Página Ativa (Active) */
.nav-admin a.active {
    background: rgba(232, 160, 32, 0.2); /* Destaque dourado */
    color: #e8a020; /* Cor Dourada do Logo */
    border-left: 4px solid #e8a020; /* Barra lateral dourada */
    font-weight: 700;
}

/* Estilo dos Ícones */
.nav-admin a i {
    font-size: 1.1rem;
    width: 20px;
    text-align: center;
}

.nav-admin a.active i {
    color: #e8a020;
}

</style>
</head>
<body>
 <aside class="sidebar">
  <div class="sb-logo">
    <div class="sb-logo-ic">
      <?php if ($logo_escola): ?>
        <img src="/betilson/sistema_escolar/<?= htmlspecialchars($logo_escola) ?>" alt="Logo">
       <?php else: ?>
        <i class="fas fa-graduation-cap"></i>
      <?php endif; ?>
    </div>
    <div class="sb-logo-txt">
      <div class="nome"><?= htmlspecialchars($nome_escola) ?></div>
      <div class="sub">Painel Admin</div>
    </div>
  </div>

  <nav class="sb-nav">
    <div class="sb-grupo">
      <div class="sb-grupo-label">Principal</div>
      
      <!-- Loop de Navegação Dinâmico -->
      <?php foreach ($nav as $item):
        if (!empty($item['mod']) && !modAtivo($item['mod'])) continue;
        $ativo = ($pagina_atual === $item['slug']) ? 'ativo' : '';
        $url_nav = defined('RAIZ') ? $item['url'] : '../'.$item['url'];
      ?>
      <a class="sb-item <?= $ativo ?>" href="<?= $url_nav ?>">
        <i class="<?= $item['icone'] ?>"></i><span><?= $item['label'] ?></span>
      </a>

      <?php if ($item['slug'] === 'dashboard'): ?>
        </div><div class="sb-grupo"><div class="sb-grupo-label">Gestão</div>
        
        <!-- NOVOS BOTÕES DE NOTAS E ESTATÍSTICAS (Inseridos no grupo Gestão) -->
        <a class="sb-item <?= ($pagina_atual == 'notas') ? 'ativo' : '' ?>" href="<?= defined('RAIZ') ? 'paginas/admin_notas.php' : 'admin_notas.php' ?>">
            <i class="fas fa-certificate"></i><span>Consultar Boletins</span>
        </a>
        <a class="sb-item <?= ($pagina_atual == 'estatisticas') ? 'ativo' : '' ?>" href="<?= defined('RAIZ') ? 'paginas/admin_estatisticas.php' : 'admin_estatisticas.php' ?>">
            <i class="fas fa-chart-line"></i><span>Estatísticas</span>
        </a>
      <?php endif; ?>

      <?php if ($item['slug'] === 'financas'): ?>
        </div><div class="sb-grupo"><div class="sb-grupo-label">Sistema</div>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <?php if ($_SESSION['user_tipo'] === 'admin'): ?>
      <div class="sb-grupo">
        <div class="sb-grupo-label">Atalhos</div>
        <a class="sb-item" href="<?= defined('RAIZ') ? 'paginas/professores.php' : 'professores.php' ?>">
          <i class="fas fa-plus"></i><span>Novo Professor</span>
        </a>
      </div>
    <?php endif; ?>
  </nav>

  <!-- Rodapé da Sidebar (Usuário) -->
  <div class="sb-user">
    <div class="sb-avatar"><?= $user_inicial ?></div>
    <div class="sb-user-info">
      <div class="nome"><?= htmlspecialchars($user_nome) ?></div>
      <div class="cargo"><?= ucfirst($user_tipo) ?></div>
    </div>
    <a class="sb-sair" href="<?= defined('RAIZ') ? 'logout.php' : '../logout.php' ?>" title="Sair">
      <i class="fas fa-sign-out-alt"></i>
    </a>
  </div>
</aside>


<main class="main">
  <div class="topbar">
    <div class="topbar-titulo"><?= htmlspecialchars($titulo_pagina ?? 'Painel') ?></div>
    <div class="topbar-acoes">
      <?php if ($notif_count > 0): ?>
      <a class="btn-ic" href="<?= defined('RAIZ') ? 'paginas/notificacoes.php' : 'notificacoes.php' ?>" title="Notificações">
        <i class="fas fa-bell"></i>
        <span class="notif-badge"><?= $notif_count ?></span>
      </a>
      <?php endif; ?>
      <a class="btn-ic" href="<?= defined('RAIZ') ? 'paginas/configuracoes.php' : 'configuracoes.php' ?>" title="Configurações">
        <i class="fas fa-cog"></i>
      </a>
    </div>
  </div>
  <div class="conteudo">


