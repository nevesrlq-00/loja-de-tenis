<?php
// TELA DE LOGIN DO ADMIN
require_once 'conexao.php';
require_once 'auth.php';

// Se já está logado, vai direto para o painel
if (admin_logado()) {
    header("Location: mensagens.php");
    exit();
}

$erro = '';   // mensagem de erro (vazia = sem erro)

// Só processa quando o formulário foi enviado (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {   // 1) confere o token de segurança
        $erro = "Sessão expirada. Tente novamente.";
    } elseif (password_verify($_POST['senha'] ?? '', ADMIN_SENHA_HASH)) {
        // 2) Senha correta: cria uma sessão nova (evita roubo de sessão) e marca como logado
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        header("Location: mensagens.php");
        exit();
    } else {
        // 3) Senha errada: espera 1s antes de responder, para atrapalhar tentativas em sequência
        sleep(1); // dificulta tentativas em sequência
        $erro = "Senha incorreta.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SneakerStore - Login do Admin</title>
    <script>try{var t=localStorage.getItem('tema');if(t)document.documentElement.dataset.tema=t;}catch(e){}</script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <a href="index.html" class="logo">👟 SneakerStore</a>
        <nav>
            <a href="index.html">Voltar à loja</a>
        </nav>
    </header>

    <main>
        <div class="form-container login-box">
            <h2>Painel Admin</h2>
            <?php if ($erro): ?>
                <div class="alerta alerta-erro" role="alert"><?= e($erro) ?></div>
            <?php endif; ?>
            <form method="POST" action="login.php">
                <?= csrf_campo() ?>   <!-- token de segurança -->
                <div class="form-group">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" autocomplete="current-password" required autofocus>
                </div>
                <button type="submit" class="btn">Entrar</button>
            </form>
        </div>
    </main>

    <footer>
        <p class="footer-copy">&copy; 2026 SneakerStore - Todos os direitos reservados.</p>
    </footer>
</body>
</html>
