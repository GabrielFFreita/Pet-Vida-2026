<?php
    require_once 'conexao.php';
    session_start(); //Iniício das seções

    // Aqui em baixo são pegas as informações do formulário de dados e colocadas em variáveis
if ($_SERVER["REQUEST_METHOD"] == 'POST'){
     $nome = trim($_POST['nome_usuario'] ?? "");
     $nome_login = trim($_POST['nome_login'] ?? "");
     $idade = trim($_POST['idade'] ?? "");
     $email = trim($_POST['email'] ?? "");
     $senha = trim($_POST['senha'] ?? "");
        // Agora serão verificados se os campos foram preenchidos e nenhuma informação está indo vazia
        if (empty($nome) || empty($idade) || empty($email) || empty($senha)){
            die ("Preencha todos os campos obrigatórios!");
        }

        // Aqui acontece a transformação da senha em hash
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        // Aqui todas as informações são preparadas para serem transferidas para o banco de dados

        try{
            $sql = "INSERT INTO usuario (nome, email, senha) VALUES (:nome, :email, :senha)";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":nome", $nome);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":senha", $senhaHash);

            $stmt->execute();

                
            $_SESSION["nome_usuario"] = $nome;

            echo "
            <script>
                alert('Cadastro realizado com sucesso!');
                window.location.href='entrar.php';
            </script>
            ";
        } catch (PDOException $e) {
            die("Erro ao salvar no banco: " . $e->getMessage());
        }
     }
      

    


    
?>