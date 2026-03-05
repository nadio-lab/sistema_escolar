<?php
require_once 'includes/conexao.php';
$token = $_GET['token'] ?? '';
$erro = ''; $sucesso = '';

// Validar se o token existe e não expirou
$stmt = $conn->prepare("SELECT email FROM recuperacao_senhas WHERE token=? AND expira > NOW() AND usado=0 LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) die("Token inválido ou expirado.");
$dados = $res->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_senha = $_POST['senha'] ?? '';
    if (strlen($nova_senha) >= 6) {
        $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        
        // 1. Atualiza a senha do utilizador
        $upd = $conn->prepare("UPDATE utilizadores SET senha=? WHERE email=?");
        $upd->bind_param("ss", $hash, $dados['email']);
        
        if ($upd->execute()) {
            // 2. Invalida o token
            $conn->query("UPDATE recuperacao_senhas SET usado=1 WHERE token='$token'");
            $sucesso = "Senha alterada com sucesso! <a href='login.php'>Fazer login</a>";
        }
    } else { $erro = "A senha deve ter pelo menos 6 caracteres."; }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Nova Senha</title>
    <style> /* Podes usar o mesmo estilo do login.php aqui */ </style>
</head>
<body>
    <div style="max-width:400px; margin:50px auto; font-family:sans-serif;">
        <h2>Nova Senha</h2>
        <?php if($sucesso) echo "<p style='color:green'>$sucesso</p>"; ?>
        <?php if($erro) echo "<p style='color:red'>$erro</p>"; ?>
        
        <?php if(!$sucesso): ?>
        <form method="POST">
            <input type="password" name="senha" placeholder="Digite a nova senha" required style="width:100%; padding:10px; margin-bottom:10px;">
            <button type="submit" style="width:100%; padding:10px; background:#1a3a5c; color:#fff; border:none; cursor:pointer;">Atualizar Senha</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
