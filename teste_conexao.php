<?php
include 'conexao.php';

// Teste 1: Verificar conexão
echo "<h1>Teste de Conexão</h1>";
if ($conexao->connect_error) {
    die("<p style='color:red'>Falha na conexão: " . $conexao->connect_error . "</p>");
} else {
    echo "<p style='color:green'>✓ Conexão com o banco OK</p>";
}

// Teste 2: Verificar tabelas
$tabelas = ['pedidos', 'clientes', 'pizzas'];
foreach ($tabelas as $tabela) {
    $result = $conexao->query("SELECT 1 FROM $tabela LIMIT 1");
    echo "<p>Tabela $tabela: " . ($result ? "EXISTE" : "NÃO EXISTE") . "</p>";
}

// Teste 3: Contar pedidos
$result = $conexao->query("SELECT COUNT(*) as total FROM pedidos");
$row = $result->fetch_assoc();
echo "<p>Total de pedidos: " . $row['total'] . "</p>";
echo "<h2>Últimos 5 pedidos (raw)</h2>";
$result = $conexao->query("SELECT * FROM pedidos ORDER BY id DESC LIMIT 5");
while($row = $result->fetch_assoc()) {
    echo "<pre>" . print_r($row, true) . "</pre>";
}
?>