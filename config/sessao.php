<?php
require_once __DIR__ . '/../includes/helpers.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$tempo_limite = 1800; // 30 minutos de inatividade

if (isset($_SESSION['ultima_atividade'])) {
    $inatividade = time() - $_SESSION['ultima_atividade'];
    
    if ($inatividade > $tempo_limite) {
        // Marca na sessão que a queda foi por inatividade antes de limpar
        session_start();
        $_SESSION['sessao_expirada_por_tempo'] = true;
        
        session_unset();
        session_destroy();
    }
}

// Se a sessão ainda existir, atualiza o tempo da última atividade
if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['nome_usuario'])) {
    $_SESSION['ultima_atividade'] = time();
}

function verificarLogado() {
    if (!isset($_SESSION["nome_usuario"])) {
        // Para páginas PHP restritas (como painel admin), manda de volta de forma segura
        header("Location: " . rootPath("index.php"));
        exit();
    }
}
function verificarAdmin() {
    verificarLogado();

    if (strtolower(trim($_SESSION["perfil"] ?? "")) !== "admin") {
        header("Location: " . rootPath("index.php"));
        exit();
    }
}
?>
