<?php
require_once "conexao.php";
require_once "config_sessao.php";
require_once "animal_admin_helpers.php";

verificarAdmin();

$idAnimal = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idAnimal = filter_input(INPUT_POST, "id_animal", FILTER_VALIDATE_INT);
}

if (!$idAnimal) {
    header("Location: listagem-animais.php?status=erro");
    exit;
}

$erro = null;

try {
    $stmtAnimal = $pdo->prepare("
        SELECT
            a.id_animal,
            a.nome,
            a.especie,
            a.raca,
            a.status_adocao,
            ab.nome AS nome_abrigo
        FROM animais_adocao a
        LEFT JOIN abrigos ab ON ab.id = a.id_abrigo
        WHERE a.id_animal = :id_animal
        LIMIT 1
    ");
    $stmtAnimal->execute([":id_animal" => $idAnimal]);
    $animal = $stmtAnimal->fetch(PDO::FETCH_ASSOC);

    if (!$animal) {
        header("Location: listagem-animais.php?status=erro");
        exit;
    }

    $stmtAdocoes = $pdo->prepare("SELECT COUNT(*) FROM adocao WHERE id_animal = :id_animal");
    $stmtAdocoes->execute([":id_animal" => $idAnimal]);
    $totalAdocoes = (int) $stmtAdocoes->fetchColumn();

    $fotos = animalAdminFetchFotos($pdo, $idAnimal);
} catch (PDOException $e) {
    header("Location: listagem-animais.php?status=erro");
    exit;
}

$bloqueado = $totalAdocoes > 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($bloqueado) {
        $erro = "Este animal não pode ser excluído porque possui solicitações de adoção vinculadas.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmtDeleteFotos = $pdo->prepare("DELETE FROM foto_animal WHERE id_animal = :id_animal");
            $stmtDeleteFotos->execute([":id_animal" => $idAnimal]);

            $stmtDeleteAnimal = $pdo->prepare("DELETE FROM animais_adocao WHERE id_animal = :id_animal");
            $stmtDeleteAnimal->execute([":id_animal" => $idAnimal]);

            $pdo->commit();

            foreach ($fotos as $foto) {
                animalAdminRemoverArquivoFoto((string) $foto["ds_img"]);
            }

            header("Location: listagem-animais.php?status=excluido");
            exit;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $erro = "Não foi possível excluir o animal selecionado.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Animal | Pet Vida</title>
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
                <h1>Excluir Animal</h1>
                <p class="subtitulo">Confirme a remoção apenas quando o cadastro não tiver vínculo ativo com solicitações de adoção.</p>
            </div>
            <a href="listagem-animais.php" class="btn-admin btn-editar btn-admin--voltar">Voltar para a listagem</a>
        </div>

        <?php if ($erro !== null): ?>
            <div class="alerta-admin alerta-admin--erro">
                <?php echo htmlspecialchars($erro, ENT_QUOTES, "UTF-8"); ?>
            </div>
        <?php endif; ?>

        <section class="painel-card painel-card--danger">
            <div class="painel-card-topo">
                <div>
                    <h2><?php echo htmlspecialchars($animal["nome"], ENT_QUOTES, "UTF-8"); ?></h2>
                    <p>Revise o resumo abaixo antes de confirmar a exclusão definitiva do cadastro.</p>
                </div>
                <span class="header-tag-admin"><?php echo htmlspecialchars($animal["status_adocao"], ENT_QUOTES, "UTF-8"); ?></span>
            </div>

            <div class="insights-lista insights-lista--usuarios">
                <div class="insight-item">
                    <strong>Tipo</strong>
                    <span><?php echo htmlspecialchars($animal["especie"] ?: "Não informado", ENT_QUOTES, "UTF-8"); ?></span>
                </div>
                <div class="insight-item">
                    <strong>Raça</strong>
                    <span><?php echo htmlspecialchars($animal["raca"] ?: "Não informada", ENT_QUOTES, "UTF-8"); ?></span>
                </div>
                <div class="insight-item">
                    <strong>Abrigo</strong>
                    <span><?php echo htmlspecialchars($animal["nome_abrigo"] ?: "Não informado", ENT_QUOTES, "UTF-8"); ?></span>
                </div>
                <div class="insight-item">
                    <strong>Fotos cadastradas</strong>
                    <span><?php echo count($fotos); ?></span>
                </div>
                <div class="insight-item">
                    <strong>Solicitações de adoção</strong>
                    <span><?php echo $totalAdocoes; ?></span>
                </div>
            </div>

            <div class="bloco-aviso-exclusao <?php echo $bloqueado ? "bloco-aviso-exclusao--erro" : "bloco-aviso-exclusao--ok"; ?>">
                <?php if ($bloqueado): ?>
                    A exclusão foi bloqueada porque existem solicitações de adoção vinculadas a este animal.
                <?php else: ?>
                    Nenhum vínculo impeditivo foi encontrado. A exclusão removerá o animal e os registros de fotos associados.
                <?php endif; ?>
            </div>

            <form action="excluir_animal.php" method="POST" class="form-exclusao-admin">
                <input type="hidden" name="id_animal" value="<?php echo (int) $animal["id_animal"]; ?>">
                <a href="listagem-animais.php" class="btn-modal-cancelar btn-link-admin">Cancelar</a>
                <button type="submit" class="btn-table btn-table-excluir" <?php echo $bloqueado ? "disabled" : ""; ?>>
                    Confirmar Exclusão
                </button>
            </form>
        </section>
    </main>

</body>
</html>
