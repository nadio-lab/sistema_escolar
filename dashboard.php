<?php
define('RAIZ', true);
require_once 'includes/conexao.php';
verificarLogin();

$titulo_pagina = 'Dashboard';
$pagina_atual  = 'dashboard';

// Estatísticas
$total_alunos   = contagem('alunos','estado="ativo"');
$total_turmas   = contagem('turmas','ativo=1');
$propinas_atraso= contagem('fin_transacoes','tipo="receita" AND estado="pendente"');

// Receita do mês atual
// Receita do mês atual
$mes = date('Y-m');
$sql_receita = "SELECT COALESCE(SUM(valor),0) as total FROM fin_transacoes WHERE tipo='receita' AND estado='pago' AND data_transacao LIKE '$mes%'";
$r = $conn->query($sql_receita);

// Verificação de segurança: só faz fetch se a consulta correu bem
$receita_mes = 0;
if ($r && $r->num_rows > 0) {
    $dados_r = $r->fetch_assoc();
    $receita_mes = $dados_r['total'] ?? 0;
}
// Próximos eventos 
$eventos = $conn->query("SELECT * FROM atividades WHERE data_inicio >= NOW() ORDER BY data_inicio ASC LIMIT 4");

// Últimas transações
$transacoes = $conn->query("SELECT t.*, c.nome cat_nome, c.cor cat_cor, a.nome_completo aluno_nome
  FROM fin_transacoes t
  LEFT JOIN fin_categorias c ON t.categoria_id=c.id
  LEFT JOIN alunos a ON t.aluno_id=a.id
  ORDER BY t.criado_em DESC LIMIT 5");

// Notificações não lidas
$notifs = $conn->query("SELECT * FROM notificacoes WHERE lida=0 AND utilizador_id={$_SESSION['uid']} ORDER BY criado_em DESC LIMIT 5");

require_once 'includes/cabecalho.php';
?>

<?php flash(); ?>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card stat-azul">
    <div class="stat-ic"><i class="fas fa-user-graduate"></i></div>
    <div class="stat-val"><?= $total_alunos ?></div>
    <div class="stat-label">Alunos Matriculados</div>
  </div>
  <div class="stat-card stat-ouro">
    <div class="stat-ic"><i class="fas fa-chalkboard"></i></div>
    <div class="stat-val"><?= $total_turmas ?></div>
    <div class="stat-label">Turmas Ativas</div>
  </div>
  <div class="stat-card stat-verde">
    <div class="stat-ic"><i class="fas fa-coins"></i></div>
    <div class="stat-val"><?= number_format($receita_mes/1000,0) ?>K</div>
    <div class="stat-label">Receita do Mês (Kz)</div>
  </div>
  <div class="stat-card stat-red">
    <div class="stat-ic"><i class="fas fa-exclamation-triangle"></i></div>
    <div class="stat-val"><?= $propinas_atraso ?></div>
    <div class="stat-label">Propinas em Atraso</div>
  </div>
</div>

<div class="grid-2">
  <!-- Próximos Eventos -->
  <div class="card">
    <div class="card-header">
      <div class="card-titulo"><i class="fas fa-calendar-alt"></i> Próximos Eventos</div>
      <a href="paginas/agenda.php" class="btn btn-sm btn-out">Ver todos</a>
    </div>
    <div class="card-body">
      <?php if ($eventos->num_rows === 0): ?>
        <p style="color:#94a3b8;font-size:.85rem;text-align:center;padding:20px 0">Nenhum evento agendado.</p>
      <?php else: ?>
      <?php while($ev = $eventos->fetch_assoc()):
        $tipos = ['evento'=>['bg'=>'#dcfce7','c'=>'#166534','label'=>'Evento'],
                  'reuniao'=>['bg'=>'#dbeafe','c'=>'#1e40af','label'=>'Reunião'],
                  'exame'=>['bg'=>'#fef9c3','c'=>'#854d0e','label'=>'Exame'],
                  'feriado'=>['bg'=>'#f3e8ff','c'=>'#6b21a8','label'=>'Feriado'],
                  'outro'=>['bg'=>'#f1f5f9','c'=>'#475569','label'=>'Outro']];
        $tp = $tipos[$ev['tipo']] ?? $tipos['outro'];
      ?>
      <div style="display:flex;align-items:flex-start;gap:12px;padding:11px 0;border-bottom:1px solid #f1f5f9;">
        <div style="width:4px;border-radius:4px;background:<?= htmlspecialchars($ev['cor']) ?>;align-self:stretch;min-height:36px;flex-shrink:0;"></div>
        <div style="flex:1">
          <div style="font-weight:700;font-size:.87rem;color:#1e293b"><?= htmlspecialchars($ev['titulo']) ?></div>
          <div style="font-size:.75rem;color:#94a3b8;margin-top:3px">
            <i class="fas fa-calendar me-1"></i><?= date('d M Y', strtotime($ev['data_inicio'])) ?>
            <?php if($ev['local_atividade']): ?> &nbsp;<i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($ev['local_atividade']) ?><?php endif; ?>
          </div>
        </div>
        <span class="badge" style="background:<?= $tp['bg'] ?>;color:<?= $tp['c'] ?>"><?= $tp['label'] ?></span>
      </div>
      <?php endwhile; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Últimas Transações -->
  <div class="card">
    <div class="card-header">
      <div class="card-titulo"><i class="fas fa-coins"></i> Últimas Transações</div>
      <a href="paginas/financas.php" class="btn btn-sm btn-out">Ver todas</a>
    </div>
    <div class="card-body" style="padding:0">
      <table class="tabela">
        <thead><tr><th>Descrição</th><th>Valor</th><th>Estado</th></tr></thead>
        <tbody>
        <?php while($tr = $transacoes->fetch_assoc()):
          $cor_val = $tr['tipo']==='receita' ? '#059669' : '#dc2626';
          $sinal   = $tr['tipo']==='receita' ? '+' : '-';
          $estados = ['pago'=>'badge-verde','pendente'=>'badge-amarelo','cancelado'=>'badge-vermelho'];
          $bc = $estados[$tr['estado']] ?? 'badge-azul';
        ?>
        <tr>
          <td>
            <div style="font-weight:600;font-size:.84rem"><?= htmlspecialchars(mb_strimwidth($tr['descricao'],0,35,'…')) ?></div>
            <div style="font-size:.72rem;color:#94a3b8"><?= date('d/m/Y',$r=strtotime($tr['data_transacao'])) ?></div>
          </td>
          <td style="font-weight:700;color:<?= $cor_val ?>"><?= $sinal ?>Kz <?= number_format($tr['valor'],0,',',' ') ?></td>
          <td><span class="badge <?= $bc ?>"><?= ucfirst($tr['estado']) ?></span></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Notificações -->
<?php if ($notifs->num_rows > 0): ?>
<div class="card" style="margin-bottom:24px">
  <div class="card-header">
    <div class="card-titulo"><i class="fas fa-bell"></i> Notificações Recentes</div>
    <a href="paginas/notificacoes.php" class="btn btn-sm btn-out">Ver todas</a>
  </div>
  <div class="card-body">
    <?php while($n = $notifs->fetch_assoc()):
      $estilos = ['info'=>'alerta-info','sucesso'=>'alerta-ok','aviso'=>'alerta-aviso','erro'=>'alerta-erro'];
      $icons = ['info'=>'info-circle','sucesso'=>'check-circle','aviso'=>'exclamation-triangle','erro'=>'times-circle'];
      $ec = $estilos[$n['tipo']] ?? 'alerta-info';
      $ic = $icons[$n['tipo']] ?? 'info-circle';
    ?>
    <div class="alerta <?= $ec ?>">
      <i class="fas fa-<?= $ic ?>"></i>
      <div style="flex:1"><strong><?= htmlspecialchars($n['titulo']) ?></strong><br><span style="font-size:.8rem"><?= htmlspecialchars($n['mensagem']) ?></span></div>
      <span style="font-size:.72rem;color:inherit;opacity:.6"><?= date('d/m H:i',strtotime($n['criado_em'])) ?></span>
    </div>
    <?php endwhile; ?>
  </div>
</div>



<?php endif; ?>

<?php require_once 'includes/rodape.php'; ?>
