<?php
// Inicia a sessão PHP. Essencial para armazenar informações do usuário após o login.
session_start();

// Ativa todos os erros para debug (útil durante o desenvolvimento)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inclui o arquivo de conexão com o banco de dados
include 'conexao.php'; // Certifique-se de que este arquivo existe e está acessível

// Verifica se a conexão com o banco de dados falhou
if ($conexao->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conexao->connect_error);
}

// Verifica se o formulário foi submetido via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coleta e sanitiza os dados do formulário
    // htmlspecialchars para evitar XSS (Cross-Site Scripting)
    $usuario = htmlspecialchars(trim($_POST['usuario']));
    $senha_digitada = htmlspecialchars(trim($_POST['senha']));

    // Prepara a consulta SQL para buscar o usuário no banco de dados
    // Usamos prepared statements para evitar SQL Injection
    $stmt = $conexao->prepare("SELECT id, usuario, senha, nome FROM funcionarios WHERE usuario = ?");
    
    // Verifica se a preparação da consulta falhou
    if (false === $stmt) {
        die('Erro na preparação da consulta: ' . $conexao->error);
    }

    // Associa o parâmetro (o usuário digitado) à consulta
    $stmt->bind_param("s", $usuario);
    
    // Executa a consulta
    $stmt->execute();
    
    // Obtém o resultado da consulta
    $result = $stmt->get_result();

    // Verifica se um usuário foi encontrado com o nome de usuário fornecido
    if ($result->num_rows == 1) {
        // Pega os dados do usuário encontrado
        $funcionario = $result->fetch_assoc();
        
        // Verifica a senha digitada com o hash da senha armazenado no banco
        // password_verify é a função correta para comparar a senha digitada com o hash
        if (password_verify($senha_digitada, $funcionario['senha'])) {
            // Login bem-sucedido: Armazena informações do usuário na sessão
            $_SESSION['loggedin'] = true; // Define que o usuário está logado
            $_SESSION['funcionario_id'] = $funcionario['id'];
            $_SESSION['usuario'] = $funcionario['usuario'];
            $_SESSION['nome'] = $funcionario['nome'];

            // Redireciona para a página de pedidos (ou outra página protegida)
            header("Location: pedidos_autonomo.php"); // Altere para o nome correto da sua página de pedidos
            exit(); // Garante que o script pare de executar após o redirecionamento
        } else {
            // Senha incorreta
            header("Location: login.html?erro=credenciais");
            exit();
        }
    } else {
        // Usuário não encontrado
        header("Location: login.html?erro=credenciais");
        exit();
    }

    // Fecha o statement
    $stmt->close();
} else {
    // Se a página for acessada diretamente sem um POST, redireciona para o formulário de login
    header("Location: login.html");
    exit();
}

// Fecha a conexão com o banco de dados
$conexao->close();
?>
