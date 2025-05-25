<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';
    $quadra = isset($_POST['quadra']) ? trim($_POST['quadra']) : '';
    $casa = isset($_POST['casa']) ? trim($_POST['casa']) : '';
    $cep = isset($_POST['cep']) ? trim($_POST['cep']) : '';
    $pizza_id = isset($_POST['pizza_id']) ? intval($_POST['pizza_id']) : 0;
    $pagamento = isset($_POST['pagamento']) ? trim($_POST['pagamento']) : '';
    $observacoes = isset($_POST['observacoes']) ? trim($_POST['observacoes']) : '';

    if (empty($nome) || empty($telefone) || empty($pizza_id) || empty($pagamento)) {
        die("Preencha todos os campos obrigatórios!");
    }

    $check_pizza = $conexao->query("SELECT id FROM pizzas WHERE id = $pizza_id");
    if ($check_pizza->num_rows == 0) {
        die("Pizza selecionada não existe!");
    }

    $cliente_id = null;
    $sql = "SELECT id FROM clientes WHERE nome = ? AND telefone = ? LIMIT 1";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ss", $nome, $telefone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $cliente_id = $row['id'];
    } else {
        $sql = "INSERT INTO clientes (nome, telefone, quadra, casa, cep) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("sssss", $nome, $telefone, $quadra, $casa, $cep);
        $stmt->execute();
        $cliente_id = $stmt->insert_id;
    }

    $sql = "INSERT INTO pedidos (cliente_id, pizza_id, pagamento, observacoes, data_cadastro) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("iiss", $cliente_id, $pizza_id, $pagamento, $observacoes);

    if ($stmt->execute()) {
        $pedido_id = $conexao->insert_id;

        header("Location: status_pedido.php?pedido_id=" . $pedido_id);
        exit(); 
    } else {
        echo "Erro ao realizar o pedido: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Acesso inválido!";
}
