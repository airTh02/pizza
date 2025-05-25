<?php
$senha_pura = 'tabajara'; // ESCOLHA SUA SENHA OU DEIXA COMO TA
$senha_hashed = password_hash($senha_pura, PASSWORD_DEFAULT);
echo "O hash para '{$senha_pura}' é: " . $senha_hashed;
?>