<?php
/*
|--------------------------------------------------------------------------
| Contexto da feature
|--------------------------------------------------------------------------
| Tela dedicada a confirmacao e exclusao de usuarios no painel
| administrativo. Esta feature valida vinculos com adocao e doacao,
| bloqueia autoexclusao e concentra a remocao em um fluxo separado
| da listagem principal.
|--------------------------------------------------------------------------
*/
require_once "conexao.php";
require_once "config_sessao.php";

verificarAdmin();

$idUsuario = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idUsuario = filter_input(INPUT_POST, "id_usuario", FILTER_VALIDATE_INT);
}

if (!$idUsuario) {
    header("Location: usuarios.php?status=erro");
    exit;
}

$erro = null;

try {
    $stmtUsuario = $pdo->prepare("
        SELECT id_usuario, nome, email, perfil
        FROM usuarios
        WHERE id_usuario = :id_usuario
        LIMIT 1
    ");
    $stmtUsuario->execute([":id_usuario" => $idUsuario]);
    $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header("Location: usuarios.php?status=erro");
        exit;
    }

    $stmtAdocao = $pdo->prepare("SELECT COUNT(*) FROM adocao WHERE id_usuario = :id_usuario");
    $stmtAdocao->execute([":id_usuario" => $idUsuario]);
    $totalAdocoes = (int) $stmtAdocao->fetchColumn();

    $stmtDoacao = $pdo->prepare("SELECT COUNT(*) FROM doacao WHERE id_usuario = :id_usuario");
    $stmtDoacao->execute([":id_usuario" => $idUsuario]);
    $totalDoacoes = (int) $stmtDoacao->fetchColumn();
} catch (PDOException $e) {
    header("Location: usuarios.php?status=erro");
    exit;
}

$bloqueado = $totalAdocoes > 0 || $totalDoacoes > 0 || ((int) ($_SESSION["id_usuario"] ?? 0) === (int) $idUsuario);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($bloqueado) {
        $erro = "Este usuário não pode ser excluído porque possui vínculos ativos ou corresponde ao usuário logado.";
    } else {
        try {
            $stmtDelete = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = :id_usuario");
            $stmtDelete->execute([":id_usuario" => $idUsuario]);

            header("Location: usuarios.php?status=excluido");
            exit;
        } catch (PDOException $e) {
            $erro = "Não foi possível excluir o usuário selecionado.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Usuário | Pet Vida</title>
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
                <li><a href="listagem-animais.php">Animais</a></li>
                <li><a href="abrigos.php">Abrigos</a></li>
                <li class="active"><a href="usuarios.php">Usuários</a></li>
                <li><a href="index.php">Sair do Painel</a></li>
            </ul>
        </nav>
    </aside>

    <main class="content">
        <div class="header-acoes-admin header-acoes-admin--stack">
            <div>
                <h1>Excluir Usuário</h1>
                <p class="subtitulo">Confirme a remoção apenas quando não houver histórico relacionado ao cadastro.</p>
            </div>
            <a href="usuarios.php" class="btn-admin btn-editar btn-admin--voltar">Voltar para a listagem</a>
        </div>

        <?php if ($erro !== null): ?>
            <div class="alerta-admin alerta-admin--erro">
                <?php echo htmlspecialchars($erro, ENT_QUOTES, "UTF-8"); ?>
            </div>
        <?php endif; ?>

        <section class="painel-card painel-card--danger">
            <div class="painel-card-topo">
                <div>
                    <h2><?php echo htmlspecialchars($usuario["nome"], ENT_QUOTES, "UTF-8"); ?></h2>
                    <p>Revise os vínculos antes de concluir a exclusão do cadastro.</p>
                </div>
                <span class="user-badge <?php echo ($usuario["perfil"] ?? "user") === "admin" ? "badge-admin" : "badge-user"; ?>">
                    <?php echo ($usuario["perfil"] ?? "user") === "admin" ? "Administrador" : "Usuário"; ?>
                </span>
            </div>

            <div class="insights-lista insights-lista--usuarios">
                <div class="insight-item">
                    <strong>E-mail</strong>
                    <span><?php echo htmlspecialchars($usuario["email"], ENT_QUOTES, "UTF-8"); ?></span>
                </div>
                <div class="insight-item">
                    <strong>Solicitações de adoção</strong>
                    <span><?php echo $totalAdocoes; ?></span>
                </div>
                <div class="insight-item">
                    <strong>Doações registradas</strong>
                    <span><?php echo $totalDoacoes; ?></span>
                </div>
            </div>

            <div class="bloco-aviso-exclusao <?php echo $bloqueado ? "bloco-aviso-exclusao--erro" : "bloco-aviso-exclusao--ok"; ?>">
                <?php if ((int) ($_SESSION["id_usuario"] ?? 0) === (int) $idUsuario): ?>
                    Você não pode excluir o próprio usuário enquanto estiver autenticado no painel.
                <?php elseif ($totalAdocoes > 0 || $totalDoacoes > 0): ?>
                    A exclusão foi bloqueada porque existem registros relacionados em adoção ou doação.
                <?php else: ?>
                    Nenhum vínculo impeditivo foi encontrado. A exclusão removerá o cadastro definitivamente.
                <?php endif; ?>
            </div>

            <form action="excluir_usuario.php" method="POST" class="form-exclusao-admin">
                <input type="hidden" name="id_usuario" value="<?php echo (int) $usuario["id_usuario"]; ?>">
                <a href="usuarios.php" class="btn-modal-cancelar btn-link-admin">Cancelar</a>
                <button type="submit" class="btn-table btn-table-excluir" <?php echo $bloqueado ? "disabled" : ""; ?>>
                    Confirmar Exclusão
                </button>
            </form>
        </section>
    </main>

</body>
</html>
