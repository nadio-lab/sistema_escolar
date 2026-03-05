<?php
require_once 'includes/conexao.php';

// Se já estiver logado, redireciona para a área correspondente
if (!empty($_SESSION['uid'])) {
    if ($_SESSION['user_tipo'] === 'professor') redir('paginas/professor_turmas.php');
    elseif ($_SESSION['user_tipo'] === 'aluno') redir('paginas/dashboard_aluno.php');
    else redir('dashboard.php');
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = limpar($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if ($email && $senha) {
        // 1. TENTA LOGIN NA TABELA UTILIZADORES (Admin/Professor)
        $stmt = $conn->prepare("SELECT id, nome, senha, tipo FROM utilizadores WHERE email=? AND ativo=1 LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows > 0) {
            $u = $res->fetch_assoc();
            if (password_verify($senha, $u['senha'])) {
                $_SESSION['uid']       = $u['id'];
                $_SESSION['user_nome'] = $u['nome'];
                $_SESSION['user_tipo'] = $u['tipo'];
                
                $conn->query("UPDATE utilizadores SET ultimo_login=NOW() WHERE id={$u['id']}");

                if ($u['tipo'] === 'professor') {
                    redir('paginas/professor_turmas.php', 'Bem-vindo, Professor '.$u['nome']);
                } else {
                    redir('dashboard.php', 'Painel Administrativo: '.$u['nome']);
                }
                exit;
            }
        }

        // 2. TENTA LOGIN NA TABELA ALUNOS (Usando email_aluno e senha_acesso)
        $stmt_aluno = $conn->prepare("SELECT id, nome_completo, senha_acesso FROM alunos WHERE email_aluno=? AND estado='ativo' LIMIT 1");
        $stmt_aluno->bind_param("s", $email);
        $stmt_aluno->execute();
        $res_aluno = $stmt_aluno->get_result();

        if ($res_aluno->num_rows > 0) {
            $al = $res_aluno->fetch_assoc();
  
   // Verifica a senha usando o campo correto: senha_acesso
            if (password_verify($senha, $al['senha_acesso'])) {
                $_SESSION['uid']       = $al['id'];
                $_SESSION['aluno_id']  = $al['id'];
                $_SESSION['user_nome'] = $al['nome_completo'];
                $_SESSION['user_tipo'] = 'aluno';
                
                redir('paginas/dashboard_aluno.php', 'Olá '.$al['nome_completo'].', bem-vindo ao teu portal!');
                exit;
            }
        }

        $erro = 'E-mail ou senha incorretos.';
    } else {
        $erro = 'Preencha todos os campos.';
    }
}
// Configurações Visuais do Colégio Visão do Futuro
$cfg = allCfg();
$cor_p = $cfg['cor_primaria']   ?? '#1a3a5c';
$cor_s = $cfg['cor_secundaria'] ?? '#e8a020';
$cor_sb= $cfg['cor_sidebar']    ?? '#0f2540';
$nome  = $cfg['escola_nome']    ?? 'Escola Nova Geração';
$slogan= $cfg['escola_slogan']  ?? 'Educando para o futuro';
$logo  = $cfg['escola_logo']    ?? '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Entrar — <?= htmlspecialchars($nome) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Lógica corrigida para o Favicon -->
    <?php if (!empty($logo)): ?>
        <link rel="shortcut icon" href="<?= htmlspecialchars($logo) ?>" type="image/x-icon">
    <?php else: ?>
        <link rel="shortcut icon" href="./uploads/logos/<?= htmlspecialchars($logo) ?>" type="image/x-icon">
    <?php endif; ?>
<style>
:root{--p:<?= $cor_p ?>;--s:<?= $cor_s ?>;--sb:<?= $cor_sb ?>;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'DM Sans',sans-serif;min-height:100vh;display:grid;grid-template-columns:1fr 1fr;background:#f0f4f8;}
.esq{background:var(--sb);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 50px;position:relative;overflow:hidden;}
.esq::before{content:'';position:absolute;width:360px;height:360px;border-radius:50%;background:rgba(255,255,255,0.03);top:-80px;right:-80px;}
.esq::after{content:'';position:absolute;width:260px;height:260px;border-radius:50%;background:rgba(255,255,255,0.03);bottom:-60px;left:-60px;}
.logo-area{text-align:center;z-index:2;}
.logo-ic{width:88px;height:88px;background:var(--s);border-radius:22px;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:38px;color:#fff;box-shadow:0 16px 50px rgba(0,0,0,0.25);overflow:hidden;}
.logo-ic img{width:100%;height:100%;object-fit:cover;}
.escola-nome{font-family:'Playfair Display',serif;font-size:2rem;color:#fff;line-height:1.2;margin-bottom:10px;}
.escola-slogan{color:rgba(255,255,255,0.55);font-size:0.92rem;font-weight:300;}
.stats-dec{margin-top:56px;display:flex;gap:14px;z-index:2;}
.st-card{background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:14px 18px;text-align:center;color:#fff;}
.st-card .n{font-size:1.5rem;font-weight:700;color:var(--s);font-family:'Playfair Display',serif;}
.st-card .l{font-size:0.68rem;color:rgba(255,255,255,0.45);margin-top:2px;}
.dir{display:flex;align-items:center;justify-content:center;padding:60px 50px;}
.form-box{width:100%;max-width:390px;}
.form-titulo{font-family:'Playfair Display',serif;font-size:1.8rem;color:var(--p);margin-bottom:6px;}
.form-sub{color:#6b7280;font-size:0.9rem;margin-bottom:32px;}
.campo{margin-bottom:18px;}
.campo label{display:block;font-size:0.78rem;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.3px;}
.iw{position:relative;}
.iw i.ic-esq{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:.9rem;}
.iw input{width:100%;padding:12px 14px 12px 42px;border:2px solid #e5e7eb;border-radius:11px;font-family:'DM Sans',sans-serif;font-size:.9rem;color:#111827;background:#fff;outline:none;transition:border-color .2s;}
.iw input:focus{border-color:var(--p);box-shadow:0 0 0 3px rgba(26,58,92,.09);}
.tg-senha{position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#9ca3af;background:none;border:none;font-size:.9rem;}
.btn-login{width:100%;padding:13px;background:var(--p);color:#fff;border:none;border-radius:11px;font-family:'DM Sans',sans-serif;font-size:.95rem;font-weight:700;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:9px;margin-top:6px;}
.btn-login:hover{filter:brightness(1.1);transform:translateY(-1px);box-shadow:0 8px 20px rgba(26,58,92,.3);}
.alerta-erro{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:9px;padding:11px 14px;margin-bottom:18px;font-size:.87rem;display:flex;align-items:center;gap:9px;}
.rodape-login{margin-top:36px;padding-top:18px;border-top:1px solid #e5e7eb;text-align:center;color:#9ca3af;font-size:.78rem;}
.info-acesso{background:#f0f9ff;border:1px solid #bae6fd;border-radius:9px;padding:10px 14px;font-size:.8rem;color:#0369a1;margin-bottom:18px;}
@media(max-width:768px){body{grid-template-columns:1fr;}.esq{padding:40px 24px;min-height:220px;}.stats-dec{display:none;}.dir{padding:40px 24px;}}
       .esqueceu-link {
    display: block;
    text-align: right;
    margin-top: -10px;
    margin-bottom: 20px;
    font-size: 0.8rem;
    color: var(--p);
    text-decoration: none;
    font-weight: 500;
}
.esqueceu-link:hover { text-decoration: underline; }

</style>
</head>
<body>
<div class="esq">
  <div class="logo-area">
    <div class="logo-ic">
      <?php if ($logo): ?><img src="<?= htmlspecialchars($logo) ?>" alt="Logo">
      <?php else: ?><i class="fas fa-graduation-cap"></i><?php endif; ?>
    </div>
    <div class="escola-nome"><?= htmlspecialchars($nome) ?></div>
    <div class="escola-slogan"><?= htmlspecialchars($slogan) ?></div>
  </div>
  <div class="stats-dec">
    <div class="st-card"><div class="n"><?= contagem('alunos','estado="ativo"') ?></div><div class="l">Alunos</div></div>
    <div class="st-card"><div class="n"><?= contagem('turmas','ativo=1') ?></div><div class="l">Turmas</div></div>
    <div class="st-card"><div class="n"><?= contagem('atividades','data_inicio>=CURDATE()') ?></div><div class="l">Eventos</div></div>
  </div>
</div>
<div class="dir">
  <div class="form-box">
    <div class="form-titulo">Entrar no sistema</div>
    <div class="form-sub">Aceda ao painel de gestão escolar</div>
    <div class="info-acesso"><i class="fas fa-info-circle me-1"></i><strong>Acesso padrão para:</strong> Administradores / Professores</div>
    <?php if ($erro): ?><div class="alerta-erro"><i class="fas fa-exclamation-circle"></i><?= $erro ?></div><?php endif; ?>
    <form method="POST">
      <div class="campo">
        <label>Email</label>
        <div class="iw">
          <i class="fas fa-envelope ic-esq"></i>
          <input type="email" name="email" placeholder="admin@escola.ao" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
        </div>
      </div>
      <div class="campo">
        <label>Senha</label>
        <div class="iw">
          <i class="fas fa-lock ic-esq"></i>
          <input type="password" name="senha" id="senha" placeholder="••••••••" required>
          <button type="button" class="tg-senha" onclick="tgSenha()"><i class="fas fa-eye" id="ic-senha"></i></button>
        </div>
      </div>
      <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> Entrar</button>
      <br>
      <a href="esqueci-senha.php" class="esqueceu-link">Esqueceu a senha?</a>
    </form>
    <div class="rodape-login">&copy; <?= date('Y') ?> <?= htmlspecialchars($nome) ?></div>
    
       <!-- ADICIONE ESTA LINHA ABAIXO -->

 
 
 
  </div>
</div>


<script>
function tgSenha(){
  const s=document.getElementById('senha'),i=document.getElementById('ic-senha');
  s.type=s.type==='password'?'text':'password';
  i.className=s.type==='password'?'fas fa-eye':'fas fa-eye-slash';
}
</script>
</body>
</html>
