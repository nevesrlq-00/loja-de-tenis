<?php
// AUTENTICAÇÃO (login do admin) + SEGURANÇA
// Este arquivo é incluído nas páginas do painel. Ele guarda "quem está logado".

// Sessão = memória do servidor sobre o visitante (identificada por um cookie).
// É assim que o site "lembra" que você já fez login.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,    // o JavaScript não consegue ler o cookie (mais seguro)
        'samesite' => 'Lax',   // o cookie não é enviado em requisições vindas de outros sites
    ]);
    session_start();
}

// Retorna true se o admin já fez login
function admin_logado(): bool {
    return !empty($_SESSION['admin']);
}

// Bloqueia a página: se não estiver logado, manda para o login
function exigir_admin(): void {
    if (!admin_logado()) {
        header("Location: login.php");
        exit();
    }
}

// CSRF = ataque em que outro site tenta fazer você clicar num botão que age no SEU painel.
// Defesa: cada formulário leva um "token" secreto aleatório que só o nosso site conhece.
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));   // gera um código aleatório
    }
    return $_SESSION['csrf'];
}

// Gera o <input> escondido com o token (colocado dentro dos formulários)
function csrf_campo(): string {
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrf_token()) . '">';
}

// Confere se o token enviado pelo formulário é igual ao da sessão
function csrf_valido(): bool {
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

// Atalho para "escapar" texto antes de mostrar na página.
// Transforma <script> em texto inofensivo (protege contra XSS).
function e($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}
?>
