<?php
require_once "conexao.php";
require_once "config_sessao.php";

if (!isset($_SESSION["nome_usuario"])) {
    header("Location: index.html");
    exit();
}

// Buscando os animais na tabela de animais e fazendo sua listagem no painel admin
try{
    $sql = "SELECT * FROM animais_adocao";
    $stmt = $pdo ->query($sql);

    $animais = $stmt->fetchall(PDO::FETCH_ASSOC);
}
catch (PDOException $e)  {
    die("Erro ao buscar animais" . $e->getMessage());
}
?>