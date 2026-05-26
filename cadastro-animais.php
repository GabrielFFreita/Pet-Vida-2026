<?php
require_once "conexao.php";
session_start();// Início da sessão

// Verificação se o usuário está ativo
if (!isset($_SESSION['nome_usuario'])){
    header("Location: index.html");
    exit();
}
    

    // Busca das informações colocadas no form do html, dentro de variávies do php
if ($_SERVER["REQUEST_METHOD"] == "POST"){
$nome          = trim($_POST['nome_animal']);
$especie       = trim($_POST['especie_animal']);
$raca          = trim($_POST['raca_animal']);
$idade         = trim($_POST['idade_animal']);
$sexo          = trim($_POST['sexo_animal']);
$descricao     = trim($_POST['descricao_animal']);
$peso          = trim($_POST['peso_animal']);
$porte         = trim($_POST['porte_animal']);
$data_cadastro = trim($_POST['data_cadastro']);
$origem        = trim($_POST['origem_animal']);

// variável com o código em sql
$sql = "INSERT INTO animais_adocao (
    nome, 
    especie, 
    raca, 
    idade, 
    sexo, 
    descricao, 
    peso, 
    porte, 
    data_cadastro, 
    origem
) VALUES (
    :nome, 
    :especie, 
    :raca, 
    :idade, 
    :sexo, 
    :descricao, 
    :peso, 
    :porte, 
    :data_cadastro, 
    :origem
);";
// Preparação do código em sql
$stmt = $pdo->prepare($sql);

// Preparação das variáveis stmt para serem colocadas no sql
$stmt->bindParam(":nome", $nome);
$stmt->bindParam(":especie", $especie);
$stmt->bindParam(":raca", $raca);
$stmt->bindParam(":idade", $idade);
$stmt->bindParam(":sexo", $sexo);
$stmt->bindParam(":descricao", $descricao);
$stmt->bindParam(":peso", $peso);
$stmt->bindParam(":porte", $porte);
$stmt->bindParam(":data_cadastro", $data_cadastro);
$stmt->bindParam(":origem", $origem);

// Execução do código sql
$stmt->execute();

}
?>