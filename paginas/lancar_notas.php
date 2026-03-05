 <?php
require_once '../includes/conexao.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// SEGURANÇA: Bloqueia quem não é professor
if (!isset($_SESSION['uid']) || $_SESSION['user_tipo'] !== 'professor') {
    header("Location: ../login.php");
    exit;
}

$professor_id = $_SESSION['uid'];
$turma_id = (int)($_GET['turma_id'] ?? 0);

// 1. Buscar as disciplinas que ESTE professor leciona NESTA turma
$sql_disciplinas = "SELECT d.id, d.nome 
                    FROM disciplinas d 
                    INNER JOIN turmas_disciplinas td ON d.id = td.disciplina_id 
                    WHERE td.turma_id = $turma_id AND td.professor_id = $professor_id";
$res_disciplinas = $conn->query($sql_disciplinas);

// Validar se o professor tem acesso a esta turma
if ($res_disciplinas->num_rows === 0) {
    die("Acesso negado: Você não está vinculado a esta turma ou disciplina.");
}

// Buscar nome da turma para o título
$turma_info = $conn->query("SELECT nome FROM turmas WHERE id = $turma_id")->fetch_assoc();

// 2. Processar Gravação de Notas
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trimestre = (int)$_POST['trimestre'];
    $disciplina_id = (int)$_POST['disciplina_id'];

    foreach ($_POST['nota'] as $aluno_id => $valor) {
        $valor = str_replace(',', '.', $valor); 
        if ($valor !== "") {
            $aluno_id = (int)$aluno_id;
            // INSERT com disciplina_id e ON DUPLICATE KEY para permitir atualizações
            $sql = "INSERT INTO notas (aluno_id, turma_id, disciplina_id, professor_id, nota, trimestre) 
                    VALUES ($aluno_id, $turma_id, $disciplina_id, $professor_id, '$valor', $trimestre)
                    ON DUPLICATE KEY UPDATE nota = '$valor'";
            $conn->query($sql);
        }
    }
    $msg = "Notas guardadas com sucesso!";
}

// 3. Buscar Alunos da Turma
$alunos = $conn->query("SELECT id, nome_completo FROM alunos WHERE turma_id = $turma_id ORDER BY nome_completo");

$cfg = allCfg();
$logo = $cfg['escola_logo'] ?? '';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Lançar Notas — <?= htmlspecialchars($turma_info['nome']) ?></title>
    <?php if ($logo): ?>
<link rel="icon" href="/betilson/sistema_escolar/<?= htmlspecialchars($logo) ?>">
<?php endif; ?>
 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com">
    <style>
        :root { --azul: #1a3a5c; --ouro: #e8a020; --fundo: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; background: var(--fundo); }
        .nav-prof { background: var(--azul); color: white; padding: 10px 5%; display: flex; justify-content: space-between; align-items: center; }
        .nav-logo img { height: 45px; }
        .container { padding: 30px 5%; max-width: 900px; margin: auto; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .tabela-notas { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .tabela-notas th { text-align: left; padding: 12px; border-bottom: 2px solid var(--azul); color: var(--azul); }
        .tabela-notas td { padding: 10px; border-bottom: 1px solid #eee; }
        .input-nota { width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center; font-weight: bold; }
        .btn-save { background: var(--azul); color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .btn-save:hover { background: var(--ouro); color: var(--azul); }
        .alert { padding: 15px; background: #d1fae5; color: #065f46; border-radius: 5px; margin-bottom: 20px; border: 1px solid #a7f3d0; }
        .filtros { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: #f8fafc; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        select { width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; }
    </style>
</head>
<body>

    <nav class="nav-prof">
        <div class="nav-logo">
            <img src="/betilson/sistema_escolar/<?= $logo ?>" alt="Logo">
        </div>
        <div style="font-weight: bold; letter-spacing: 1px;">CADERNETA DO PROFESSOR</div>
        <a href="professor_turmas.php" style="color: white; text-decoration: none;"><i class="fas fa-arrow-left"></i> Voltar</a>
    </nav>

    <div class="container">
        <div class="card">
            <h2 style="color: var(--azul); margin-top: 0;"><?= htmlspecialchars($turma_info['nome']) ?></h2>
            
            <?php if($msg): ?> 
                <div class="alert"><i class="fas fa-check-circle"></i> <?= $msg ?></div> 
            <?php endif; ?>

            <form method="POST">
                <div class="filtros">
                    <div>
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Disciplina:</label>
                        <select name="disciplina_id" required>
                            <option value="">Selecione a Disciplina...</option>
                            <?php while($d = $res_disciplinas->fetch_assoc()): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nome']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Trimestre:</label>
                        <select name="trimestre" required>
                            <option value="1">1º Trimestre</option>
                            <option value="2">2º Trimestre</option>
                            <option value="3">3º Trimestre</option>
                        </select>
                    </div>
                </div>

                <table class="tabela-notas">
                    <thead>
                        <tr>
                            <th>Nome do Aluno</th>
                            <th style="text-align: center;">Nota (0-20)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($alunos->num_rows > 0): ?>
                            <?php while($a = $alunos->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($a['nome_completo']) ?></td>
                                <td style="text-align: center;">
                                    <input type="number" step="0.1" min="0" max="20" name="nota[<?= $a['id'] ?>]" class="input-nota" placeholder="—">
                                </td>
                                
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="2" style="text-align:center; padding: 20px;">Nenhum aluno encontrado nesta turma.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div style="margin-top: 30px; text-align: right;">
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Guardar Notas</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
