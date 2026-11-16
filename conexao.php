<?php
// CONEXÃO COM O BANCO DE DADOS (MySQL)
// Todos os outros arquivos PHP fazem require_once 'conexao.php' para usar a variável $pdo.

$host = "localhost";        // onde o MySQL está rodando
$usuario = "root";
$senha = "";
$banco = "loja_tenis";      // nome do banco (criado pelo banco.sql)

/*
 * Senha do Painel Admin (guardada como "hash", nunca como texto puro).
 * A senha padrão é: admin123
 * TROQUE antes de publicar! Para gerar um novo hash, rode no terminal:
 *   php -r 'echo password_hash("SUA_NOVA_SENHA", PASSWORD_DEFAULT);'
 * e cole o resultado abaixo.
 */
define('ADMIN_SENHA_HASH', '$2y$10$wW1/IvEte1SDFgVW8JvvUOJqDORdv5el1KxUUFXLpMLGPoxRa8bEm');

try {
    // PDO = forma moderna e segura de conversar com o banco
    $pdo = new PDO("mysql:host=$host;dbname=$banco;charset=utf8mb4", $usuario, $senha);
    // Se der erro numa consulta, o PHP avisa (exceção) em vez de falhar calado
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Cada linha do banco volta como array com o nome das colunas: $msg['nome']
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Não mostra detalhes técnicos ao visitante (ficam no log do servidor)
    error_log("Erro de conexão: " . $e->getMessage());
    http_response_code(500);
    die("Não foi possível conectar ao banco de dados no momento.");
}
?>
