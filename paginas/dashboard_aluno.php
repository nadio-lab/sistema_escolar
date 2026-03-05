 <?php
require_once '../includes/conexao.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$exibir_boas_vindas = false;
$hoje = date('Y-m-d');

// Se não houver registo de saudação para hoje nesta sessão
if (!isset($_SESSION['saudacao_dia']) || $_SESSION['saudacao_dia'] !== $hoje) {
    $exibir_boas_vindas = true;
    $_SESSION['saudacao_dia'] = $hoje; // Marca como exibido
}

// SEGURANÇA: Bloqueio de acesso
if (!isset($_SESSION['uid']) || ($_SESSION['user_tipo'] !== 'aluno' && $_SESSION['user_tipo'] !== 'encarregado')) {
    header("Location: ../login.php"); exit;
}

$aluno_id = $_SESSION['aluno_id'] ?? 0;
$nome_aluno = $_SESSION['user_nome'];
$cfg = allCfg();
$logo = $cfg['escola_logo'] ?? '';
$mensagem_senha = '';

// --- LÓGICA: ALTERAÇÃO DE SENHA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'alterar_senha') {
    $nova = $_POST['nova_senha'] ?? '';
    $conf = $_POST['confirma_senha'] ?? '';

    if (!empty($nova) && $nova === $conf) {
        $hash = password_hash($nova, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE alunos SET senha_acesso = ? WHERE id = ?");
        $stmt->bind_param("si", $hash, $aluno_id);
        if ($stmt->execute()) {
            $mensagem_senha = '<p style="color: #059669; font-size: 0.85rem; font-weight: bold;">✔ Senha atualizada!</p>';
        }
    } else {
        $mensagem_senha = '<p style="color: #dc2626; font-size: 0.85rem;">✘ As senhas não coincidem.</p>';
    }
}

// 1. CONSULTA DE PROPINAS
$propinas = $conn->query("SELECT * FROM fin_transacoes WHERE aluno_id = $aluno_id AND descricao LIKE '%propina%' ORDER BY data_transacao DESC LIMIT 3");

// 2. CONSULTA DE DESEMPENHO
$notas_sql = "SELECT d.nome, 
              MAX(CASE WHEN n.trimestre = 1 THEN n.nota END) as n1,
              MAX(CASE WHEN n.trimestre = 2 THEN n.nota END) as n2,
              MAX(CASE WHEN n.trimestre = 3 THEN n.nota END) as n3
              FROM notas n 
              JOIN disciplinas d ON n.disciplina_id = d.id 
              WHERE n.aluno_id = $aluno_id GROUP BY d.id";
$desempenho = $conn->query($notas_sql);
 
 // 3. CRONOGRAMA DE ATIVIDADES (Eventos, Exames, Feriados)
$atividades = $conn->query("SELECT * FROM atividades 
                            WHERE data_inicio >= CURDATE() 
                            ORDER BY data_inicio ASC LIMIT 5");
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'enviar_tarefa') {
    $titulo = limpar($_POST['titulo_tarefa']);
    $coment = limpar($_POST['comentario']);
    }
 
 // 1. Pegar o ano letivo atual das configurações (ex: "2025")
$ano_letivo_full = $cfg['ano_letivo'] ?? date('Y'); 
$ano_referencia = substr($ano_letivo_full, 0, 4); // Pega apenas os primeiros 4 dígitos

// 2. Definir meses (Ajustado para o calendário de Angola: Fev a Dez)
$meses_letivos = [
    2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
    7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];

 
