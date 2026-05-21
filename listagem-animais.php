<?php
require_once "conexao.php"
session_start()

if (!isset($_SESSION["nome_usuario"])) {
    header("Location: index.html");
    exit();
}

// Buscando os animais na tabela de animais e fazendo sua listagem no painel admin
try{
    $sql = "SELECT * FROM animais"
    $stmt = $pdo ->query($sql)
    $animais = $stmt->fetchall(PDO::FETCH_ASSOC);
}
catch{
    die("Erro ao buscar animais" . $e->getMessage());
}
?>