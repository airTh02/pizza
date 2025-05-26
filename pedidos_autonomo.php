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


$termo = isset($_POST['termo']) ? trim($_POST['termo']) : '';
$tipo_busca = isset($_POST['tipo_busca']) ? $_POST['tipo_busca'] : 'linear';


$sql = "SELECT 
          ped.id as pedido_id,
          c.nome as cliente, 
          p.nome as pizza, 
          ped.pagamento, 
          ped.observacoes,
          ped.data_cadastro
        FROM pedidos ped
        JOIN clientes c ON ped.cliente_id = c.id
        JOIN pizzas p ON ped.pizza_id = p.id
        ORDER BY ped.id ASC"; 

$result = $conexao->query($sql);

$pedidos = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }
}


function buscaLinear($array, $termo) {
    $resultados = [];
    $termo_lower = mb_strtolower($termo);
    foreach ($array as $item) {
        if (mb_stripos($item['cliente'], $termo) !== false) {
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
        $mid_id = (int)$array[$mid]['pedido_id'];
        if ($mid_id === (int)$id) {
            return [$array[$mid]]; 
        } elseif ($mid_id < (int)$id) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }
    return []; 
}


if (!empty($termo)) {
    if ($tipo_busca === 'linear') {
        $pedidos_filtrados = buscaLinear($pedidos, $termo);
    } else { 
        $pedidos_filtrados = buscaBinaria($pedidos, $termo);
    }
} else {

    $pedidos_filtrados = $pedidos;
}


$lista_pedidos_html = "";

if (count($pedidos_filtrados) > 0) {
    foreach ($pedidos_filtrados as $row) {
        $lista_pedidos_html .= "<li>";
        $lista_pedidos_html .= "<div style='margin-bottom: 5px;'><strong style='color:#F96D00;'>Pedido #" . htmlspecialchars($row['pedido_id']) . "</strong></div>";
        $lista_pedidos_html .= "<div><b>Cliente:</b> " . htmlspecialchars($row['cliente']) . "</div>";
        $lista_pedidos_html .= "<div><b>Pizza:</b> " . htmlspecialchars($row['pizza']) . "</div>";
        $lista_pedidos_html .= "<div><b>Pagamento:</b> " . htmlspecialchars($row['pagamento']) . "</div>";
        $lista_pedidos_html .= "<div><b>Observações:</b> " . (!empty($row['observacoes']) ? htmlspecialchars($row['observacoes']) : 'Nenhuma') . "</div>";
        $lista_pedidos_html .= "<div style='margin-top: 5px; font-size: 0.8em; color: #666;'>";
        $lista_pedidos_html .= "Data: " . date('d/m/Y H:i', strtotime($row['data_cadastro']));
        $lista_pedidos_html .= "</div>";
        $lista_pedidos_html .= "</li>";
    }
} else {
    $lista_pedidos_html = "<li class='no-pedidos'>Nenhum pedido encontrado para o termo: <strong>" . htmlspecialchars($termo) . "</strong></li>";
}

$conexao->close();
?>


<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <title>Pizzaria Tabajara - Pedidos</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Josefin+Sans" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Nothing+You+Could+Do" rel="stylesheet" />

    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css" />
    <link rel="stylesheet" href="css/animate.css" />
    <link rel="stylesheet" href="css/owl.carousel.min.css" />
    <link rel="stylesheet" href="css/owl.theme.default.min.css" />
    <link rel="stylesheet" href="css/magnific-popup.css" />
    <link rel="stylesheet" href="css/aos.css" />
    <link rel="stylesheet" href="css/ionicons.min.css" />
    <link rel="stylesheet" href="css/bootstrap-datepicker.css" />
    <link rel="stylesheet" href="css/jquery.timepicker.css" />
    <link rel="stylesheet" href="css/flaticon.css" />
    <link rel="stylesheet" href="css/icomoon.css" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="adm.css" />

    <style>
      #adm_pedidos {
        height: 800px;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px;
        position: relative;
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

      /* Estilos para a lista de pedidos */
      #lista-pedidos {
        width: 80%;
        max-height: 600px;
        overflow-y: auto;
        background-color: rgba(255, 255, 255, 0.95);
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
      }

      #lista-pedidos li {
        padding: 15px;
        margin-bottom: 15px;
        background: #fff;
        border-left: 4px solid #f96d00;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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

      /* Estilos para o formulário de busca */
      form#form_busca {
        margin-bottom: 20px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
      }

      form#form_busca input[type="text"] {
        padding: 8px;
        border-radius: 5px;
        border: 1px solid #ccc;
        min-width: 250px;
        font-size: 1rem;
      }

      form#form_busca select {
        padding: 8px;
        border-radius: 5px;
        border: 1px solid #ccc;
        font-size: 1rem;
        min-width: 200px;
      }

      form#form_busca button {
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        font-size: 1rem;
        color: white;
        background-color: #f96d00;
        transition: background-color 0.2s;
      }

      form#form_busca button[name="reset"] {
        background-color: #666;
      }

      form#form_busca button:hover {
        background-color: #d46000;
      }

      form#form_busca button[name="reset"]:hover {
        background-color: #555;
      }
    </style>
  </head>
  <body>
    <nav
      class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light"
      id="ftco-navbar"
    >
      <div class="container" id="menu_adm">
        <a class="navbar-brand" href="index.html"
          ><span class="flaticon-pizza-1 mr-1"></span>Pizzaria<br /><small
            >Tabajara</small
          ></a>
      </div>
    </nav>

    <section id="adm_pedidos">
      <form method="POST" id="form_busca">
        <input
          type="text"
          name="termo"
          placeholder="Buscar cliente ou ID..."
          required
          value="<?php echo isset($termo) ? htmlspecialchars($termo) : ''; ?>"
        />
        <select name="tipo_busca" required>
          <option value="linear" <?php if(isset($tipo_busca) && $tipo_busca === 'linear') echo 'selected'; ?>>
            Busca Linear (Nome Cliente)
          </option>
          <option value="binaria" <?php if(isset($tipo_busca) && $tipo_busca === 'binaria') echo 'selected'; ?>>
            Busca Binária (ID Pedido)
          </option>
        </select>
        <button type="submit">Buscar</button>
        <?php if (!empty($termo)): ?>
        <button type="submit" name="reset" value="1">Mostrar Todos</button>
        <?php endif; ?>
      </form>

      <div class="one-half img" style="background-image: url(images/about.jpg)"></div>
      <h1>Lista de Pedidos</h1>
      <ul id="lista-pedidos">
        <?php echo $lista_pedidos_html; ?>
      </ul>
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
