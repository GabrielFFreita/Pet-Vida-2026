<?php
require_once "conexao.php";
require_once "config_sessao.php";
verificarAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_UNSAFE_RAW);
    $cnpj = filter_input(INPUT_POST, 'cnpj', FILTER_UNSAFE_RAW);
    $localizacao = filter_input(INPUT_POST, 'localizacao', FILTER_UNSAFE_RAW);
    $cep = filter_input(INPUT_POST, 'cep', FILTER_UNSAFE_RAW);
    $descricao = filter_input(INPUT_POST, 'descricao', FILTER_UNSAFE_RAW);

    if ($nome && $cnpj && $localizacao && $cep) {
        try {
            $sql_insert = "INSERT INTO abrigos (nome, cnpj, localizacao, cep, descricao) VALUES (:nome, :cnpj, :localizacao, :cep, :descricao)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([
                ':nome' => $nome,
                ':cnpj' => $cnpj,
                ':localizacao' => $localizacao,
                ':cep' => $cep,
                ':descricao' => $descricao
            ]);
            header("Location: abrigos.php");
            exit;
        } catch (PDOException $e) {
            $erro = "Erro ao cadastrar abrigo: " . $e->getMessage();
        }
    }
}

try {
    $sql = "SELECT id, nome, localizacao, cep, descricao FROM abrigos ORDER BY id DESC";
    $stmt = $pdo->query($sql);
    $abrigos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $abrigos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Abrigos | Pet Vida</title>
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
                <li class="active"><a href="abrigos.php">Abrigos</a></li>
                <li><a href="usuarios.php">Usuários</a></li>
                <li><a href="index.php">Sair do Painel</a></li>
            </ul>
        </nav>
    </aside>

    <main class="content">
        <div class="header-acoes-admin">
            <div>
                <h1>Gerenciar Abrigos</h1>
                <p class="subtitulo">Visualize, edite ou remova abrigos cadastrados na plataforma.</p>
            </div>
            <button class="btn-adicionar-novo" onclick="abrirModal()">
                <span>+</span> Novo Abrigo
            </button>
        </div>

        <?php if (isset($erro)): ?>
            <div style="background-color: #FEF2F2; color: #DC2626; padding: 15px; border-radius: var(--raio); margin-bottom: 20px; border: 1px solid #FEE2E2; width: 100%;">
                <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <section class="grid-cards">
            <?php if (empty($abrigos)): ?>
                <div style="grid-column: 1 / -1; text-align: center; color: var(--texto-leve); padding: 40px;">
                    <p>Nenhum abrigo cadastrado no momento.</p>
                </div>
            <?php else: ?>
                <?php foreach ($abrigos as $abrigo): ?>
                    <article class="admin-card">
                        <div class="card-header-img">
                            <span class="placeholder-icon">🏢</span>
                            <span class="card-badge"><?php echo htmlspecialchars($abrigo['cep']); ?></span>
                        </div>
                        <div class="card-corpo">
                            <h3 class="card-titulo"><?php echo htmlspecialchars($abrigo['nome']); ?></h3>
                            <p class="card-detalhe">📍 <?php echo htmlspecialchars($abrigo['localizacao']); ?></p>
                            <p class="card-detalhe" style="margin-top: 8px; font-size: 13px; line-height: 1.4;">
                                <?php 
                                    $texto = htmlspecialchars($abrigo['descricao'] ?? '');
                                    echo (strlen($texto) > 90) ? substr($texto, 0, 85) . '...' : $texto; 
                                ?>
                            </p>
                            
                            <div class="card-acoes-btn">
                                <a href="perfil_abrigo.php?id=<?php echo $abrigo['id']; ?>" class="btn-admin btn-editar">Visualizar</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <div id="modalCadastro" class="modal-admin-overlay" onclick="fecharModalExterno(event)">
        <div class="modal-admin-content">
            <div class="modal-admin-header">
                <h2>Cadastrar Novo Abrigo</h2>
                <button class="btn-modal-fechar" onclick="fecharModal()">&times;</button>
            </div>
            <form action="abrigos.php" method="POST" class="modal-admin-form">
                <div class="form-grupo">
                    <label for="nome">Nome do Abrigo</label>
                    <input type="text" id="nome" name="nome" required placeholder="Ex: Abrigo Patinhas Felizes">
                </div>
                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="cnpj">CNPJ</label>
                        <input type="text" id="cnpj" name="cnpj" required placeholder="00.000.000/0001-00">
                    </div>
                    <div class="form-grupo">
                        <label for="cep">CEP</label>
                        <input type="text" id="cep" name="cep" required placeholder="00000-000">
                    </div>
                </div>
                <div class="form-grupo">
                    <label for="localizacao">Endereço Completo</label>
                    <input type="text" id="localizacao" name="localizacao" required placeholder="Rua, Número, Bairro, Cidade - UF">
                </div>
                <div class="form-grupo">
                    <label for="descricao">Descrição / Detalhes</label>
                    <textarea id="descricao" name="descricao" rows="4" placeholder="Fale um pouco sobre a infraestrutura e história do abrigo..."></textarea>
                </div>
                <div class="modal-admin-footer">
                    <button type="button" class="btn-modal-cancelar" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn-modal-salvar">Salvar Abrigo</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModal() {
            document.getElementById('modalCadastro').classList.add('ativo');
        }
        function fecharModal() {
            document.getElementById('modalCadastro').classList.remove('ativo');
        }
        function fecharModalExterno(event) {
            if (event.target === document.getElementById('modalCadastro')) {
                fecharModal();
            }
        }
    </script>

</body>
</html>
