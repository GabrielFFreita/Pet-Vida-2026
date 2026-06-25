<?php
require_once "config_sessao.php";
verificarLogado();

// Proteção extra: bloqueia usuários não-admin mesmo que estejam logados
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'admin') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo | Pet Vida</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@400;600&family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

    <aside class="sidebar">
        <h2>Pet Vida Admin</h2>
        <nav>
            <ul>
                <li class="active"><a href="adimpage.php">Visão Geral</a></li>
                <li><a href=".animais.php">Animais</a></li>
                <li><a href="abrigos.php">Abrigos</a></li>
                <li><a href="usuarios.php">Usuários</a></li>
                <li><a href="index.php">Sair do Painel</a></li>
            </ul>
        </nav>
    </aside>

    <main class="content">
        <h1>Visão Geral do Sistema</h1>
        <p class="subtitulo">Gerencie as adoções, abrigos parceiros e métricas operacionais.</p>

        <section class="dashboard-grid">
            <div class="card-metrica">
                <h3>Animais Disponíveis</h3>
                <div class="valor">42</div>
            </div>
            <div class="card-metrica">
                <h3>Abrigos Cadastrados</h3>
                <div class="valor">8</div>
            </div>
            <div class="card-metrica">
                <h3>Adoções Realizadas</h3>
                <div class="valor">156</div>
            </div>
            <div class="card-metrica">
                <h3>Novos Pedidos</h3>
                <div class="valor">12</div>
            </div>
        </section>

        </main>

</body>
</html>