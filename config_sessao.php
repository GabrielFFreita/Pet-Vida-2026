<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$tempo_limite = 120; // 2 minutos de inatividade

if (isset($_SESSION['ultima_atividade'])) {
    $inatividade = time() - $_SESSION['ultima_atividade'];
    
    if ($inatividade > $tempo_limite) {
        session_unset();
        session_destroy();
        echo "<script>alert('A sua sessão expirou por inatividade.'); window.location.href='entrar.php';</script>";
        exit();
    }
}

$_SESSION['ultima_atividade'] = time();

function verificarLogado() {
    if (!isset($_SESSION["nome_usuario"])) {
        header("Location: entrar.php");
        exit();
    }
}
?>