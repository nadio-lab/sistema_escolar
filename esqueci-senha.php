 <?php
require_once 'includes/conexao.php';
$erro = ''; $sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = limpar($_POST['email'] ?? '');
    if ($email) {
        $stmt = $conn->prepare("SELECT id FROM utilizadores WHERE email=? AND ativo=1 LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            // Gerar token único e validade de 1 hora
            $token = bin2hex(random_bytes(32));
            $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));

            $ins = $conn->prepare("INSERT INTO recuperacao_senhas (email, token, expira) VALUES (?, ?, ?)");
            $ins->bind_param("sss", $email, $token, $expira);
            $ins->execute();

            // Link que o utilizador deve clicar
            $link = "redefinir-senha.php?token=" . $token;
            
            // Simulação de envio (Num projeto real usarias mail() ou PHPMailer)
            $sucesso = "Token gerado! Use este link: <a href='$link'>Redefinir Agora</a>";
        } else { $erro = "Email não encontrado."; }
    }
}
// ... (resto do HTML do esqueci-senha.php enviado antes)
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha — <?= htmlspecialchars($cfg['escola_nome'] ?? 'Escola') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com" rel="stylesheet">
    <style>
        :root{--p:<?= $cor_p ?>;}
        body{font-family:'DM Sans',sans-serif; background:#f0f4f8; display:flex; align-items:center; justify-content:center; height:100vh; margin:0;}
        .form-box{width:100%; max-width:400px; background:#fff; padding:40px; border-radius:15px; box-shadow:0 10px 25px rgba(0,0,0,0.05);}
        .titulo{font-family:'Playfair Display',serif; font-size:1.8rem; color:var(--p); margin-bottom:10px;}
        .sub{color:#6b7280; font-size:0.9rem; margin-bottom:25px;}
        .campo{margin-bottom:20px;}
        .campo label{display:block; font-size:0.8rem; font-weight:700; margin-bottom:8px; color:#374151;}
        .campo input{width:100%; padding:12px; border:2px solid #e5e7eb; border-radius:10px; outline:none; box-sizing:border-box;}
        .btn{width:100%; padding:13px; background:var(--p); color:#fff; border:none; border-radius:10px; font-weight:700; cursor:pointer;}
        .alerta{padding:12px; border-radius:8px; margin-bottom:15px; font-size:0.85rem;}
        .erro{background:#fef2f2; color:#dc2626; border:1px solid #fecaca;}
        .sucesso{background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0;}
        .voltar{display:block; text-align:center; margin-top:20px; color:var(--p); text-decoration:none; font-size:0.85rem;}

    </style>
</head>
<body>
    <div class="form-box">
        <div class="titulo">Recuperar Senha</div>
        <p class="sub">Introduza o seu email para receber um link de reposição.</p>
        
        <?php if($erro): ?><div class="alerta erro"><?= $erro ?></div><?php endif; ?>
        <?php if($sucesso): ?><div class="alerta sucesso"><?= $sucesso ?></div><?php endif; ?>

        <form method="POST">
            <div class="campo">
                <label>Email de Registo</label>
                <input type="email" name="email" required placeholder="exemplo@escola.ao">
            </div>
            <button type="submit" class="btn">Enviar Link</button>
        </form>
        <a href="login.php" class="voltar"><i class="fas fa-arrow-left"></i> Voltar ao login</a>
    </div>
   
</body>
</html>
