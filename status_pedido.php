<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'conexao.php'; 

if (!isset($_GET['pedido_id']) || !is_numeric($_GET['pedido_id'])) {
    
    header("Location: index.html"); 
    exit();
}

$pedido_id = $_GET['pedido_id'];

$cliente_nome = "Não encontrado";
$pizza_nome = "Não encontrada";
$pizza_preco = 0.00;
$pagamento = "Não informado";
$observacoes = "Nenhuma";
$data_pedido = "Não informada";
$total_pedido = 0.00; 


$sql = "SELECT 
          p.id as pedido_id,
          c.nome as cliente_nome, 
          pz.nome as pizza_nome, 
          pz.preco as pizza_preco,
          p.pagamento, 
          p.observacoes,
          p.data_cadastro
        FROM pedidos p
        JOIN clientes c ON p.cliente_id = c.id
        JOIN pizzas pz ON p.pizza_id = pz.id
        WHERE p.id = ?";

$stmt = $conexao->prepare($sql);

if ($stmt === false) {
    die("Erro na preparação da consulta: " . $conexao->error);
}

$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $pedido = $result->fetch_assoc();
    
    $cliente_nome = htmlspecialchars($pedido['cliente_nome']);
    $pizza_nome = htmlspecialchars($pedido['pizza_nome']);
    $pizza_preco = floatval($pedido['pizza_preco']); 
    $pagamento = htmlspecialchars($pedido['pagamento']);
    $observacoes = !empty($pedido['observacoes']) ? htmlspecialchars($pedido['observacoes']) : 'Nenhuma';
    $data_pedido = date('d/m/Y H:i', strtotime($pedido['data_cadastro']));
    
    $total_pedido = $pizza_preco; 
    
} else {
    header("Location: index.html"); 
    exit();
}

