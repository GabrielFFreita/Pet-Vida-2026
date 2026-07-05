<?php
require_once __DIR__ . '/helpers.php';

$pageTitle = $pageTitle ?? 'Pet Vida - Adote um Amigo';
$bodyClass = $bodyClass ?? '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuarioHeaderLogado = isset($_SESSION['id_usuario']);
$usuarioHeaderNome = trim((string) ($_SESSION['nome_usuario'] ?? ''));
$usuarioHeaderEmail = trim((string) ($_SESSION['email'] ?? ''));
$usuarioHeaderPerfil = trim((string) ($_SESSION['perfil'] ?? 'user'));
$usuarioHeaderPrimeiroNome = $usuarioHeaderNome !== '' ? explode(' ', preg_replace('/\s+/', ' ', $usuarioHeaderNome))[0] : 'Conta';
$usuarioHeaderIniciais = 'PV';

if ($usuarioHeaderNome !== '') {
    $partesNomeHeader = preg_split('/\s+/', $usuarioHeaderNome);
    $iniciaisHeader = '';

    foreach (array_slice($partesNomeHeader, 0, 2) as $parteNomeHeader) {
        $iniciaisHeader .= function_exists('mb_substr')
            ? mb_strtoupper(mb_substr($parteNomeHeader, 0, 1, 'UTF-8'), 'UTF-8')
            : strtoupper(substr($parteNomeHeader, 0, 1));
    }

    if ($iniciaisHeader !== '') {
        $usuarioHeaderIniciais = $iniciaisHeader;
    }
}
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
    <link rel="stylesheet" href="<?php echo htmlspecialchars(assetPath('css/site.css?v=11'), ENT_QUOTES, 'UTF-8'); ?>">
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
                <a href="<?php echo htmlspecialchars(rootPath('index.php'), ENT_QUOTES, 'UTF-8'); ?>" class="logo-link" aria-label="Ir para a home do Pet Vida">
                    <img src="<?php echo htmlspecialchars(assetPath('img/logo/logo_petvida.png'), ENT_QUOTES, 'UTF-8'); ?>" alt="Logo Pet Vida">
                    <span class="logo-text">Pet <em>Vida</em></span>
                </a>
            </div>

            <div class="barra-busca">
                <input type="text" id="campo-busca" placeholder="Buscar animal por nome ou raca...">
                <button id="botao-busca" type="button"><i class="fas fa-search"></i></button>
            </div>

            <div class="acoes-cabecalho">
                <a href="<?php echo htmlspecialchars(rootPath('adocao.php'), ENT_QUOTES, 'UTF-8'); ?>" class="botao-adote" id="btnFavoritos" onclick="return irParaAdocao(event)">
                    <i class="fas fa-heart"></i>
                    <span>Adocao</span>
                </a>
                <div class="acao-cabecalho">
                    <button class="botao-doacao" type="button"><i class="fas fa-hand-holding-heart"></i> Doe/ajude</button>
                </div>
                <div class="acao-cabecalho acao-cabecalho-usuario">
                    <div id="botaoUsuario" class="usuario-menu-wrapper">
                        <?php if ($usuarioHeaderLogado): ?>
                            <button
                                id="usuarioMenuTrigger"
                                class="usuario-trigger"
                                type="button"
                                aria-haspopup="true"
                                aria-expanded="false"
                                aria-label="Abrir menu da conta de <?php echo htmlspecialchars($usuarioHeaderNome !== '' ? $usuarioHeaderNome : 'Usuario Pet Vida', ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <span class="usuario-avatar" aria-hidden="true"><?php echo htmlspecialchars($usuarioHeaderIniciais, ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="usuario-trigger-textos">
                                    <span class="usuario-trigger-nome"><?php echo htmlspecialchars($usuarioHeaderPrimeiroNome, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="usuario-status">
                                        <span class="usuario-status-dot" aria-hidden="true"></span>
                                        <span>Logado</span>
                                    </span>
                                </span>
                                <i class="fas fa-chevron-down usuario-trigger-seta" aria-hidden="true"></i>
                            </button>
                        <?php else: ?>
                            <div class="usuario-acoes-visitante">
                                <button
                                    id="usuarioLoginTrigger"
                                    class="usuario-trigger usuario-trigger--visitante"
                                    type="button"
                                    aria-label="Acessar sua conta"
                                >
                                    <span class="usuario-avatar usuario-avatar--visitante" aria-hidden="true">
                                        <i class="far fa-user"></i>
                                    </span>
                                    <span class="usuario-trigger-textos">
                                        <span class="usuario-trigger-nome">Entrar</span>
                                        <span class="usuario-trigger-subtexto">Acesse sua conta</span>
                                    </span>
                                </button>
                                <button
                                    id="usuarioCadastroTrigger"
                                    class="usuario-trigger usuario-trigger--cadastro"
                                    type="button"
                                    aria-label="Ir para cadastro"
                                >
                                    Cadastre-se
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div
                        id="menuUsuarioDropdown"
                        class="usuario-dropdown"
                        aria-hidden="true"
                        role="menu"
                        aria-label="Menu da conta"
                    >
                    </div>
                </div>
            </div>
        </div>
    </header>
