<?php
require_once '../includes/conexao.php';
verificarLogin();
$titulo_pagina = 'Agenda Escolar';
$pagina_atual  = 'agenda';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar' || $acao === 'editar') {
        $titulo   = limpar($_POST['titulo'] ?? '');
        $descricao= limpar($_POST['descricao'] ?? '');
        $tipo     = limpar($_POST['tipo'] ?? 'evento');
        $cor      = limpar($_POST['cor'] ?? '#0d6efd');
        $inicio   = limpar($_POST['data_inicio'] ?? '');
        $fim      = limpar($_POST['data_fim'] ?? '');
        $local    = limpar($_POST['local_atividade'] ?? '');
        $notificar= isset($_POST['notificar']) ? 1 : 0;
        $uid      = (int)$_SESSION['uid'];

        if ($acao === 'criar') {
            $conn->query("INSERT INTO atividades (titulo,descricao,tipo,cor,data_inicio,data_fim,local_atividade,notificar,criado_por)
                VALUES ('$titulo','$descricao','$tipo','$cor','$inicio',".($fim?"'$fim'":'NULL').",'$local',$notificar,$uid)");
            redir('agenda.php','Atividade agendada com sucesso!');
        } else {
            $id = (int)($_POST['id'] ?? 0);
            $conn->query("UPDATE atividades SET titulo='$titulo',descricao='$descricao',tipo='$tipo',cor='$cor',data_inicio='$inicio',data_fim=".($fim?"'$fim'":'NULL').",local_atividade='$local',notificar=$notificar WHERE id=$id");
            redir('agenda.php','Atividade atualizada!');
        }
    }
    if ($acao === 'apagar') {
        $id = (int)($_POST['id'] ?? 0);
        $conn->query("DELETE FROM atividades WHERE id=$id");
        redir('agenda.php','Atividade removida.','warning');
    }
}

$atividades = $conn->query("SELECT * FROM atividades ORDER BY data_inicio ASC");
$tipos_info = [
    'evento'  => ['label'=>'Evento',  'bg'=>'#dcfce7','c'=>'#166534'],
    'reuniao' => ['label'=>'Reunião', 'bg'=>'#dbeafe','c'=>'#1e40af'],
    'exame'   => ['label'=>'Exame',   'bg'=>'#fef9c3','c'=>'#854d0e'],
    'feriado' => ['label'=>'Feriado', 'bg'=>'#f3e8ff','c'=>'#6b21a8'],
    'outro'   => ['label'=>'Outro',   'bg'=>'#f1f5f9','c'=>'#475569'],
];

require_once '../includes/cabecalho.php';
?>
<?php flash(); ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
  <div>
    <h2 style="font-family:'Playfair Display',serif;color:var(--p);font-size:1.5rem">Agenda Escolar</h2>
    <p style="color:#94a3b8;font-size:.83rem">Calendário de atividades e eventos</p>
  </div>
  <button class="btn btn-p" onclick="abrirModal('modalAtiv')"><i class="fas fa-plus"></i> Agendar Atividade</button>
</div>

<div class="grid-2">
  <!-- Calendário -->
  <div class="card">
    <div class="card-header">
      <div style="display:flex;align-items:center;gap:12px">
        <button onclick="mudarMes(-1)" style="width:30px;height:30px;border-radius:7px;border:1.5px solid var(--borda);background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#64748b">&#8249;</button>
        <div style="font-family:'Playfair Display',serif;font-size:1.1rem;color:var(--p)" id="mes-label">—</div>
        <button onclick="mudarMes(1)" style="width:30px;height:30px;border-radius:7px;border:1.5px solid var(--borda);background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#64748b">&#8250;</button>
      </div>
    </div>
    <div class="card-body">
      <div id="calendario" style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px"></div>
    </div>
  </div>

  <!-- Lista de atividades -->
  <div class="card">
    <div class="card-header"><div class="card-titulo"><i class="fas fa-list-ul"></i> Todas as Atividades</div></div>
    <div class="card-body" style="padding:0;max-height:500px;overflow-y:auto">
      <?php if ($atividades->num_rows === 0): ?>
        <p style="color:#94a3b8;text-align:center;padding:30px;font-size:.85rem">Nenhuma atividade agendada.</p>
      <?php else: ?>
      <?php while($ev = $atividades->fetch_assoc()):
        $tp = $tipos_info[$ev['tipo']] ?? $tipos_info['outro'];
        $passado = strtotime($ev['data_inicio']) < time();
      ?>
      <div style="display:flex;gap:12px;align-items:flex-start;padding:14px 18px;border-bottom:1px solid #f1f5f9;<?= $passado?'opacity:.55':'' ?>">
        <div style="width:4px;border-radius:4px;background:<?= htmlspecialchars($ev['cor']) ?>;align-self:stretch;min-height:40px;flex-shrink:0"></div>
        <div style="flex:1">
          <div style="font-weight:700;font-size:.87rem;color:#1e293b"><?= htmlspecialchars($ev['titulo']) ?></div>
          <?php if($ev['descricao']): ?><div style="font-size:.75rem;color:#64748b;margin-top:2px"><?= htmlspecialchars(mb_strimwidth($ev['descricao'],0,60,'…')) ?></div><?php endif; ?>
          <div style="font-size:.73rem;color:#94a3b8;margin-top:4px;display:flex;gap:10px">
            <span><i class="fas fa-calendar me-1"></i><?= date('d M Y',strtotime($ev['data_inicio'])) ?></span>
            <span><i class="fas fa-clock me-1"></i><?= date('H:i',strtotime($ev['data_inicio'])) ?></span>
            <?php if($ev['local_atividade']): ?><span><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($ev['local_atividade']) ?></span><?php endif; ?>
          </div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px">
          <span class="badge" style="background:<?= $tp['bg'] ?>;color:<?= $tp['c'] ?>"><?= $tp['label'] ?></span>
          <div style="display:flex;gap:4px">
            <button class="btn-acao btn-editar" onclick="editarAtiv(<?= htmlspecialchars(json_encode($ev)) ?>)"><i class="fas fa-edit"></i></button>
            <form method="POST" style="display:inline" onsubmit="return confirm('Remover atividade?')">
              <input type="hidden" name="acao" value="apagar">
              <input type="hidden" name="id" value="<?= $ev['id'] ?>">
              <button type="submit" class="btn-acao btn-apagar"><i class="fas fa-trash"></i></button>
            </form>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal Atividade -->