$stmt->close();
$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <title>Pizzaria Tabajara - Status do Pedido</title>
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

    <style>
      .delivery-status-section {
        padding: 8em 0;
        background-color: #f8f8f8; 
      }
      .delivery-status-container {
        max-width: 800px;
        margin: 0 auto;
        background: #fff;
        padding: 40px;
        border-radius: 8px;
        box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.1);
      }
      .delivery-status-header {
        text-align: center;
        margin-bottom: 40px;
      }
      .delivery-status-header h2 {
        font-family: 'Josefin Sans', sans-serif;
        color: #bb2a2a; 
        margin-bottom: 10px;
      }
      .delivery-status-header p {
        color: #8c8c8c;
      }
      .progress-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        position: relative;
        padding: 0 20px;
      }
      .progress-line {
        position: absolute;
        top: 50%;
        left: 20px;
        right: 20px;
        height: 4px;
        background-color: #eee;
        z-index: 1;
        transform: translateY(-50%);
      }
      .progress-line-filled {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        background-color: #bb2a2a; 
        width: 0%; 
        transition: width 0.5s ease-in-out;
      }
      .progress-step {
        width: 30px;
        height: 30px;
        background-color: #eee;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: bold;
        color: #fff;
        z-index: 2;
        transition: background-color 0.3s ease-in-out;
      }
      .progress-step.active {
        background-color: #bb2a2a; 
      }
      .progress-labels {
        display: flex;
        justify-content: space-between;
        text-align: center;
        margin-top: 10px;
      }
      .progress-labels div {
        flex: 1;
        font-size: 0.9em;
        color: #8c8c8c;
      }
      .order-details {
        margin-top: 40px;
        border-top: 1px solid #eee;
        padding-top: 30px;
      }
      .order-details h3 {
        font-family: 'Josefin Sans', sans-serif;
        color: #333;
        margin-bottom: 20px;
      }
      .order-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        padding-bottom: 5px;
        border-bottom: 1px dashed #eee;
      }
      .order-item:last-child {
        border-bottom: none;
      }
      .order-item .name {
        font-weight: 500;
        color: #555;
      }
      .order-item .price {
        color: #bb2a2a; 
        font-weight: 600;
      }
      .total-price {
        text-align: right;
        margin-top: 20px;
        font-size: 1.2em;
        font-weight: bold;
        color: #bb2a2a;
      }
    </style>

  </head>
  <body>
  	<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
	    <div class="container">
		      <a class="navbar-brand" href="index.html"><span class="flaticon-pizza-1 mr-1"></span>Pizzaria<br><small>Tabajara</small></a>
		      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
		        <span class="oi oi-menu"></span> Menu
		      </button>
	      <div class="collapse navbar-collapse" id="ftco-nav">
	        <ul class="navbar-nav ml-auto">
	          <li class="nav-item"><a href="index.html" class="nav-link">Ínicio</a></li>
	          <li class="nav-item"><a href="menu.php" class="nav-link">Cardápio</a></li>
            <li class="nav-item active" style="display: flex; align-items: center; border: 1;"><a href="login.php" class="nav-link">ADM</a></li>
	      </div>
		  </div>
	  </nav>
 
    <section class="home-slider owl-carousel img" style="background-image: url(images/bg_1.jpg); box-shadow: 0 5px 10px rgba(0, 0, 0, 0.4);">
      <div class="slider-item">
      	<div class="overlay"></div>
        <div class="container">
          <div class="row slider-text align-items-center" data-scrollax-parent="true">
            <div class="col-md-12 col-sm-12 ftco-animate text-center">
            	<span class="subheading">Seu Pedido</span>
              <h1 class="mb-4">Status da Entrega</h1>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="ftco-section delivery-status-section">
      <div class="container delivery-status-container">
        <div class="delivery-status-header">
          <h2>Seu Pedido #<?php echo htmlspecialchars($pedido_id); ?> está a caminho!</h2>
          <p>Acompanhe o status da sua entrega em tempo real.</p>
          <p>Realizado em: <?php echo htmlspecialchars($data_pedido); ?></p>
        </div>

        <div class="progress-container">
          <div class="progress-line">
            <div class="progress-line-filled" id="progress-line-filled"></div>
          </div>
          <div class="progress-step active" id="step1">1</div>
          <div class="progress-step" id="step2">2</div>
          <div class="progress-step" id="step3">3</div>
          <div class="progress-step" id="step4">4</div>
        </div>
        <div class="progress-labels">
          <div>Pedido Recebido</div>
          <div>Preparando</div>
          <div>Saiu para Entrega</div>
          <div>Entregue</div>
        </div>

        <div class="order-details">
          <h3>Detalhes do Pedido</h3>
          <div class="order-item">
            <span class="name">Pizza: <?php echo $pizza_nome; ?></span>
            <span class="price">R$ <?php echo number_format($pizza_preco, 2, ',', '.'); ?></span>
          </div>
          <div class="order-item">
            <span class="name">Cliente: <?php echo $cliente_nome; ?></span>
            <span class="price"></span>
          </div>
          <div class="order-item">
            <span class="name">Pagamento: <?php echo $pagamento; ?></span>
            <span class="price"></span>
          </div>
           <div class="order-item">
            <span class="name">Observações: <?php echo $observacoes; ?></span>
            <span class="price"></span>
          </div>
          <div class="order-item">
            <span class="name">Taxa de Entrega</span>
            <span class="price">R$ 5,00</span> </div>
          <div class="total-price">
            Total: R$ <?php echo number_format($total_pedido + 5.00, 2, ',', '.'); ?> </div>
        </div>
      </div>
    </section>

    <footer class="ftco-footer ftco-section img">
    	<div class="overlay"></div>
      <div class="container">
        <div class="row mb-5">
          <div class="col-lg-3 col-md-6 mb-5 mb-md-5">
            <div class="ftco-footer-widget mb-4">
              <h2 class="ftco-heading-2">Sobre nós</h2>
              <p>Em algum lugar, entre sabores, forno aceso e boas histórias, nasceu a Pizzaria Tabajara, criada por Thiago, Erick e Carlos. Aqui, a paixão pela pizza se transforma em sabor a cada fatia.</p>
              <ul class="ftco-footer-social list-unstyled float-md-left float-lft mt-5">
                <li class="ftco-animate"><a href="#"><span class="icon-twitter"></span></a></li>
                <li class="ftco-animate"><a href="#"><span class="icon-facebook"></span></a></li>
                <li class="ftco-animate"><a href="#"><span class="icon-instagram"></span></a></li>
              </ul>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 mb-5 mb-md-5">
            <div class="ftco-footer-widget mb-4">
              <h2 class="ftco-heading-2">Blog Recente</h2>
              <div class="block-21 mb-4 d-flex">
                <a class="blog-img mr-4" style="background-image: url(images/image_1.jpg);"></a>
                <div class="text">
                  <h3 class="heading"><a href="#">Até os maiores apaixonados por pizza não resistem a uma boa pasta. Descubra como preparamos nossas massas frescas, com ingredientes selecionados e muito amor.</a></h3>
                  <div class="meta">
                    <div><a href="#"><span class="icon-calendar"></span> 21 de Maio, 2025</a></div>
                    <div><a href="#"><span class="icon-person"></span> Admin</a></div>
                    <div><a href="#"><span class="icon-chat"></span> 19</a></div>
                  </div>
                </div>
              </div>
              <div class="block-21 mb-4 d-flex">
                <a class="blog-img mr-4" style="background-image: url(images/image_2.jpg);"></a>
                <div class="text">
                  <h3 class="heading"><a href="#">Chegaram as nossas opções de massas: penne, spaghetti e fettuccine, com molhos incríveis feitos na casa. Uma nova experiência de sabor na Tabajara.</a></h3>
                  <div class="meta">
                    <div><a href="#"><span class="icon-calendar"></span> 26 de Maio, 2025</a></div>
                    <div><a href="#"><span class="icon-person"></span> Admin</a></div>
                    <div><a href="#"><span class="icon-chat"></span> 19</a></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-2 col-md-6 mb-5 mb-md-5">
             <div class="ftco-footer-widget mb-4 ml-md-4">
              <h2 class="ftco-heading-2">Services</h2>
              <ul class="list-unstyled">
                <li><a href="#" class="py-2 d-block">Preparado com carinho</a></li>
                <li><a href="#" class="py-2 d-block">Entrega rápida</a></li>
                <li><a href="#" class="py-2 d-block">Alimentos de qualidade</a></li>
                <li><a href="#" class="py-2 d-block">Sabores variados</a></li>
              </ul>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 mb-5 mb-md-5">
            <div class="ftco-footer-widget mb-4">
            	<h2 class="ftco-heading-2">Dúvidas</h2>
            	<div class="block-23 mb-3">
	              <ul>
	                <li><span class="icon icon-map-marker"></span><span class="text">SCLS 205 Bloco C Loja 12 – Asa Sul, Brasília – DF, 70232-530</span></li>
	                <li><a href="#"><span class="icon icon-phone"></span><span class="text">+55 61 98138-2823</span></a></li>
	                <li><a href="#"><span class="icon icon-envelope"></span><span class="text">pizzariatabajara@tabajara.com</span></a></li>
	              </ul>
	            </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 text-center">

            
          </div>
        </div>
      </div>
    </footer>
    
  

  <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/></svg></div>


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
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
  <script src="js/google-map.js"></script>
  <script src="js/main.js"></script>
  <script src="js/adm.js"></script>

  <script>
    $(document).ready(function() {
      let currentStep = 1; 

      function updateProgressBar(step) {
        const totalSteps = 4;
        const progressPercentage = ((step - 1) / (totalSteps - 1)) * 100;
        $('#progress-line-filled').css('width', progressPercentage + '%');

        $('.progress-step').removeClass('active');
        for (let i = 1; i <= step; i++) {
          $(`#step${i}`).addClass('active');
        }
      }

      setTimeout(() => {
        currentStep = 2; // "Preparando"
        updateProgressBar(currentStep);
      }, 3000); 

      setTimeout(() => {
        currentStep = 3; // "Saiu para Entrega"
        updateProgressBar(currentStep);
      }, 7000); 

      setTimeout(() => {
        currentStep = 4; // "Entregue"
        updateProgressBar(currentStep);
      }, 12000); 

      updateProgressBar(currentStep);
    });
  </script>
    
  </body>
</html>
