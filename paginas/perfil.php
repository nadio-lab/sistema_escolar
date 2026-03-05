<?php
require_once '../includes/conexao.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// SEGURANÇA: Bloqueia quem não é professor
if (!isset($_SESSION['uid']) || $_SESSION['user_tipo'] !== 'professor') {
    header("Location: ../login.php");
    exit;
}

$professor_id = $_SESSION['uid'];
$msg = "";
$tipo_msg = "success";

// 1. Processar a Alteração
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_novo = limpar($_POST['nome']);
    $senha_atual = $_POST['senha_atual'];
    $senha_nova = $_POST['senha_nova'];

    // Buscar a senha atual na BD para validar
    $user = $conn->query("SELECT senha FROM utilizadores WHERE id = $professor_id")->fetch_assoc();

    if (password_verify($senha_atual, $user['senha'])) {
        // Senha atual correta, vamos atualizar
        if (!empty($senha_nova)) {
            $senha_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
            $conn->query("UPDATE utilizadores SET nome = '$nome_novo', senha = '$senha_hash' WHERE id = $professor_id");
        } else {
            $conn->query("UPDATE utilizadores SET nome = '$nome_novo' WHERE id = $professor_id");
        }
        $_SESSION['user_nome'] = $nome_novo; // Atualiza o nome na sessão
        $msg = "Perfil atualizado com sucesso!";
    } else {
        $msg = "A senha atual está incorreta.";
        $tipo_msg = "danger";
    }
}

// 2. Buscar dados atuais do professor
$dados = $conn->query("SELECT nome, email FROM utilizadores WHERE id = $professor_id")->fetch_assoc();

$cfg = allCfg();
$logo = $cfg['escola_logo'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Meu Perfil — Painel do Professor</title>
    <?php if ($logo): ?>
<link rel="icon" href="/betilson/sistema_escolar/<?= htmlspecialchars($logo) ?>">
<?php endif; ?>
 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com">
    <style>
        :root { --azul: #1a3a5c; --ouro: #e8a020; --fundo: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; background: var(--fundo); }
        .nav-prof { background: var(--azul); color: white; padding: 10px 5%; display: flex; justify-content: space-between; align-items: center; }
        .nav-logo img { height: 45px; }
        .container { padding: 30px 5%; max-width: 600px; margin: auto; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: var(--azul); font-size: 0.9rem; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; background: #fafafa; }
        .form-control:focus { border-color: var(--ouro); outline: none; background: #fff; }
        .btn-update { background: var(--azul); color: white; border: none; padding: 12px; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; transition: 0.3s; }
        .btn-update:hover { background: var(--ouro); color: var(--azul); }
        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-size: 0.9rem; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    </style>
</head>
<body>

    <nav class="nav-prof">
        <div class="nav-logo">
            <img src="/betilson/sistema_escolar/<?= $logo ?>" alt="Logo">
        </div>
        <div style="font-weight: bold;">MEU PERFIL / CONFIGURAÇÕES</div>
        <a href="professor_turmas.php" style="color: white; text-decoration: none;"><i class="fas fa-arrow-left"></i> Voltar</a>
    </nav>

    <div class="container">
        <div class="card">
            <?php if($msg): ?>
                <div class="alert alert-<?= $tipo_msg ?>"><?= $msg ?></div>
            <?php endif; ?>

            <form method="POST">
                <div style="text-align: center; margin-bottom: 25px;">
                    <i class="fas fa-user-circle" style="font-size: 4rem; color: var(--azul);"></i>
                    <p style="color: #666; font-size: 0.85rem; margin-top: 5px;"><?= $dados['email'] ?></p>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nome de Exibição:</label>
                    <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($dados['nome']) ?>" required>
                </div>

                <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">
                <h4 style="color: var(--ouro); margin-bottom: 15px;"><i class="fas fa-lock"></i> Alterar Senha</h4>

                <div class="form-group">
                    <label>Senha Atual:</label>
                    <input type="password" name="senha_atual" class="form-control" required placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label>Nova Senha (deixe vazio para não alterar):</label>
                    <input type="password" name="senha_nova" class="form-control" placeholder="Mínimo 6 caracteres">
                </div>

                <button type="submit" class="btn-update">
                    <i class="fas fa-save"></i> Guardar Alterações
                </button>
            </form>
        </div>
    </div>

</body>
</html>
