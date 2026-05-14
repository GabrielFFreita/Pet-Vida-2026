<!-- Essa página vai ser dedeicada para fazer a conexão com o banco de dados -->

<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Isso aqui em cima liga os "erros", pq senão o php quando der erro aparece apenas uma tela branca.

$host = "localhost";
$dbname = "petvida";
$usuario = "root";
$senha = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>