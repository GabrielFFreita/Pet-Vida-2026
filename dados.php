<?php
    require_once 'conexao.php';
    require_once "config_sessao.php"; 
 
if ($_SERVER["REQUEST_METHOD"] == 'POST') {
     $nome            = trim($_POST['nome_usuario'] ?? "");
     $email           = trim($_POST['email'] ?? "");
     $nome_login      = trim($_POST['nome_login'] ?? "");
     $idade           = trim($_POST['idade'] ?? null);
     $senha           = trim($_POST['senha'] ?? "");
     $telefone        = trim($_POST['telefone'] ?? "");
     $cpf             = trim($_POST['cpf'] ?? "");
     $data_nascimento = trim($_POST['data_nascimento'] ?? "");
     $cidade          = trim($_POST['cidade'] ?? "");
     $estado          = trim($_POST['estado'] ?? "");
     $endereco        = trim($_POST['endereco'] ?? "");
     
     if (empty($nome) || empty($email) || empty($senha)) {
         die("Preencha todos os campos obrigatórios!");
     }

     $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

     try {
         $sql = "INSERT INTO usuarios (nome, email, senha, nome_login, idade, telefone, cpf, data_cadastro, cidade, estado, endereco) 
                 VALUES (:nome, :email, :senha, :nome_login, :idade, :telefone, :cpf, NOW(), :cidade, :estado, :endereco)";

         $stmt = $pdo->prepare($sql);
         $stmt->bindParam(":nome", $nome);
         $stmt->bindParam(":email", $email);
         $stmt->bindParam(":senha", $senhaHash);
         $stmt->bindParam(":nome_login", $nome_login);
         $stmt->bindParam(":idade", $idade);
         $stmt->bindParam(":telefone", $telefone);
         $stmt->bindParam(":cpf", $cpf);
         $stmt->bindParam(":cidade", $cidade);
         $stmt->bindParam(":estado", $estado);
         $stmt->bindParam(":endereco", $endereco);

         $stmt->execute();

         $_SESSION["nome_usuario"] = $nome;

         header('Content-Type: application/json');
         echo json_encode(["success" => true, "usuario" => ["nome" => $nome]]);
         exit();
     } catch (PDOException $e) {
         header('Content-Type: application/json');
         echo json_encode(["success" => false, "error" => $e->getMessage()]);
         exit();
     }
}
?>