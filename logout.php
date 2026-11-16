<?php
// SAIR DO PAINEL: apaga a sessão e volta para o login
require_once 'auth.php';
$_SESSION = [];        // esvazia os dados da sessão
session_destroy();     // encerra a sessão
header("Location: login.php");
exit();
?>