// 1. A consulta SQL TEM de selecionar o ID
$pagamentos_res = $conn->query("SELECT id, MONTH(data_transacao) as mes 
                                FROM fin_transacoes 
                                WHERE aluno_id = $aluno_id 
                                AND descricao LIKE '%propina%'");

$meses_pagos = [];
if ($pagamentos_res) {
    while($row = $pagamentos_res->fetch_assoc()) {
        // 2. O array TEM de guardar o ID no índice do mês
        $meses_pagos[(int)$row['mes']] = $row['id']; 
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'atualizar_foto') {
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === 0) {
        $extensao = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg', 'jpeg', 'png'];

        if (in_array($extensao, $permitidos)) {
            $novo_nome = "aluno_" . $aluno_id . "_" . time() . "." . $extensao;
            $caminho_db = "uploads/fotos/" . $novo_nome; // Caminho para a BD
            $destino = "../" . $caminho_db; // Caminho para mover o ficheiro

            if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $destino)) {
                $conn->query("UPDATE alunos SET foto = '$caminho_db' WHERE id = $aluno_id");
                $msg_foto = "<p style='color:green'>✔ Foto atualizada!</p>";
                // Atualiza a variável $logo ou $foto para refletir na página atual
                header("Refresh:0");
            }
        } else {
            $msg_foto = "<p style='color:red'>✘ Apenas JPG ou PNG.</p>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Meu Painel — <?= $cfg['escola_nome'] ?? 'Colégio' ?></title>
    <!-- Font Awesome para os ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com">
    <!-- Chart.js para o gráfico -->
    <script src="https://cdn.jsdelivr.net"></script>
    <!-- Estilos anteriores mantidos... -->
  <?php if ($logo): ?>
<link rel="icon" href="/betilson/sistema_escolar/<?= htmlspecialchars($logo) ?>">
<?php endif; ?>
    <style>
        :root { --azul: #1a3a5c; --ouro: #e8a020; --fundo: #f4f7f6; }
        body { font-family: 'DM Sans', sans-serif; margin: 0; background: var(--fundo); color: #334155; }
        .header { background: var(--azul); color: white; padding: 15px 5%; display: flex; justify-content: space-between; align-items: center; }
        .header img { height: 50px; }
        .main-grid { padding: 30px 5%; display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .card-h { font-weight: bold; color: var(--azul); border-bottom: 2px solid var(--ouro); padding-bottom: 10px; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        
        /* Estilos de Formulário */
        .input-senha { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn-senha { width: 100%; background: var(--azul); color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-senha:hover { opacity: 0.9; }

        .status-badge { padding: 3px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; text-transform: uppercase; }
        .st-ok { background: #d1fae5; color: #065f46; } 
        .st-ex { background: #fef3c7; color: #92400e; } 
        .st-re { background: #fee2e2; color: #991b1b; }

        .propina-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dotted #ddd; font-size: 0.9rem; }
        .pago { color: #059669; font-weight: bold; }
    </style>
</head>
<body>

<header class="header">
    <div style="display:flex; align-items:center; gap:15px;">
        <img src="/betilson/sistema_escolar/<?= $logo ?>" alt="Logo">
        <div><strong>PAINEL DO ALUNO: <?= strtoupper($nome_aluno) ?></strong></div>
    </div>
    <a href="../logout.php" style="color:white; text-decoration:none;"><i class="fas fa-sign-out-alt"></i> Sair</a>
</header>

<div class="main-grid">
    <!-- COLUNA PRINCIPAL -->
    
        <div class="card">
            <div class="card-h"><i class="fas fa-chart-bar"></i> Notas e Desempenho</div>
            <table width="100%" style="border-collapse: collapse; font-size: 0.9rem;">
                <thead>
                    <tr style="text-align: left; color: #64748b;">
                        <th>Disciplina</th>
                        <th>Média (MF)</th>
                        <th>Estado Académico</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($desempenho && $desempenho->num_rows > 0): ?>
                        <?php 
                        $desempenho->data_seek(0);
                        while($d = $desempenho->fetch_assoc()): 
                            // Filtra apenas notas preenchidas
                            $notas_v = array_filter([$d['n1'], $d['n2'], $d['n3']], function($v) { return !is_null($v); });
                            $mf = count($notas_v) > 0 ? array_sum($notas_v) / count($notas_v) : 0;
                            
                            // Lógica Académica Corrigida (Escala 0-20)
                            if ($mf < 7) {
                                $status = "st-re"; $txt = "Reprovado";
                            } elseif ($mf < 9.5) {
                                $status = "st-re"; $txt = "Recurso";
                            } elseif ($mf < 13.5) {
                                $status = "st-ex"; $txt = "Admitido";
                            } else {
                                $status = "st-ok"; $txt = "Dispensado";
                            }
                        ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 12px 0;"><strong><?= htmlspecialchars($d['nome']) ?></strong></td>
                            <td style="font-weight: bold; color: <?= ($mf < 9.5) ? '#dc2626' : '#1a3a5c' ?>;">
                                <?= number_format($mf, 1) ?>
                            </td>
                            <td><span class="status-badge <?= $status ?>"><?= $txt ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="padding:20px; text-align:center; color:#999;">Nenhuma nota lançada.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
     
        <div class="card">
            <div class="card-h"><i class="fas fa-calendar-alt"></i> Próximas Atividades e Exames</div>
            <?php if($atividades && $atividades->num_rows > 0): ?>
                <?php while($at = $atividades->fetch_assoc()): 
                    $cor_at = !empty($at['cor']) ? $at['cor'] : '#0d6efd';
                ?>
                    <div style="display: flex; gap: 15px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9;">
                        <div style="background: <?= $cor_at ?>15; color: <?= $cor_at ?>; padding: 10px; border-radius: 8px; text-align: center; min-width: 50px; border: 1px solid <?= $cor_at ?>30;">
                            <div style="font-size: 0.7rem; font-weight: bold; text-transform: uppercase;"><?= date('M', strtotime($at['data_inicio'])) ?></div>
                            <div style="font-size: 1.2rem; font-weight: bold;"><?= date('d', strtotime($at['data_inicio'])) ?></div>
                        </div>

                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <strong style="color: var(--azul); font-size: 0.95rem;"><?= htmlspecialchars($at['titulo']) ?></strong>
                                <span style="font-size: 0.7rem; background: #eee; padding: 2px 6px; border-radius: 4px; text-transform: uppercase;"><?= $at['tipo'] ?></span>
                            </div>
                            <p style="font-size: 0.85rem; color: #64748b; margin: 4px 0;">
                                <i class="far fa-clock"></i> <?= date('H:i', strtotime($at['data_inicio'])) ?> 
                                <?php if($at['local_atividade']): ?>
                                    &nbsp;·&nbsp; <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($at['local_atividade']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #94a3b8; padding: 20px;">Nenhuma atividade agendada.</p>
            <?php endif; ?>

        </div>
 

    <!-- COLUNA LATERAL -->
        <div class="card">
            <div class="card-h"><i class="fas fa-shield-alt"></i> Segurança do Portal</div>
            <?= $mensagem_senha ?>
            <form method="POST">
                <input type="hidden" name="acao" value="alterar_senha">
                <input type="password" name="nova_senha" class="input-senha" placeholder="Nova Senha" required minlength="6">
                <input type="password" name="confirma_senha" class="input-senha" placeholder="Confirmar Senha" required minlength="6">
                <button type="submit" class="btn-senha">Atualizar Acesso</button>
            </form>
           
   
</div>
 <div class="card">
    <div class="card-h"><i class="fas fa-camera"></i> Foto do Perfil</div>
    <?= $msg_foto ?? '' ?>
    <form method="POST" enctype="multipart/form-data" style="text-align: center;">
        <input type="hidden" name="acao" value="atualizar_foto">
        
        <!-- Preview da foto atual -->
        <div style="width: 100px; height: 100px; border-radius: 50%; overflow: hidden; margin: 0 auto 15px; border: 3px solid var(--ouro);">
            <?php 
                $foto_res = $conn->query("SELECT foto FROM alunos WHERE id = $aluno_id")->fetch_assoc();
                $foto_atual = $foto_res['foto'] ?? 'assets/img/user-default.png';
            ?>
            <img src="../<?= $foto_atual ?>" style="width: 100%; height: 100%; object-fit: cover;">
        </div>

        <input type="file" name="foto_perfil" class="input-senha" style="font-size: 0.7rem;" required>
        <button type="submit" class="btn-senha" style="font-size: 0.8rem; padding: 5px;">
            <i class="fas fa-sync"></i> Alterar Foto
        </button>
    </form>
    <br> 
    <a href="cartao_estudante.php" target="_blank" style="text-decoration:none;">
    <div class="card" style="background: linear-gradient(135deg, var(--azul) 0%, #2c5282 100%); color: white; text-align: center; padding: 25px; transition: 0.3s; border: none;">
        <i class="fas fa-id-card" style="font-size: 2rem; margin-bottom: 10px; color: var(--ouro);"></i>
        <h4 style="margin:0;">MEU CARTÃO DIGITAL</h4>
        <small style="opacity: 0.8;">Identificação de Estudante</small>
    </div>
</a>
</div>



<div class="card">
    <div class="card-h">
        <i class="fas fa-money-check-alt"></i> Propinas de <?= $ano_referencia ?>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
        <?php foreach ($meses_letivos as $num => $nome): 
    // Verifica se o mês está pago e pega o ID do recibo
    $id_recibo = $meses_pagos[$num] ?? null;
    $pago = isset($id_recibo);
?>
    <div style="padding: 8px 12px; border-radius: 6px; background: <?= $pago ? '#f0fdf4' : '#f8fafc' ?>; border: 1px solid <?= $pago ? '#bbf7d0' : '#e2e8f0' ?>; display: flex; justify-content: space-between; align-items: center;">
        
        <span style="font-size: 0.75rem; font-weight: 600; color: #475569;"><?= $nome ?></span>
        
        <div style="display: flex; align-items: center; gap: 10px;">
            <?php if($pago): ?>
                <!-- BOTÃO PARA VER E IMPRIMIR RECIBO -->
                <a href="recibo.php?id=<?= $id_recibo ?>" target="_blank" title="Imprimir Recibo" style="color: var(--azul); font-size: 0.9rem;">
                    <i class="fas fa-print"></i>
                </a>
                <i class="fas fa-check-circle" style="color: #059669; font-size: 0.9rem;"></i>
            <?php else: ?>
                <i class="far fa-circle" style="color: #cbd5e1; font-size: 0.9rem;"></i>
            <?php endif; ?>
        </div>

    </div>
<?php endforeach; ?>

<!-- div mãe -->
</div>
     


  
<?php if ($exibir_boas_vindas): ?>
<div id="toast-boas-vindas" style="position: fixed; top: 20px; right: 20px; background: var(--azul); color: white; padding: 15px 25px; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 9999; display: flex; align-items: center; gap: 15px; border-left: 5px solid var(--ouro); animation: slideIn 0.5s ease-out;">
    <div style="font-size: 1.5rem;">👋</div>
    <div>
        <strong style="display: block;">Olá, <?= explode(' ', $nome_aluno)[0] ?>!</strong>
        <small style="opacity: 0.8;">Bom ver-te de volta ao Colégio Visão do Futuro.</small>
    </div>
    <button onclick="this.parentElement.remove()" style="background: none; border: none; color: white; cursor: pointer; margin-left: 10px;">&times;</button>
</div>

<style>
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
</style>

<script>
    // Remove a notificação automaticamente após 5 segundos
    setTimeout(() => {
        const toast = document.getElementById('toast-boas-vindas');
        if(toast) toast.style.opacity = '0';
        setTimeout(() => toast ? toast.remove() : null, 500);
    }, 5000);
</script>
<?php endif; ?>

   
<!-- Importar Chart.js -->
<script src="https://cdn.jsdelivr.net"></script>

</body>
</html>
