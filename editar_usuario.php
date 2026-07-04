<?php
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
$usuario = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim((string) ($_POST["nome"] ?? ""));
    $email = trim((string) ($_POST["email"] ?? ""));
    $telefone = trim((string) ($_POST["telefone"] ?? ""));
    $cpf = trim((string) ($_POST["cpf"] ?? ""));
    $idade = filter_input(INPUT_POST, "idade", FILTER_VALIDATE_INT);
    $dataNascimento = trim((string) ($_POST["data_nascimento"] ?? ""));
    $cidade = trim((string) ($_POST["cidade"] ?? ""));
    $estado = trim((string) ($_POST["estado"] ?? ""));
    $endereco = trim((string) ($_POST["endereco"] ?? ""));
    $perfil = trim((string) ($_POST["perfil"] ?? "user"));

    if ($nome === "" || $email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Informe nome e um e-mail válido para atualizar o usuário.";
    } elseif ($idade === false || $idade < 0) {
        $erro = "Informe uma idade válida para atualizar o usuário.";
    } elseif (!in_array($perfil, ["user", "admin"], true)) {
        $erro = "Perfil inválido para este cadastro.";
    } else {
        try {
            $stmtEmail = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email AND id_usuario <> :id_usuario");
            $stmtEmail->execute([
                ":email" => $email,
                ":id_usuario" => $idUsuario,
            ]);

            if ((int) $stmtEmail->fetchColumn() > 0) {
                $erro = "Já existe outro usuário cadastrado com este e-mail.";
            } else {
                $sqlUpdate = "
                    UPDATE usuarios
                    SET
                        nome = :nome,
                        email = :email,
                        telefone = :telefone,
                        cpf = :cpf,
                        idade = :idade,
                        data_nascimento = :data_nascimento,
                        cidade = :cidade,
                        estado = :estado,
                        endereco = :endereco,
                        perfil = :perfil
                    WHERE id_usuario = :id_usuario
                ";

                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $stmtUpdate->execute([
                    ":nome" => $nome,
                    ":email" => $email,
                    ":telefone" => $telefone !== "" ? $telefone : null,
                    ":cpf" => $cpf !== "" ? $cpf : null,
                    ":idade" => $idade,
                    ":data_nascimento" => $dataNascimento !== "" ? $dataNascimento : null,
                    ":cidade" => $cidade !== "" ? $cidade : null,
                    ":estado" => $estado !== "" ? $estado : null,
                    ":endereco" => $endereco !== "" ? $endereco : null,
                    ":perfil" => $perfil,
                    ":id_usuario" => $idUsuario,
                ]);

                if (isset($_SESSION["id_usuario"]) && (int) $_SESSION["id_usuario"] === (int) $idUsuario) {
                    $_SESSION["nome_usuario"] = $nome;
                    $_SESSION["email"] = $email;
                    $_SESSION["perfil"] = $perfil;
                }

                header("Location: usuarios.php?status=editado");
                exit;
            }
        } catch (PDOException $e) {
            $erro = "Não foi possível salvar as alterações do usuário.";
        }
    }
}

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
    <title>Editar Usuário | Pet Vida</title>
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
                <h1>Editar Usuário</h1>
                <p class="subtitulo">Atualize os dados cadastrais mantendo o perfil e as informações de contato consistentes.</p>
            </div>
            <a href="usuarios.php" class="btn-admin btn-editar btn-admin--voltar">Voltar para a listagem</a>
        </div>

        <?php if ($erro !== null): ?>
            <div class="alerta-admin alerta-admin--erro">
                <?php echo htmlspecialchars($erro, ENT_QUOTES, "UTF-8"); ?>
            </div>
        <?php endif; ?>

        <section class="painel-card painel-card--formulario">
            <div class="painel-card-topo">
                <div>
                    <h2><?php echo htmlspecialchars($usuario["nome"], ENT_QUOTES, "UTF-8"); ?></h2>
                    <p>Revise os campos abaixo antes de salvar.</p>
                </div>
                <span class="user-badge <?php echo ($usuario["perfil"] ?? "user") === "admin" ? "badge-admin" : "badge-user"; ?>">
                    <?php echo ($usuario["perfil"] ?? "user") === "admin" ? "Administrador" : "Usuário"; ?>
                </span>
            </div>

            <form action="editar_usuario.php" method="POST" class="modal-admin-form modal-admin-form--page">
                <input type="hidden" name="id_usuario" value="<?php echo (int) $usuario["id_usuario"]; ?>">

                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="nome">Nome</label>
                        <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars((string) $usuario["nome"], ENT_QUOTES, "UTF-8"); ?>">
                    </div>
                    <div class="form-grupo">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars((string) $usuario["email"], ENT_QUOTES, "UTF-8"); ?>">
                    </div>
                </div>

                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="telefone">Telefone</label>
                        <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars((string) ($usuario["telefone"] ?? ""), ENT_QUOTES, "UTF-8"); ?>">
                    </div>
                    <div class="form-grupo">
                        <label for="cpf">CPF</label>
                        <input type="text" id="cpf" name="cpf" value="<?php echo htmlspecialchars((string) ($usuario["cpf"] ?? ""), ENT_QUOTES, "UTF-8"); ?>">
                    </div>
                </div>

                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="idade">Idade</label>
                        <input type="number" id="idade" name="idade" min="0" required value="<?php echo htmlspecialchars((string) ($usuario["idade"] ?? ""), ENT_QUOTES, "UTF-8"); ?>">
                    </div>
                    <div class="form-grupo">
                        <label for="data_nascimento">Data de Nascimento</label>
                        <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars((string) ($usuario["data_nascimento"] ?? ""), ENT_QUOTES, "UTF-8"); ?>">
                    </div>
                </div>

                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="cidade">Cidade</label>
                        <input type="text" id="cidade" name="cidade" value="<?php echo htmlspecialchars((string) ($usuario["cidade"] ?? ""), ENT_QUOTES, "UTF-8"); ?>">
                    </div>
                    <div class="form-grupo">
                        <label for="estado">Estado</label>
                        <input type="text" id="estado" name="estado" maxlength="50" value="<?php echo htmlspecialchars((string) ($usuario["estado"] ?? ""), ENT_QUOTES, "UTF-8"); ?>">
                    </div>
                </div>

                <div class="form-linha-dupla">
                    <div class="form-grupo form-grupo--expandido">
                        <label for="endereco">Endereço</label>
                        <input type="text" id="endereco" name="endereco" value="<?php echo htmlspecialchars((string) ($usuario["endereco"] ?? ""), ENT_QUOTES, "UTF-8"); ?>">
                    </div>
                    <div class="form-grupo">
                        <label for="perfil">Perfil</label>
                        <select id="perfil" name="perfil" required>
                            <option value="user" <?php echo ($usuario["perfil"] ?? "user") === "user" ? "selected" : ""; ?>>Usuário</option>
                            <option value="admin" <?php echo ($usuario["perfil"] ?? "") === "admin" ? "selected" : ""; ?>>Administrador</option>
                        </select>
                    </div>
                </div>

                <div class="modal-admin-footer">
                    <a href="usuarios.php" class="btn-modal-cancelar btn-link-admin">Cancelar</a>
                    <button type="submit" class="btn-modal-salvar">Salvar Alterações</button>
                </div>
            </form>
        </section>
    </main>

</body>
</html>
