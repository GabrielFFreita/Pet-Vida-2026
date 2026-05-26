<?php
require_once "conexao.php";
session_start();

if(!isset($_SESSION['nome_usuario'])){
    header("Location: index.html");
    exit();
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    header("Location: listagem-usuarios.php");
    exit();
}
?>