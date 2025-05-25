<?php include 'conexao.php'; ?>
<?php ob_start(); ?>
<?php $page_content = file_get_contents('menu.html'); ?>
<?php ob_end_clean(); ?>

<?php
$select = "";
$sql = "SELECT id, nome FROM pizzas";
$result = $conexao->query($sql);
while($row = $result->fetch_assoc()) {
    $select .= "<option value='{$row['id']}'>{$row['nome']}</option>";
}

$replacements = [
    '<form>' => '<form action="fazer_pedido.php" method="post">',
    'id="exampleInputPassword1" placeholder="Digite seu nome"' => 'name="nome" id="nome" placeholder="Digite seu nome" required',
    'id="exampleInputPassword1" placeholder="Digite seu telefone"' => 'name="telefone" id="telefone" placeholder="Digite seu telefone" required',
    'id="exampleInputPassword1" placeholder="Digite sua quadra"' => 'name="quadra" id="quadra" placeholder="Digite sua quadra"',
    'id="exampleInputPassword1" placeholder="Digite o numero da sua casa"' => 'name="casa" id="casa" placeholder="Digite o numero da sua casa"',
    'id="exampleInputPassword1" placeholder="Digite seu CEP"' => 'name="cep" id="cep" placeholder="Digite seu CEP"',
    '<select class="form-control " id="exampleFormControlSelect1">' => '<select name="pizza_id" class="form-control" id="pizza_id" required>',
    '<select class="form-control" id="exampleFormControlSelect1">' => '<select name="pagamento" class="form-control" id="pagamento" required>',
    'id="exampleFormControlTextarea1" rows="3"' => 'name="observacoes" id="observacoes" rows="3"'
];

foreach ($replacements as $search => $replace) {
    $page_content = str_replace($search, $replace, $page_content);
}

$page_content = preg_replace('/<select name="pizza_id".*?<\/select>/s', 
    '<select name="pizza_id" class="form-control" id="pizza_id" required>'.$select.'</select>', $page_content);

echo $page_content;
?>