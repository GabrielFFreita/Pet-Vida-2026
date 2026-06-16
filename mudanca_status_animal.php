<?php
require_once "conexao.php";
require_once "config_sessao.php";
verificarLogado();

if (isset($_POST['btn-confirmar-adocao'])) {

    $valor_btn = $_POST['btn-confirmar-adocao'];

    $sql = "ALTER TABLE animais_adocao  ";

    $stmt = $pdo->prepare($sql)


}


?>