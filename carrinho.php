<?php
require_once "conexao.php"
session_start()

if ($_SERVER["REQUEST_METHOD"] == "POST") {
//    Aqui é pego o id do formulário html e colocado em uma variável
    $id_produto = $_POST["produto_id"]; 
    
// Aqui é feita a criação de uma variável session (carrinho) e uma verificação se o item já foi adicionado ao carrinho, e adicionado a um array
    if (!isset($_SESSION['carrinho'][$id_produto])) {
       
        $_SESSION['carrinho'][$id_produto] = [
            'nome' => 'Nome do Produto vindo do Banco',
            'preco' => 99.90,
            'quantidade' => 1
        ];
    } else {
        // caso ele não for, o item é adicionado a primeira vez ao carrinho
        $_SESSION['carrinho'][$id_produto]['quantidade'] += 1;
    }
    // Aqui após a verificação de tudo e a adição do item ao carrinho, o usuário é redirecionado a página de carrinho
    header("Location: carrinho.php");
    exit();
}
?>