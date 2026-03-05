<?php
require_once 'includes/conexao.php';
session_destroy();
redir('login.php', 'Sessão terminada com sucesso.', 'info');
