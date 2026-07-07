<?php
/*
|--------------------------------------------------------------------------
| Contexto da feature
|--------------------------------------------------------------------------
| Tela dedicada a consulta de usuarios no painel administrativo.
| Esta feature preserva o padrao visual do admin e apresenta
| os dados cadastrais apenas para leitura, sem permitir
| alteracoes administrativas nesta versao.
|--------------------------------------------------------------------------
*/
require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../config/sessao.php";

verificarAdmin();

$idUsuario = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idUsuario = filter_input(INPUT_POST, "id_usuario", FILTER_VALIDATE_INT);
}

if (!$idUsuario) {
    header("Location: usuarios.php?status=erro");
    exit;
}

$usuario = null;

try {
    $stmtUsuario = $pdo->prepare("
        SELECT id_usuario, nome, idade, email, telefone, cpf, data_nascimento, cidade, estado, endereco, perfil
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
} catch (PDOException $e) {
    header("Location: usuarios.php?status=erro");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Usuario | Pet Vida</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@400;600&family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

    <?php $adminActivePage = 'usuarios'; require __DIR__ . '/../includes/menu-admin.php'; ?>

    <main class="content">
        <div class="header-acoes-admin header-acoes-admin--stack">
            <div>
                <h1>Consultar Usuario</h1>
                <p class="subtitulo">Consulte os dados cadastrais no painel administrativo. As alteracoes permanecem desabilitadas nesta versao.</p>
            </div>
            <a href="usuarios.php" class="btn-admin btn-editar btn-admin--voltar">Voltar para a listagem</a>
        </div>

        <section class="painel-card painel-card--formulario">
            <div class="painel-card-topo">
                <div>
                    <h2><?php echo htmlspecialchars($usuario["nome"], ENT_QUOTES, "UTF-8"); ?></h2>
                    <p>Os dados abaixo sao exibidos apenas para consulta administrativa.</p>
                </div>
                <span class="user-badge <?php echo ($usuario["perfil"] ?? "user") === "admin" ? "badge-admin" : "badge-user"; ?>">
                    <?php echo ($usuario["perfil"] ?? "user") === "admin" ? "Administrador" : "Usuario"; ?>
                </span>
            </div>

            <div class="modal-admin-form modal-admin-form--page">
                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="nome">Nome</label>
                        <input type="text" id="nome" value="<?php echo htmlspecialchars((string) $usuario["nome"], ENT_QUOTES, "UTF-8"); ?>" readonly>
                    </div>
                    <div class="form-grupo">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars((string) $usuario["email"], ENT_QUOTES, "UTF-8"); ?>" readonly>
                    </div>
                </div>

                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="telefone">Telefone</label>
                        <input type="text" id="telefone" value="<?php echo htmlspecialchars((string) ($usuario["telefone"] ?? ""), ENT_QUOTES, "UTF-8"); ?>" readonly>
                    </div>
                    <div class="form-grupo">
                        <label for="cpf">CPF</label>
                        <input type="text" id="cpf" value="<?php echo htmlspecialchars((string) ($usuario["cpf"] ?? ""), ENT_QUOTES, "UTF-8"); ?>" readonly>
                    </div>
                </div>

                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="idade">Idade</label>
                        <input type="number" id="idade" value="<?php echo htmlspecialchars((string) ($usuario["idade"] ?? ""), ENT_QUOTES, "UTF-8"); ?>" readonly>
                    </div>
                    <div class="form-grupo">
                        <label for="data_nascimento">Data de Nascimento</label>
                        <input type="date" id="data_nascimento" value="<?php echo htmlspecialchars((string) ($usuario["data_nascimento"] ?? ""), ENT_QUOTES, "UTF-8"); ?>" readonly>
                    </div>
                </div>

                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="cidade">Cidade</label>
                        <input type="text" id="cidade" value="<?php echo htmlspecialchars((string) ($usuario["cidade"] ?? ""), ENT_QUOTES, "UTF-8"); ?>" readonly>
                    </div>
                    <div class="form-grupo">
                        <label for="estado">Estado</label>
                        <input type="text" id="estado" value="<?php echo htmlspecialchars((string) ($usuario["estado"] ?? ""), ENT_QUOTES, "UTF-8"); ?>" readonly>
                    </div>
                </div>

                <div class="form-linha-dupla">
                    <div class="form-grupo form-grupo--expandido">
                        <label for="endereco">Endereco</label>
                        <input type="text" id="endereco" value="<?php echo htmlspecialchars((string) ($usuario["endereco"] ?? ""), ENT_QUOTES, "UTF-8"); ?>" readonly>
                    </div>
                    <div class="form-grupo">
                        <label for="perfil">Perfil</label>
                        <input
                            type="text"
                            id="perfil"
                            value="<?php echo htmlspecialchars(($usuario["perfil"] ?? "user") === "admin" ? "Administrador" : "Usuario", ENT_QUOTES, "UTF-8"); ?>"
                            readonly
                        >
                    </div>
                </div>

                <div class="modal-admin-footer">
                    <a href="usuarios.php" class="btn-modal-cancelar btn-link-admin">Voltar</a>
                </div>
            </div>
        </section>
    </main>

</body>
</html>
