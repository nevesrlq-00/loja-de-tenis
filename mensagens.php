<?php
// PAINEL ADMIN: lista, edita e exclui as mensagens do formulário de contato (o "R", "U" e "D" do CRUD)
require_once 'conexao.php';
require_once 'auth.php';
exigir_admin();   // se não estiver logado, é mandado para o login

// R - LISTAR: busca todas as mensagens, da mais nova para a mais antiga
$stmt = $pdo->query("SELECT * FROM mensagens ORDER BY data_envio DESC");
$mensagens = $stmt->fetchAll();

// Se a URL tem ?editar_id=5, busca essa mensagem para mostrar o formulário de edição
$editando = false;
$msgEdit = null;
if (isset($_GET['editar_id'])) {
    $idEdit = (int)$_GET['editar_id'];
    $stmtEdit = $pdo->prepare("SELECT * FROM mensagens WHERE id = ?");
    $stmtEdit->execute([$idEdit]);
    $msgEdit = $stmtEdit->fetch();
    if ($msgEdit) $editando = true;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Administrador - Mensagens</title>
    <script>try{var t=localStorage.getItem('tema');if(t)document.documentElement.dataset.tema=t;}catch(e){}</script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <a href="index.html" class="logo">👟 SneakerStore (Admin)</a>
        <button type="button" class="menu-toggle" aria-label="Abrir menu" aria-expanded="false" aria-controls="menu-principal">
            <span></span><span></span><span></span>
        </button>
        <nav id="menu-principal">
            <a href="index.html">Home</a>
            <a href="produtos.html">Produtos</a>
            <a href="carrinho.html">Carrinho (<span id="cart-count">0</span>)</a>
            <a href="contato.html">Contato</a>
            <a href="mensagens.php" class="active" aria-current="page">Painel Admin</a>
            <a href="logout.php">Sair</a>
            <button type="button" class="tema-toggle" id="tema-toggle" aria-label="Alternar tema">🌙</button>
        </nav>
    </header>

    <main>
        <!-- Formulário de edição: só aparece quando $editando é verdadeiro -->
        <?php if ($editando): ?>
            <!-- Form de Edição -->
            <div class="form-container">
                <h2>Editar Mensagem #<?= (int)$msgEdit['id'] ?></h2>
                <form action="processa_contato.php" method="POST">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="acao" value="editar">
                    <input type="hidden" name="id" value="<?= (int)$msgEdit['id'] ?>">

                    <div class="form-group">
                        <label for="edit-nome">Nome:</label>
                        <input type="text" id="edit-nome" name="nome" value="<?= e($msgEdit['nome']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-email">E-mail:</label>
                        <input type="email" id="edit-email" name="email" value="<?= e($msgEdit['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-assunto">Assunto:</label>
                        <input type="text" id="edit-assunto" name="assunto" value="<?= e($msgEdit['assunto']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-mensagem">Mensagem:</label>
                        <textarea id="edit-mensagem" name="mensagem" rows="4" required><?= e($msgEdit['mensagem']) ?></textarea>
                    </div>
                    <button type="submit" class="btn">Salvar Alterações</button>
                    <a href="mensagens.php" class="btn btn-danger">Cancelar</a>
                </form>
            </div>
        <?php endif; ?>

        <div class="admin-barra">
            <h2>Mensagens / Suporte Recebidos (<?= count($mensagens) ?>)</h2>
        </div>
        <div class="tabela-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Assunto</th>
                        <th>Mensagem</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($mensagens) > 0): ?>
                        <?php foreach ($mensagens as $msg): // repete uma linha <tr> para cada mensagem ?>
                            <tr>
                                <td><?= (int)$msg['id'] ?></td>
                                <td><?= e($msg['nome']) ?></td>
                                <td><?= e($msg['email']) ?></td>
                                <td><?= e($msg['assunto']) ?></td>
                                <td class="msg-texto"><?= e($msg['mensagem']) ?></td>
                                <td><?= e(date('d/m/Y H:i', strtotime($msg['data_envio']))) ?></td>
                                <td>
                                    <div class="acoes-inline">
                                        <a href="mensagens.php?editar_id=<?= (int)$msg['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                                        <!-- Excluir é um formulário POST com token de segurança (não é mais um link) -->
                                        <form method="POST" action="processa_contato.php" onsubmit="return confirm('Deseja excluir este registro?')">
                                            <?= csrf_campo() ?>
                                            <input type="hidden" name="acao" value="excluir">
                                            <input type="hidden" name="id" value="<?= (int)$msg['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">Nenhuma mensagem cadastrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer>
        <p class="footer-copy">&copy; 2026 SneakerStore - Todos os direitos reservados.</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>