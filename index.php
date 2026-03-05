<?php
require_once 'includes/conexao.php';
if (!empty($_SESSION['uid'])) redir('dashboard.php');
else redir('login.php');
