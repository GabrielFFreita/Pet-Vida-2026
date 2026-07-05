<?php
// Essa página é dedicada a fazer a conexão com o banco de dados.
// (Comentário movido pra dentro das tags PHP — texto fora do <?php 
// é impresso literalmente na tela, o que quebra endpoints que precisam
// devolver JSON puro, como o solicitar_adocao.php e o dashboard.

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Isso aqui em cima liga os "erros", pq senão o php quando der erro aparece apenas uma tela branca.

$host = "tini.click";
$dbname = "petvida_db";
$usuario = "petvida_db";
$senha = "4287816f7bc22c82a83f70ad492266db";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

?>