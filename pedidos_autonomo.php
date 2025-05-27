<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html?erro=acesso_negado");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'conexao.php';
if ($conexao->connect_error) {
    die("<h1 style='color:red;'>Erro de conexão com o banco de dados: " . $conexao->connect_error . "</h1>");
}

// Lógica para criação, atualização e exclusão de pedidos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'create') {
            $cliente_id = $_POST['cliente_id'];
            $pizza_id = $_POST['pizza_id'];
            $pagamento = $_POST['pagamento'];
            $observacoes = $_POST['observacoes'];

            $sql = "INSERT INTO pedidos (cliente_id, pizza_id, pagamento, observacoes, data_cadastro)
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("iiss", $cliente_id, $pizza_id, $pagamento, $observacoes);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $mensagem = "Pedido criado com sucesso!";
            } else {
                $erro = "Erro ao criar pedido: " . $conexao->error;
            }

        } elseif ($action === 'update') {
            $pedido_id = $_POST['pedido_id'];
            $cliente_id = $_POST['cliente_id'];
            $pizza_id = $_POST['pizza_id'];
            $pagamento = $_POST['pagamento'];
            $observacoes = $_POST['observacoes'];

            $sql = "UPDATE pedidos SET
                    cliente_id = ?,
                    pizza_id = ?,
                    pagamento = ?,
                    observacoes = ?
                    WHERE id = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("iissi", $cliente_id, $pizza_id, $pagamento, $observacoes, $pedido_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $mensagem = "Pedido atualizado com sucesso!";
            } else {
                $erro = "Erro ao atualizar pedido: " . $conexao->error;
            }

        } elseif ($action === 'delete') {
            $pedido_id = $_POST['pedido_id'];

            $sql = "DELETE FROM pedidos WHERE id = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("i", $pedido_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $mensagem = "Pedido excluído com sucesso!";
            } else {
                $erro = "Erro ao excluir pedido: " . $conexao->error;
            }
        }
    }
}

// --- Início da Lógica de Busca Binária e Linear ---
$termo = isset($_POST['termo']) ? trim($_POST['termo']) : '';
$tipo_busca = isset($_POST['tipo_busca']) ? $_POST['tipo_busca'] : 'linear';

// Se o botão 'Mostrar Todos' foi clicado, limpa o termo de busca e tipo_busca
if (isset($_POST['reset']) && $_POST['reset'] == '1') {
    $termo = '';
    $tipo_busca = 'linear';
}

// A query SQL para buscar todos os pedidos DEVE ser ordenada por ID para a busca binária funcionar
$sql_pedidos_todos = "SELECT
                  ped.id as pedido_id,
                  c.nome as cliente,
                  p.nome as pizza,
                  ped.pagamento,
                  ped.observacoes,
                  ped.data_cadastro
                FROM pedidos ped
                JOIN clientes c ON ped.cliente_id = c.id
                JOIN pizzas p ON ped.pizza_id = p.id
                ORDER BY ped.id ASC"; // Importante: Ordenar por ID para a busca binária

$result_pedidos_todos = $conexao->query($sql_pedidos_todos);

$pedidos_dados_completos = [];
if ($result_pedidos_todos && $result_pedidos_todos->num_rows > 0) {
    while($row = $result_pedidos_todos->fetch_assoc()) {
        $pedidos_dados_completos[] = $row;
    }
}

// Funções de busca
function buscaLinear($array, $termo) {
    $resultados = [];
    $termo_lower = mb_strtolower($termo); // Converte para minúsculas para busca case-insensitive
    foreach ($array as $item) {
        // Usa mb_stripos para busca case-insensitive em strings que podem conter acentuação
        if (mb_stripos(mb_strtolower($item['cliente']), $termo_lower) !== false) {
            $resultados[] = $item;
        }
    }
    return $resultados;
}

function buscaBinaria($array, $id) {
    $left = 0;
    $right = count($array) - 1;
    while ($left <= $right) {
        $mid = intdiv($left + $right, 2);
        // Garante que o ID do pedido e o termo de busca sejam tratados como inteiros para comparação
        $mid_id = (int)$array[$mid]['pedido_id'];
        $id_int = (int)$id;

        if ($mid_id === $id_int) {
            // Retorna um array contendo o item encontrado para manter a consistência com buscaLinear
            return [$array[$mid]];
        } elseif ($mid_id < $id_int) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }
    return []; // Retorna um array vazio se não encontrar
}

$pedidos_filtrados = [];
if (!empty($termo)) {
    if ($tipo_busca === 'linear') {
        $pedidos_filtrados = buscaLinear($pedidos_dados_completos, $termo);
    } else { // Busca Binária
        $pedidos_filtrados = buscaBinaria($pedidos_dados_completos, $termo);
    }
} else {
    // Se não há termo de busca, exibe todos os pedidos obtidos do banco (já ordenados por ID)
    $pedidos_filtrados = $pedidos_dados_completos;
}
// --- Fim da Lógica de Busca Binária e Linear ---

