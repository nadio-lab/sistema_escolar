 <?php
require_once '../includes/conexao.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// SEGURANÇA: Só professores entram
if (!isset($_SESSION['uid']) || $_SESSION['user_tipo'] !== 'professor') {
    header("Location: ../login.php");
    exit;
}

$professor_id = $_SESSION['uid'];

// 1. Puxar Configurações (Logo)
$cfg = allCfg();
$logo = $cfg['escola_logo'] ?? '';

// 2. Filtro de Turma
$filtro_turma = (int)($_GET['turma_id'] ?? 0);
$where = $filtro_turma ? "AND t.id = $filtro_turma" : "";

// 3. Consulta de Alunos
$sql = "SELECT DISTINCT a.id, a.nome_completo, a.estado, a.turma_id, t.nome as turma_nome 
        FROM alunos a 
        INNER JOIN turmas t ON a.turma_id = t.id 
        INNER JOIN turmas_disciplinas td ON t.id = td.turma_id 
        WHERE td.professor_id = $professor_id $where 
        ORDER BY t.nome, a.nome_completo";

$res_alunos = $conn->query($sql);

// 4. Buscar turmas para o Select
$turmas_prof = $conn->query("SELECT t.id, t.nome FROM turmas t 
                             INNER JOIN turmas_disciplinas td ON t.id = td.turma_id 
                             WHERE td.professor_id = $professor_id 
                             GROUP BY t.id ORDER BY t.nome");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Meus Alunos — Painel Docente</title>
    <!-- FontAwesome Corrigido -->
    <?php if ($logo): ?>
<link rel="icon" href="/betilson/sistema_escolar/<?= htmlspecialchars($logo) ?>">
<?php endif; ?>
 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com">
    <style>
        :root { --azul: #1a3a5c; --ouro: #e8a020; --fundo: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; background: var(--fundo); }
        .nav-prof { background: var(--azul); color: white; padding: 10px 5%; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .nav-logo img { height: 45px; }
        .container { padding: 30px 5%; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .tabela { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .tabela th { background: var(--azul); color: white; padding: 12px; text-align: left; font-size: 0.85rem; text-transform: uppercase; }
        .tabela td { padding: 12px; border-bottom: 1px solid #eee; font-size: 0.9rem; color: #334155; }
        .badge-turma { background: var(--ouro); color: var(--azul); padding: 3px 8px; border-radius: 4px; font-weight: bold; font-size: 0.75rem; }
        .btn-voltar { color: white; text-decoration: none; font-size: 0.9rem; transition: 0.3s; }
        .btn-voltar:hover { color: var(--ouro); }
        select { padding: 8px 15px; border-radius: 5px; border: 1px solid #cbd5e1; outline: none; color: var(--azul); font-weight: 500; }
        
        /* Estilo para as ações */
        .btn-acao { color: var(--azul); font-size: 1.1rem; margin: 0 8px; text-decoration: none; transition: 0.2s; }
        .btn-acao:hover { color: var(--ouro); transform: scale(1.1); }
        .btn-tarefa { color: #059669; }
    </style>
</head>
<body>

    <nav class="nav-prof">
        <div class="nav-logo">
            <img src="/betilson/sistema_escolar/<?= htmlspecialchars($logo) ?>" alt="Logo">
        </div>
        <div>
            <span style="margin-right:20px; font-size: 0.85rem;"><i class="fas fa-user-tie"></i> Professor</span>
            <a href="professor_turmas.php" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>
    </nav>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <div>
                <h2 style="color: var(--azul); margin: 0; font-family: 'Playfair Display', serif;">Meus Alunos</h2>
                <p style="color: #64748b; margin: 5px 0 0 0; font-size: 0.85rem;">Gestão académica e recepção de trabalhos.</p>
            </div>
            
            <form method="GET">
                <label style="font-size: 0.8rem; font-weight: bold; color: var(--azul);">FILTRAR TURMA:</label><br>
                <select name="turma_id" onchange="this.form.submit()">
                    <option value="">Todas as turmas</option>
                    <?php if($turmas_prof): ?>
                        <?php while($tp = $turmas_prof->fetch_assoc()): ?>
                            <option value="<?= $tp['id'] ?>" <?= $filtro_turma == $tp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($tp['nome']) ?></option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </form>
        </div>

        <div class="card">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Nome Completo</th>
                        <th>Turma</th>
                        <th>Estado</th>
                        <th style="text-align: center;">Gestão</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($res_alunos && $res_alunos->num_rows > 0): ?>
                        <?php while($al = $res_alunos->fetch_assoc()): ?>
                        <tr>
                            
                            <td style="font-weight: 600;"><?= htmlspecialchars($al['nome_completo']) ?></td>
                            <td><span class="badge-turma"><?= htmlspecialchars($al['turma_nome']) ?></span></td>
                            <td><?= $al['estado'] == 'ativo' ? '🟢 <span style="color:#059669">Ativo</span>' : '🔴 <span style="color:#dc2626">Inativo</span>' ?></td>

                            <td style="text-align: center;">
                                <!-- Link para Notas -->
                                <a href="lancar_notas.php?turma_id=<?= $al['turma_id'] ?>" class="btn-acao" title="Lançar Notas">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                                
                                <!-- Link para Ver Trabalhos Enviados (NOVO) -->
                                <a href="ver_entregas.php?turma_id=<?= $al['turma_id'] ?>" class="btn-acao btn-tarefa" title="Trabalhos Recebidos">
                                    <i class="fas fa-folder-open"></i>
                                </a>
                            </td>

                        </tr>

                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #64748b; padding: 50px;">
                                <i class="fas fa-user-slash" style="display:block; font-size:2rem; margin-bottom:10px;"></i>
                                Selecione uma turma para visualizar os alunos.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
