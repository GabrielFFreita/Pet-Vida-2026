<?php
require_once "conexao.php";
require_once "config_sessao.php";
verificarLogado();


// Selecionando a tabela de usuários e listando eles com o select
try{
    $sql = "SELECT * FROM usuario";

    $stmt = $pdo ->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
      die("Erro ao buscar usuários: " . $e->getMessage());
}



?>