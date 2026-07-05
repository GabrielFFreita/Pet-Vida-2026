<?php
$pageTitle = $pageTitle ?? 'Pet Vida - Adote um Amigo';
$bodyClass = $bodyClass ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&family=Poppins:wght@400;600&family=Playfair+Display:ital,wght@0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=10">
</head>
<body<?php echo $bodyClass !== '' ? ' class="' . htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>

    <div vw class="enabled">
        <div vw-access-button class="active"></div>
        <div vw-plugin-wrapper>
            <div class="vw-plugin-top-wrapper"></div>
        </div>
    </div>
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script>new window.VLibras.Widget('https://vlibras.gov.br/app');</script>

    <header class="cabecalho-principal">
        <div class="conteudo-cabecalho">
            <div class="logo">
                <a href="index.php" class="logo-link" aria-label="Ir para a home do Pet Vida">
                    <img src="img/logo_petvida.png" alt="Logo Pet Vida">
                    <span class="logo-text">Pet <em>Vida</em></span>
                </a>
            </div>

            <div class="barra-busca">
                <input type="text" id="campo-busca" placeholder="Buscar animal por nome ou raca...">
                <button id="botao-busca" type="button"><i class="fas fa-search"></i></button>
            </div>

            <div class="acoes-cabecalho">
                <a href="adocao.php" class="botao-adote" id="btnFavoritos" onclick="return irParaAdocao(event)">
                    <i class="fas fa-heart"></i>
                    <span>Adocao</span>
                </a>
                <div class="acao-cabecalho">
                    <button class="botao-doacao" type="button"><i class="fas fa-hand-holding-heart"></i> Doe/ajude</button>
                </div>
                <div class="acao-cabecalho acao-cabecalho-usuario">
                    <div id="botaoUsuario">
                        <div class="usuario-perfil-bloco">
                            <i class="far fa-user" id="iconeUsuario"></i>
                            <span id="textoUsuario">Entrar/Cadastrar</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
