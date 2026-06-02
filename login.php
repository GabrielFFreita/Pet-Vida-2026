<?php
require_once "conexao.php";
require_once "config_sessao.php";


if (!isset($_SESSION['nome_usuario'])){
    header("Location:dados.html");
    exit();
}
?>