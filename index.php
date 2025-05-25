<?php ob_start(); ?>
<?php $page_content = file_get_contents('index.html'); ?>
<?php ob_end_clean(); ?>
<?php
$page_content = str_replace('menu.html', 'menu.php', $page_content);
$page_content = str_replace("adm_abrir()", "location.href='adm.php'", $page_content);
echo $page_content;
?>
