<?php
// PROCESSAMENTO DAS AÇÕES (o "C", "U" e "D" do CRUD)
// Este arquivo não tem tela: recebe os dados dos formulários, mexe no banco e redireciona.
//   cadastrar = Create (formulário público de contato)
//   editar    = Update (painel admin)
//   excluir   = Delete (painel admin)

require_once 'conexao.php';
require_once 'auth.php';

$acao = $_POST['acao'] ?? '';   // qual ação foi pedida (vem num campo escondido do formulário)

// Só aceitamos POST (dados enviados por formulário). Acessar pelo endereço não faz nada.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: contato.html");
    exit();
}

// ---------- C - CADASTRAR MENSAGEM (qualquer visitante pode) ----------
if ($acao === 'cadastrar') {
    // Campo-armadilha: é invisível para pessoas, então só robôs preenchem.
    // Se veio preenchido, finge que deu certo e descarta.
    if (!empty($_POST['website'])) {
        header("Location: contato.html?enviado=1");
        exit();
    }

    // trim() remove espaços sobrando no começo/fim. O "?? ''" evita erro se o campo não vier.
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $assunto = trim($_POST['assunto'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');

    // VALIDAÇÃO no servidor (o navegador pode ser burlado, então o PHP confere de novo):
    // campos preenchidos, e-mail com formato válido e tamanhos dentro do limite do banco
    $valido = $nome !== '' && $assunto !== '' && $mensagem !== ''
        && filter_var($email, FILTER_VALIDATE_EMAIL)
        && mb_strlen($nome) <= 100 && mb_strlen($email) <= 100 && mb_strlen($assunto) <= 150;

    if ($valido) {
        // Os "?" são marcadores: os valores vão separados da consulta SQL.
        // Isso se chama PREPARED STATEMENT e impede SQL Injection.
        $stmt = $pdo->prepare("INSERT INTO mensagens (nome, email, assunto, mensagem) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $email, $assunto, $mensagem]);
        header("Location: contato.html?enviado=1");   // volta com mensagem de sucesso
    } else {
        header("Location: contato.html?erro=1");      // volta com mensagem de erro
    }
    exit();
}

// ---------- A partir daqui, só o ADMIN pode ----------
exigir_admin();                 // precisa estar logado
if (!csrf_valido()) {           // e o token de segurança tem que bater
    http_response_code(403);
    die("Requisição inválida (token de segurança ausente ou expirado).");
}

// ---------- U - EDITAR MENSAGEM ----------
if ($acao === 'editar') {
    $id = (int)($_POST['id'] ?? 0);     // (int) garante que o id é um número
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $assunto = trim($_POST['assunto'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');

    if ($id > 0 && $nome !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && $assunto !== '' && $mensagem !== '') {
        $stmt = $pdo->prepare("UPDATE mensagens SET nome = ?, email = ?, assunto = ?, mensagem = ? WHERE id = ?");
        $stmt->execute([$nome, $email, $assunto, $mensagem, $id]);
    }
    header("Location: mensagens.php");
    exit();
}

// ---------- D - EXCLUIR MENSAGEM ----------
if ($acao === 'excluir') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM mensagens WHERE id = ?");
        $stmt->execute([$id]);
    }
    header("Location: mensagens.php");
    exit();
}

// Ação desconhecida: volta para o painel
header("Location: mensagens.php");
exit();
?>