<div class="modal-overlay" id="modalAtiv">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-titulo" id="titulo-modal-ativ"><i class="fas fa-calendar-plus me-2"></i>Agendar Atividade</div>
      <button class="modal-fechar" onclick="fecharModal('modalAtiv')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="acao" id="acao-ativ" value="criar">
        <input type="hidden" name="id" id="ativ-id" value="">
        <div class="form-group">
          <label>Título *</label>
          <input type="text" name="titulo" id="ativ-titulo" class="form-control" placeholder="Nome da atividade" required>
        </div>
        <div class="form-group">
          <label>Descrição</label>
          <textarea name="descricao" id="ativ-desc" class="form-control" rows="2" placeholder="Detalhes..."></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Tipo</label>
            <select name="tipo" id="ativ-tipo" class="form-control">
              <option value="evento">Evento</option>
              <option value="reuniao">Reunião</option>
              <option value="exame">Exame</option>
              <option value="feriado">Feriado</option>
              <option value="outro">Outro</option>
            </select>
          </div>
          <div class="form-group">
            <label>Cor</label>
            <input type="color" name="cor" id="ativ-cor" class="form-control" value="#0d6efd">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Data e Hora Início *</label>
            <input type="datetime-local" name="data_inicio" id="ativ-inicio" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Data e Hora Fim</label>
            <input type="datetime-local" name="data_fim" id="ativ-fim" class="form-control">
          </div>
        </div>
        <div class="form-group">
          <label>Local</label>
          <input type="text" name="local_atividade" id="ativ-local" class="form-control" placeholder="Ex: Auditório, Sala de Reuniões...">
        </div>
        <div style="display:flex;align-items:center;gap:10px">
          <label class="tg"><input type="checkbox" name="notificar" id="ativ-notif"><span class="tg-sl"></span></label>
          <span style="font-size:.87rem;color:#374151">Enviar notificação por email</span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-out" onclick="fecharModal('modalAtiv')">Cancelar</button>
        <button type="submit" class="btn btn-p"><i class="fas fa-save"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>

<script>
// Calendário simples
let dataAtual = new Date();
const meses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
const dias = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];

function renderCal(){
  const cal = document.getElementById('calendario');
  document.getElementById('mes-label').textContent = meses[dataAtual.getMonth()] + ' ' + dataAtual.getFullYear();
  cal.innerHTML = '';
  dias.forEach(d => {
    const h = document.createElement('div');
    h.style.cssText = 'text-align:center;font-size:.65rem;font-weight:700;color:#94a3b8;padding:6px 0;text-transform:uppercase;';
    h.textContent = d; cal.appendChild(h);
  });
  const primeiro = new Date(dataAtual.getFullYear(), dataAtual.getMonth(), 1);
  const ultimo = new Date(dataAtual.getFullYear(), dataAtual.getMonth()+1, 0).getDate();
  const hoje = new Date();
  for(let i=0; i<primeiro.getDay(); i++){
    const v=document.createElement('div'); v.style.cssText='aspect-ratio:1'; cal.appendChild(v);
  }
  for(let d=1; d<=ultimo; d++){
    const div=document.createElement('div');
    const isHoje=hoje.getDate()===d&&hoje.getMonth()===dataAtual.getMonth()&&hoje.getFullYear()===dataAtual.getFullYear();
    div.style.cssText='aspect-ratio:1;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.82rem;font-weight:500;cursor:pointer;transition:all .15s;';
    div.textContent=d;
    if(isHoje){ div.style.background='var(--p)'; div.style.color='#fff'; div.style.fontWeight='700'; }
    else { div.onmouseover=()=>div.style.background='rgba(26,58,92,.08)'; div.onmouseout=()=>div.style.background=''; }
    cal.appendChild(div);
  }
}
function mudarMes(dir){ dataAtual.setMonth(dataAtual.getMonth()+dir); renderCal(); }
renderCal();

function editarAtiv(ev){
  document.getElementById('acao-ativ').value='editar';
  document.getElementById('ativ-id').value=ev.id;
  document.getElementById('ativ-titulo').value=ev.titulo||'';
  document.getElementById('ativ-desc').value=ev.descricao||'';
  document.getElementById('ativ-tipo').value=ev.tipo||'evento';
  document.getElementById('ativ-cor').value=ev.cor||'#0d6efd';
  document.getElementById('ativ-inicio').value=ev.data_inicio?ev.data_inicio.replace(' ','T').substring(0,16):'';
  document.getElementById('ativ-fim').value=ev.data_fim?ev.data_fim.replace(' ','T').substring(0,16):'';
  document.getElementById('ativ-local').value=ev.local_atividade||'';
  document.getElementById('ativ-notif').checked=ev.notificar==1;
  document.getElementById('titulo-modal-ativ').innerHTML='<i class="fas fa-edit me-2"></i>Editar Atividade';
  abrirModal('modalAtiv');
}
</script>

<?php require_once '../includes/rodape.php'; ?>
