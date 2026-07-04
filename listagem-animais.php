<?php
require_once "conexao.php";
require_once "config_sessao.php";
require_once "animal_admin_helpers.php";

verificarAdmin();

$animais = [];
$metricas = [
    "total" => 0,
    "disponiveis" => 0,
    "em_processo" => 0,
    "com_deficiencia" => 0,
];
$mensagem = null;
$tipoMensagem = "sucesso";

if (isset($_GET["status"])) {
    $status = (string) $_GET["status"];

    if ($status === "editado") {
        $mensagem = "Animal atualizado com sucesso.";
    } elseif ($status === "excluido") {
        $mensagem = "Animal removido com sucesso.";
    } elseif ($status === "erro") {
        $mensagem = "Não foi possível concluir a operação solicitada.";
        $tipoMensagem = "erro";
    }
}

try {
    $animais = $pdo->query("
        SELECT
            a.id_animal,
            a.nome,
            a.especie,
            a.raca,
            a.castrado,
            a.vacinado,
            a.deficiencia,
            a.status_adocao,
            a.id_abrigo,
            ab.nome AS nome_abrigo,
            fp.ds_img AS foto_capa
        FROM animais_adocao a
        LEFT JOIN abrigos ab ON ab.id = a.id_abrigo
        LEFT JOIN (
            SELECT id_animal, MIN(ds_img) AS ds_img
            FROM foto_animal
            GROUP BY id_animal
        ) fp ON fp.id_animal = a.id_animal
        ORDER BY a.id_animal DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $metricas["total"] = (int) $pdo->query("SELECT COUNT(*) FROM animais_adocao")->fetchColumn();
    $metricas["disponiveis"] = (int) $pdo->query("SELECT COUNT(*) FROM animais_adocao WHERE status_adocao = 'Disponível'")->fetchColumn();
    $metricas["em_processo"] = (int) $pdo->query("SELECT COUNT(*) FROM animais_adocao WHERE status_adocao = 'Em processo'")->fetchColumn();
    $metricas["com_deficiencia"] = (int) $pdo->query("SELECT COUNT(*) FROM animais_adocao WHERE deficiencia IS NOT NULL AND TRIM(deficiencia) <> ''")->fetchColumn();
} catch (PDOException $e) {
    $mensagem = "Não foi possível carregar a listagem de animais.";
    $tipoMensagem = "erro";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Animais | Pet Vida</title>
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
                <li><a href="adimpage.php">Visão Geral</a></li>
                <li class="active"><a href="listagem-animais.php">Animais</a></li>
                <li><a href="abrigos.php">Abrigos</a></li>
                <li><a href="usuarios.php">Usuários</a></li>
                <li><a href="index.php">Sair do Painel</a></li>
            </ul>
        </nav>
    </aside>

    <main class="content">
        <div class="header-acoes-admin header-acoes-admin--stack">
            <div>
                <h1>Gerenciar Animais</h1>
                <p class="subtitulo">Consulte os cadastros, atualize dados do perfil do animal e remova registros sem vínculos ativos.</p>
            </div>
        </div>

        <?php if ($mensagem !== null): ?>
            <div class="alerta-admin <?php echo $tipoMensagem === "erro" ? "alerta-admin--erro" : "alerta-admin--sucesso"; ?>">
                <?php echo htmlspecialchars($mensagem, ENT_QUOTES, "UTF-8"); ?>
            </div>
        <?php endif; ?>

        <section class="dashboard-grid">
            <article class="card-metrica">
                <h3>Total de Animais</h3>
                <div class="valor-metrica"><?php echo $metricas["total"]; ?></div>
                <span class="legenda-metrica">Cadastros disponíveis para administração</span>
            </article>
            <article class="card-metrica">
                <h3>Disponíveis</h3>
                <div class="valor-metrica"><?php echo $metricas["disponiveis"]; ?></div>
                <span class="legenda-metrica">Prontos para novas solicitações</span>
            </article>
            <article class="card-metrica">
                <h3>Em Processo</h3>
                <div class="valor-metrica"><?php echo $metricas["em_processo"]; ?></div>
                <span class="legenda-metrica">Com adoção em andamento</span>
            </article>
            <article class="card-metrica">
                <h3>Com Deficiência</h3>
                <div class="valor-metrica"><?php echo $metricas["com_deficiencia"]; ?></div>
                <span class="legenda-metrica">Animais que exigem atenção adicional</span>
            </article>
        </section>

        <section class="painel-card painel-card--usuarios">
            <div class="painel-card-topo">
                <div>
                    <h2>Base de animais</h2>
                    <p>Layout focado em leitura rápida, com ações diretas de edição, exclusão e identificação visual por foto.</p>
                </div>
                <div class="painel-resumo-pill">
                    <?php echo count($animais); ?> registro(s)
                </div>
            </div>

            <div class="tabela-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Animal</th>
                            <th>Tipo</th>
                            <th>Raça</th>
                            <th>Castração</th>
                            <th>Vacinação</th>
                            <th>Deficiência</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($animais)): ?>
                            <tr>
                                <td colspan="7" class="estado-vazio-tabela">Nenhum animal encontrado no sistema.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($animais as $animal): ?>
                                <tr>
                                    <td>
                                        <div class="animal-listagem-celula">
                                            <img
                                                src="<?php echo htmlspecialchars(animalAdminFotoPublica($animal["foto_capa"] ?? null), ENT_QUOTES, "UTF-8"); ?>"
                                                alt="Foto de <?php echo htmlspecialchars($animal["nome"], ENT_QUOTES, "UTF-8"); ?>"
                                                class="animal-listagem-thumb"
                                            >
                                            <div class="animal-listagem-texto">
                                                <strong><?php echo htmlspecialchars($animal["nome"], ENT_QUOTES, "UTF-8"); ?></strong>
                                                <span><?php echo htmlspecialchars($animal["nome_abrigo"] ?? "Abrigo não informado", ENT_QUOTES, "UTF-8"); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($animal["especie"] ?: "Não informado", ENT_QUOTES, "UTF-8"); ?></td>
                                    <td><?php echo htmlspecialchars($animal["raca"] ?: "Não informada", ENT_QUOTES, "UTF-8"); ?></td>
                                    <td>
                                        <span class="status-inline-badge <?php echo animalAdminClasseBooleana($animal["castrado"]); ?>">
                                            <?php echo htmlspecialchars(animalAdminDescricaoBooleana($animal["castrado"]), ENT_QUOTES, "UTF-8"); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-inline-badge <?php echo animalAdminClasseBooleana($animal["vacinado"]); ?>">
                                            <?php echo htmlspecialchars(animalAdminDescricaoBooleana($animal["vacinado"]), ENT_QUOTES, "UTF-8"); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars(trim((string) ($animal["deficiencia"] ?? "")) !== "" ? $animal["deficiencia"] : "Não possui", ENT_QUOTES, "UTF-8"); ?></td>
                                    <td>
                                        <div class="table-acoes">
                                            <a href="editar_animal.php?id=<?php echo (int) $animal["id_animal"]; ?>" class="btn-table btn-table-editar">Editar</a>
                                            <a href="excluir_animal.php?id=<?php echo (int) $animal["id_animal"]; ?>" class="btn-table btn-table-excluir">Excluir</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

</body>
</html>
