<?php
require_once 'conexao.php';
require_once 'config_sessao.php';
// Aqui são pegas as variávis do form do html
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_digitado = trim($_POST['nome_login'] ?? "");
    $senha_digitada = trim($_POST['senha'] ?? "");
// Aqui elas são verificadas para saber se algum campo está em branco
    if (!empty($login_digitado) && !empty($senha_digitada)) {
        try {
            // Procura o usuário pelo login informado
            $sql = "SELECT * FROM usuario WHERE email = :login LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":login", $login_digitado);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verifica se achou o usuário e se a senha digitada bate com o hash do banco
            if ($usuario && password_verify($senha_digitada, $usuario['senha'])) {
                
                // LOGIN REALIZADO COM SUCESSO: Cria as variáveis na sessão
                $_SESSION["nome_usuario"] = $usuario['nome'];
                $_SESSION['ultima_atividade'] = time();

                // Direciona para o painel principal do sistema
                header("Location: listagem-animais.php");
                exit();
            } else {
                echo "<script>alert('Usuário ou senha incorretos!'); window.history.back();</script>";
            }
        } catch (PDOException $e) {
            die("Erro no sistema: " . $e->getMessage());
        }
    } else {
        echo "<script>alert('Preencha todos os campos!'); window.history.back();</script>";
    }
}
?>