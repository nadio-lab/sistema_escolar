 <?php
require_once '../includes/conexao.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// SEGURANÇA: Bloqueia quem não é professor
if (!isset($_SESSION['uid']) || $_SESSION['user_tipo'] !== 'professor') {
    header("Location: ../login.php");
    exit;
}

$professor_id = $_SESSION['uid'];
$nome_prof = $_SESSION['user_nome'];

// Puxar dados da escola para o cabeçalho
$cfg = allCfg();
$logo = $cfg['escola_logo'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Painel do Professor — Colégio Visão do Futuro</title>
    <!-- FontAwesome Corrigido para os ícones aparecerem -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com">
 <?php if ($logo): ?>
<link rel="icon" href="/betilson/sistema_escolar/<?= htmlspecialchars($logo) ?>">
<?php endif; ?>

    <style>
        :root { --azul: #1a3a5c; --ouro: #e8a020; --fundo: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; background: var(--fundo); }
        
        /* CABEÇALHO EXCLUSIVO */
        .nav-prof { background: var(--azul); color: white; padding: 10px 5%; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.2); }
        .nav-logo { display: flex; align-items: center; gap: 15px; }
        .nav-logo img { height: 50px; }
        
        .menu-prof { display: flex; gap: 20px; list-style: none; margin: 0; padding: 0; }
        .menu-prof a { color: white; text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: 0.3s; display: flex; align-items: center; gap: 8px; }
        .menu-prof a:hover { color: var(--ouro); }
        
        .user-info { font-size: 0.85rem; border-left: 1px solid rgba(255,255,255,0.2); padding-left: 20px; display: flex; align-items: center; gap: 10px; }
        .btn-sair { background: #ff4d4d; color: white !important; padding: 5px 12px; border-radius: 4px; text-decoration: none; font-weight: bold; transition: 0.3s; }
        .btn-sair:hover { background: #cc0000; }

        .container { padding: 30px 5%; }
        .welcome-msg { margin-bottom: 30px; border-bottom: 2px solid var(--ouro); display: inline-block; padding-bottom: 5px; color: var(--azul); }
    </style>
</head>
<body>

    <nav class="nav-prof">
        <div class="nav-logo">
            <img src="/betilson/sistema_escolar/<?= htmlspecialchars($logo) ?>" alt="Logo">
            <div style="line-height: 1.2;">
                <span style="font-weight: bold; display: block;">PROFESSOR</span>
                <small style="color: var(--ouro);">Colégio Visão do Futuro</small>
            </div>
        </div>

        <ul class="menu-prof">
            <li><a href="professor_turmas.php"><i class="fas fa-home"></i> Início</a></li>
            <li><a href="alunos_lista.php"><i class="fas fa-users"></i> Meus Alunos</a></li>
             
            <li><a href="perfil.php"><i class="fas fa-key"></i> Trocar Senha</a></li>
        </ul>

        <div class="user-info">
            <span><i class="fas fa-user-circle"></i> <?= htmlspecialchars($nome_prof) ?></span>
            <a href="../logout.php" class="btn-sair" title="Terminar Sessão" onclick="return confirm('Deseja realmente sair do sistema?')">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </div>
    </nav>

    <div class="container">
        <h2 class="welcome-msg">Minhas Turmas</h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            <?php
            // SQL CORRIGIDO: O GROUP BY t.id impede que a turma apareça repetida para cada disciplina
            $sql_turmas = "SELECT t.id, t.nome FROM turmas t 
                           INNER JOIN turmas_disciplinas td ON t.id = td.turma_id 
                           WHERE td.professor_id = $professor_id 
                           GROUP BY t.id"; 
            
            $res = $conn->query($sql_turmas);

            if (!$res) {
                echo "<div style='background:#fee2e2; padding:20px; border-radius:8px; color:#b91c1c; grid-column: 1/-1;'>
                        <strong>Erro na Base de Dados:</strong> " . $conn->error . "
                      </div>";
            } elseif ($res->num_rows > 0) {
                while($t = $res->fetch_assoc()) { ?>
                    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid var(--ouro); box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                        <h3 style="color: var(--azul); margin-top: 0;"><?= htmlspecialchars($t['nome']) ?></h3>
                        <p style="font-size: 0.85rem; color: #666;">Gestão de notas e alunos vinculados.</p>
                        <a href="lancar_notas.php?turma_id=<?= $t['id'] ?>" style="background: var(--azul); color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-size: 0.8rem; display: inline-block;">
                            <i class="fas fa-arrow-right"></i> Entrar na Turma
                        </a>
                    </div>
                <?php } 
            } else { ?>
                <p style="grid-column: 1/-1; color: #666; text-align: center; padding: 50px;">
                    <i class="fas fa-info-circle"></i> Nenhuma turma atribuída ao seu perfil docente.
                </p>
            <?php } ?>
        </div>
    </div>

</body>
</html>
