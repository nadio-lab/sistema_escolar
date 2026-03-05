<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'escola_gestao';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Erro BD: " . $conn->connect_error);
$conn->set_charset("utf8mb4");
if (session_status() === PHP_SESSION_NONE) session_start();

function limpar($v){ global $conn; return $conn->real_escape_string(trim($v)); }

function redir($url, $msg=null, $tipo='success'){
    if ($msg) $_SESSION['flash'] = ['texto'=>$msg,'tipo'=>$tipo];
    header("Location:$url"); exit;
}

function flash(){
    if (!empty($_SESSION['flash'])){
        $f = $_SESSION['flash'];
        $ic = ['success'=>'check-circle','danger'=>'exclamation-circle','warning'=>'exclamation-triangle','info'=>'info-circle'];
        $i = $ic[$f['tipo']] ?? 'info-circle';
        echo "<div class='alert alert-{$f['tipo']} alert-dismissible fade show d-flex align-items-center gap-2 mb-3'>
                <i class='fas fa-$i'></i><div>{$f['texto']}</div>
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
        unset($_SESSION['flash']);
    }
}

function verificarLogin(){
    if (empty($_SESSION['uid'])) redir('login.php');
}

function cfg($k, $pad=''){
    global $conn;
    $k = $conn->real_escape_string($k);
    $r = $conn->query("SELECT valor FROM escola_config WHERE chave='$k' LIMIT 1");
    return ($r && $r->num_rows) ? $r->fetch_assoc()['valor'] : $pad;
}

function setCfg($k, $v){
    global $conn;
    $k = $conn->real_escape_string($k);
    $v = $conn->real_escape_string($v);
    $conn->query("INSERT INTO escola_config(chave,valor) VALUES('$k','$v') ON DUPLICATE KEY UPDATE valor='$v'");
}

function allCfg(){
    global $conn; $c = [];
    $r = $conn->query("SELECT chave,valor FROM escola_config");
    while ($row = $r->fetch_assoc()) $c[$row['chave']] = $row['valor'];
    return $c;
}

function modAtivo($slug){
    global $conn;
    $slug = $conn->real_escape_string($slug);
    $r = $conn->query("SELECT ativo FROM modulos WHERE slug='$slug' LIMIT 1");
    return ($r && $r->num_rows) ? (bool)$r->fetch_assoc()['ativo'] : false;
}

function contagem($t, $w='1'){
    global $conn;
    $r = $conn->query("SELECT COUNT(*) c FROM $t WHERE $w");
    return $r ? $r->fetch_assoc()['c'] : 0;
}
