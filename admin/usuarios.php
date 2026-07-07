<?php
/*
|--------------------------------------------------------------------------
| Contexto da feature
|--------------------------------------------------------------------------
| Tela principal de gerenciamento de usuarios do painel administrativo.
| Esta feature reorganiza a listagem, destaca metricas resumidas e
| centraliza os acessos para consulta e exclusao sem permitir
| inclusao ou edicao cadastral nesta pagina.
|--------------------------------------------------------------------------
*/
require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../config/sessao.php";

verificarAdmin();

function fetchTotalUsuarios(PDO $pdo, string $sql, array $params = []): int
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return (int) $stmt->fetchColumn();
}

$usuarios = [];
$metricas = [
    "total" => 0,
    "admins" => 0,
    "comuns" => 0,
    "com_adocao" => 0,
];
$mensagem = null;
$tipoMensagem = "sucesso";

if (isset($_GET["status"])) {
    $status = $_GET["status"];

    if ($status === "excluido") {
        $mensagem = "Usuario removido com sucesso.";
    } elseif ($status === "erro") {
        $mensagem = "Nao foi possivel concluir a operacao informada.";
        $tipoMensagem = "erro";
    }
}

try {
    $sql = "
        SELECT
            u.id_usuario,
            u.nome,
            u.email,
            u.telefone,
            u.cidade,
            u.estado,
            u.perfil,
            COUNT(a.id_adocao) AS total_adocoes
        FROM usuarios u
        LEFT JOIN adocao a ON a.id_usuario = u.id_usuario
        GROUP BY u.id_usuario, u.nome, u.email, u.telefone, u.cidade, u.estado, u.perfil
        ORDER BY u.id_usuario DESC
    ";
    $stmt = $pdo->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $metricas["total"] = fetchTotalUsuarios($pdo, "SELECT COUNT(*) FROM usuarios");
    $metricas["admins"] = fetchTotalUsuarios($pdo, "SELECT COUNT(*) FROM usuarios WHERE perfil = 'admin'");
    $metricas["comuns"] = fetchTotalUsuarios($pdo, "SELECT COUNT(*) FROM usuarios WHERE perfil = 'user' OR perfil IS NULL");
    $metricas["com_adocao"] = fetchTotalUsuarios($pdo, "SELECT COUNT(DISTINCT id_usuario) FROM adocao");
} catch (PDOException $e) {
    $usuarios = [];
    $mensagem = "Nao foi possivel carregar a listagem de usuarios.";
    $tipoMensagem = "erro";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuarios | Pet Vida</title>
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
                <h1>Gerenciar Usuarios</h1>
                <p class="subtitulo">Acompanhe a base cadastrada, consulte os dados e remova usuarios sem dependencias ativas.</p>
            </div>
        </div>

        <?php if ($mensagem !== null): ?>
            <div class="alerta-admin <?php echo $tipoMensagem === "erro" ? "alerta-admin--erro" : "alerta-admin--sucesso"; ?>">
                <?php echo htmlspecialchars($mensagem, ENT_QUOTES, "UTF-8"); ?>
            </div>
        <?php endif; ?>

        <section class="dashboard-grid">
            <article class="card-metrica">
                <h3>Total de Usuarios</h3>
                <div class="valor-metrica"><?php echo $metricas["total"]; ?></div>
                <span class="legenda-metrica">Registros disponiveis para administracao</span>
            </article>
            <article class="card-metrica">
                <h3>Administradores</h3>
                <div class="valor-metrica"><?php echo $metricas["admins"]; ?></div>
                <span class="legenda-metrica">Perfis com acesso ao painel</span>
            </article>
            <article class="card-metrica">
                <h3>Usuarios Comuns</h3>
                <div class="valor-metrica"><?php echo $metricas["comuns"]; ?></div>
                <span class="legenda-metrica">Perfis de uso padrao da plataforma</span>
            </article>
            <article class="card-metrica">
                <h3>Com Solicitacoes</h3>
                <div class="valor-metrica"><?php echo $metricas["com_adocao"]; ?></div>
                <span class="legenda-metrica">Usuarios vinculados a adocoes</span>
            </article>
        </section>

        <section class="painel-card painel-card--usuarios">
            <div class="painel-card-topo">
                <div>
                    <h2>Base de usuarios</h2>
                    <p>Listagem centralizada para consulta rapida e exclusao segura.</p>
                </div>
                <div class="painel-resumo-pill">
                    <?php echo count($usuarios); ?> registro(s)
                </div>
            </div>

            <div class="tabela-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Contato</th>
                            <th>Localidade</th>
                            <th>Perfil</th>
                            <th>Adocoes</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="6" class="estado-vazio-tabela">
                                    Nenhum usuario encontrado no sistema.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $user): ?>
                                <tr>
                                    <td>
                                        <div class="usuario-celula">
                                            <strong><?php echo htmlspecialchars($user["nome"], ENT_QUOTES, "UTF-8"); ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="usuario-celula">
                                            <strong><?php echo htmlspecialchars($user["email"], ENT_QUOTES, "UTF-8"); ?></strong>
                                            <span><?php echo htmlspecialchars($user["telefone"] ?: "Nao informado", ENT_QUOTES, "UTF-8"); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $cidade = trim((string) ($user["cidade"] ?? ""));
                                        $estado = trim((string) ($user["estado"] ?? ""));
                                        $localidade = trim($cidade . ($cidade !== "" && $estado !== "" ? " - " : "") . $estado);
                                        echo htmlspecialchars($localidade !== "" ? $localidade : "Nao informada", ENT_QUOTES, "UTF-8");
                                        ?>
                                    </td>
                                    <td>
                                        <?php $perfil = strtolower((string) ($user["perfil"] ?? "user")); ?>
                                        <span class="user-badge <?php echo $perfil === "admin" ? "badge-admin" : "badge-user"; ?>">
                                            <?php echo htmlspecialchars($perfil === "admin" ? "Administrador" : "Usuario", ENT_QUOTES, "UTF-8"); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-relacao"><?php echo (int) $user["total_adocoes"]; ?> registro(s)</span>
                                    </td>
                                    <td>
                                        <div class="table-acoes">
                                            <a href="usuario-editar.php?id=<?php echo (int) $user["id_usuario"]; ?>" class="btn-table btn-table-editar">Visualizar</a>
                                            <?php if ((int) ($_SESSION["id_usuario"] ?? 0) !== (int) $user["id_usuario"]): ?>
                                                <a href="usuario-excluir.php?id=<?php echo (int) $user["id_usuario"]; ?>" class="btn-table btn-table-excluir">Excluir</a>
                                            <?php endif; ?>
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
