<?php
require_once "conexao.php";
session_start();
try {
    // Corrigido: id_usuario no lugar de id, e removido o data_cadastro que não existe
    $sql = "SELECT id_usuario, nome, email, telefone, perfil FROM usuarios ORDER BY id_usuario DESC";
    $stmt = $pdo->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $usuarios = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários | Pet Vida</title>
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
                <li><a href="animais.php">Animais</a></li>
                <li><a href="abrigos.php">Abrigos</a></li>
                <li class="active"><a href="usuarios.php">Usuários</a></li>
                <li><a href="index.php">Sair do Painel</a></li>
            </ul>
        </nav>
    </aside>

    <main class="content">
        <h1>Gerenciar Usuários</h1>
      

        <div class="tabela-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                        <th>Perfil</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--texto-leve); padding: 30px;">
                                Nenhum usuário encontrado no sistema.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $user): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($user['id_usuario']); ?></td>
                                <td><strong><?php echo htmlspecialchars($user['nome']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['telefone'] ?? 'Não informado'); ?></td>
                                <td>
                                    <span class="user-badge <?php echo ($user['perfil'] === 'admin') ? 'badge-admin' : 'badge-user'; ?>">
                                        <?php echo htmlspecialchars($user['perfil'] ?? 'Usuário'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="table-acoes">
                                        <a href="editar_usuario.php?id=<?php echo $user['id_usuario']; ?>" class="btn-table btn-table-editar">Editar</a>
                                        <button class="btn-table btn-table-excluir" onclick="if(confirm('Tem certeza que deseja excluir este usuário?')) window.location.href='excluir_usuario.php?id=<?php echo $user['id_usuario']; ?>'">Excluir</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?> </tbody>
            </table>
        </div>
    </main>

</body>
</html>