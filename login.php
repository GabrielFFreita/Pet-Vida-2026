<?php
require_once "conexao.php"
session_start()

if (!isset($_SESSION['nome_usuario'])){
    header("Location:cadastro.html");
    exit();
}
?>