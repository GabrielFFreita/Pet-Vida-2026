<?php
require_once "conexao.php";
session_start();

if (!isset($_SESSION["nome_usuario"])) {
    header("Location: index.html");
    exit();
}
// Selecionando a tabela de usuários e listando eles com o select
try{
    $sql = "SELECT * FROM usuarios";

    $stmt = $pdo ->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
      die("Erro ao buscar usuários: " . $e->getMessage());
}



?>