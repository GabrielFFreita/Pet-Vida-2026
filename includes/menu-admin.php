<?php
require_once __DIR__ . "/helpers.php";

$adminActivePage = $adminActivePage ?? "";

$adminMenuItems = [
    "dashboard" => ["label" => "Visão Geral", "href" => "dashboard.php"],
    "animais" => ["label" => "Animais", "href" => "animais.php"],
    "abrigos" => ["label" => "Abrigos", "href" => "abrigos.php"],
    "usuarios" => ["label" => "Usuários", "href" => "usuarios.php"],
];
?>
<div vw class="enabled">
    <div vw-access-button class="active"></div>
    <div vw-plugin-wrapper>
        <div class="vw-plugin-top-wrapper"></div>
    </div>
</div>
<script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
<script>new window.VLibras.Widget('https://vlibras.gov.br/app');</script>
<aside class="sidebar">
    <h2>Pet Vida Admin</h2>
    <nav>
        <ul>
            <?php foreach ($adminMenuItems as $menuKey => $menuItem): ?>
                <li class="<?php echo $adminActivePage === $menuKey ? "active" : ""; ?>">
                    <a href="<?php echo htmlspecialchars($menuItem["href"], ENT_QUOTES, "UTF-8"); ?>">
                        <?php echo htmlspecialchars($menuItem["label"], ENT_QUOTES, "UTF-8"); ?>
                    </a>
                </li>
            <?php endforeach; ?>
            <li><a href="<?php echo htmlspecialchars(rootPath("index.php"), ENT_QUOTES, "UTF-8"); ?>">Sair do Painel</a></li>
        </ul>
    </nav>
</aside>