// As queries para clientes e pizzas não são afetadas pela busca de pedidos
$clientes = $conexao->query("SELECT id, nome FROM clientes ORDER BY nome");
$pizzas = $conexao->query("SELECT id, nome FROM pizzas ORDER BY nome");

$pedido_edicao = null;
if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $sql = "SELECT * FROM pedidos WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $_GET['editar']);
    $stmt->execute();
    $result = $stmt->get_result();
    $pedido_edicao = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Pizzaria Tabajara - Pedidos</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Josefin+Sans" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nothing+You+Could+Do" rel="stylesheet">

    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">
    <link rel="stylesheet" href="css/magnific-popup.css">
    <link rel="stylesheet" href="css/aos.css">
    <link rel="stylesheet" href="css/ionicons.min.css">
    <link rel="stylesheet" href="css/bootstrap-datepicker.css">
    <link rel="stylesheet" href="css/jquery.timepicker.css">
    <link rel="stylesheet" href="css/flaticon.css">
    <link rel="stylesheet" href="css/icomoon.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="adm.css">

    <style>
        #adm_pedidos {
            height: auto; /* Alterado para auto para se ajustar ao conteúdo */
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            position: relative;
            min-height: 800px;
        }

        #adm_pedidos .one-half.img {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.3;
            object-fit: cover;
        }

        #adm_pedidos h1 {
            margin: 30px 0;
            color: #333;
            text-align: center;
            font-size: 2.5rem;
        }

        .crud-container {
            width: 80%;
            margin: 20px auto;
            background-color: rgba(0, 0, 0, 0.95);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(255, 253, 253, 0.2);
        }

        #lista-pedidos {
            width: 80%;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }

        #lista-pedidos li {
            padding: 15px;
            margin-bottom: 15px;
            background: #fff;
            border-left: 4px solid #F96D00;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        #lista-pedidos li:hover {
            transform: translateX(5px);
        }

        .no-pedidos {
            color: #666;
            text-align: center;
            padding: 30px;
            font-style: italic;
        }

        footer {
            background-color: #000;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: auto;
        }

        footer p {
            color: rgb(148, 147, 146);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            font-weight: bold;
        }

        .btn-primary {
            background-color: #F96D00;
            color: white;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .action-buttons {
            margin-top: 10px;
        }

        .action-buttons a {
            margin-right: 5px;
            text-decoration: none;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.9em;
        }

        .edit-btn {
            background-color: #ffc107;
            color: #212529;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        /* Estilos para o formulário de busca */
        #form_busca {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            width: 80%; /* Ajuste a largura conforme necessário */
            justify-content: center;
            align-items: center;
            flex-wrap: wrap; /* Permite que os elementos quebrem a linha em telas menores */
        }

        #form_busca input[type="text"],
        #form_busca select,
        #form_busca button {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }

        #form_busca button {
            background-color: #F96D00;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #form_busca button:hover {
            background-color: #e06000;
        }

        #form_busca button[name="reset"] {
            background-color: #6c757d; /* Cor para o botão "Mostrar Todos" */
        }

        #form_busca button[name="reset"]:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
        <div class="container" id="menu_adm">
            <a class="navbar-brand" href="index.html"><span class="flaticon-pizza-1 mr-1"></span>Pizzaria<br><small>Tabajara</small></a>
        </div>
    </nav>

    <section id="adm_pedidos">
        <div class="one-half img" style="background-image: url(images/about.jpg);"></div>
        <h1>Gerenciamento de Pedidos</h1>

        <?php if (isset($mensagem)): ?>
            <div class="alert alert-success"><?php echo $mensagem; ?></div>
        <?php endif; ?>

        <?php if (isset($erro)): ?>
            <div class="alert alert-danger"><?php echo $erro; ?></div>
        <?php endif; ?>

        <form method="POST" id="form_busca">
            <input
              type="text"
              name="termo"
              placeholder="Buscar cliente ou ID..."
              value="<?php echo htmlspecialchars($termo); ?>"
            />
            <select name="tipo_busca" required>
              <option value="linear" <?php if($tipo_busca === 'linear') echo 'selected'; ?>>
                Busca Linear (Nome Cliente)
              </option>
              <option value="binaria" <?php if($tipo_busca === 'binaria') echo 'selected'; ?>>
                Busca Binária (ID Pedido)
              </option>
            </select>
            <button type="submit">Buscar</button>
            <?php if (!empty($termo)): ?>
            <button type="submit" name="reset" value="1">Mostrar Todos</button>
            <?php endif; ?>
          </form>

        <div class="crud-container">
            <h2><?php echo $pedido_edicao ? 'Editar Pedido' : 'Criar Novo Pedido'; ?></h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $pedido_edicao ? 'update' : 'create'; ?>">

                <?php if ($pedido_edicao): ?>
                    <input type="hidden" name="pedido_id" value="<?php echo $pedido_edicao['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="cliente_id">Cliente:</label>
                    <select class="form-control" id="cliente_id" name="cliente_id" required>
                        <option value="">Selecione um cliente</option>
                        <?php
                        // Reinicia o ponteiro do resultado para o início antes do loop
                        if ($clientes && $clientes->num_rows > 0) {
                            $clientes->data_seek(0);
                            while ($cliente = $clientes->fetch_assoc()): ?>
                                <option value="<?php echo $cliente['id']; ?>"
                                    <?php if ($pedido_edicao && $pedido_edicao['cliente_id'] == $cliente['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($cliente['nome']); ?>
                                </option>
                            <?php endwhile;
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="pizza_id">Pizza:</label>
                    <select class="form-control" id="pizza_id" name="pizza_id" required>
                        <option value="">Selecione uma pizza</option>
                        <?php
                        if ($pizzas && $pizzas->num_rows > 0) {
                            $pizzas->data_seek(0);
                            while ($pizza = $pizzas->fetch_assoc()): ?>
                                <option value="<?php echo $pizza['id']; ?>"
                                    <?php if ($pedido_edicao && $pedido_edicao['pizza_id'] == $pizza['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($pizza['nome']); ?>
                                </option>
                            <?php endwhile;
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="pagamento">Forma de Pagamento:</label>
                    <select class="form-control" id="pagamento" name="pagamento" required>
                        <option value="Dinheiro" <?php if ($pedido_edicao && $pedido_edicao['pagamento'] == 'Dinheiro') echo 'selected'; ?>>Dinheiro</option>
                        <option value="Cartão" <?php if ($pedido_edicao && $pedido_edicao['pagamento'] == 'Cartão') echo 'selected'; ?>>Cartão</option>
                        <option value="PIX" <?php if ($pedido_edicao && $pedido_edicao['pagamento'] == 'PIX') echo 'selected'; ?>>PIX</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="observacoes">Observações:</label>
                    <textarea class="form-control" id="observacoes" name="observacoes"><?php echo $pedido_edicao ? htmlspecialchars($pedido_edicao['observacoes']) : ''; ?></textarea>
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary"><?php echo $pedido_edicao ? 'Atualizar' : 'Criar'; ?></button>
                    <?php if ($pedido_edicao): ?>
                        <a href="pedidos_autonomo.php" class="btn btn-secondary">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div id="lista-pedidos">
            <h2>Lista de Pedidos</h2>
            <?php if (count($pedidos_filtrados) > 0): ?>
                <ul>
                    <?php foreach ($pedidos_filtrados as $row): ?>
                        <li>
                            <div style="margin-bottom: 5px;"><strong style="color:#F96D00;">Pedido #<?php echo htmlspecialchars($row['pedido_id']); ?></strong></div>
                            <div><b>Cliente:</b> <?php echo htmlspecialchars($row['cliente']); ?></div>
                            <div><b>Pizza:</b> <?php echo htmlspecialchars($row['pizza']); ?></div>
                            <div><b>Pagamento:</b> <?php echo htmlspecialchars($row['pagamento']); ?></div>
                            <div><b>Observações:</b> <?php echo !empty($row['observacoes']) ? htmlspecialchars($row['observacoes']) : 'Nenhuma'; ?></div>
                            <div style="margin-top: 5px; font-size: 0.8em; color: #666;">
                                Data: <?php echo date('d/m/Y H:i', strtotime($row['data_cadastro'])); ?>
                            </div>
                            <div class="action-buttons" style="margin-top: 10px;">
                                <a href="pedidos_autonomo.php?editar=<?php echo $row['pedido_id']; ?>" class="edit-btn">Editar</a>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="pedido_id" value="<?php echo $row['pedido_id']; ?>">
                                    <button type="submit" class="delete-btn" onclick="return confirm('Tem certeza que deseja excluir este pedido?')">Excluir</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="no-pedidos">Nenhum pedido encontrado para o termo: <strong><?php echo htmlspecialchars($termo); ?></strong></p>
            <?php endif; ?>
        </div>
    </section>

    <footer>
        <p>--- Painel de Pedidos da Pizzaria Tabajara ---</p>
    </footer>

    <div
      id="ftco-loader"
      class="show fullscreen"
    >
      <svg
        class="circular"
        width="48px"
        height="48px"
      >
        <circle
          class="path-bg"
          cx="24"
          cy="24"
          r="22"
          fill="none"
          stroke-width="4"
          stroke="#eeeeee"
        />
        <circle
          class="path"
          cx="24"
          cy="24"
          r="22"
          fill="none"
          stroke-width="4"
          stroke-miterlimit="10"
          stroke="#F96D00"
        />
      </svg>
    </div>

    <script src="js/jquery.min.js"></script>
    <script src="js/jquery-migrate-3.0.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.easing.1.3.js"></script>
    <script src="js/jquery.waypoints.min.js"></script>
    <script src="js/jquery.stellar.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/aos.js"></script>
    <script src="js/jquery.animateNumber.min.js"></script>
    <script src="js/bootstrap-datepicker.js"></script>
    <script src="js/jquery.timepicker.min.js"></script>
    <script src="js/scrollax.min.js"></script>
    <script
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"
    ></script>
    <script src="js/google-map.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
