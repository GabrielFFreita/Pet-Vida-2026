<?php
require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../config/sessao.php";
require_once __DIR__ . "/../includes/animal-admin-helpers.php";

verificarAdmin();

$mensagem = null;
$tipoMensagem = "sucesso";

// Processamento da atualização de status da adoção
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idAdocao = filter_input(INPUT_POST, 'id_adocao', FILTER_VALIDATE_INT);
    $novoStatus = trim($_POST['novo_status'] ?? '');
    $statusValidos = ['Pendente', 'Aprovada', 'Rejeitada'];

    if (!$idAdocao || !in_array($novoStatus, $statusValidos, true)) {
        header("Location: adocoes.php?status=erro_dados");
        exit;
    }

    try {
        // Busca a adoção para pegar o id_animal associado
        $stmtBusca = $pdo->prepare("SELECT id_adocao, id_animal, status FROM adocao WHERE id_adocao = :id");
        $stmtBusca->execute([':id' => $idAdocao]);
        $adocao = $stmtBusca->fetch(PDO::FETCH_ASSOC);

        if (!$adocao) {
            header("Location: adocoes.php?status=nao_encontrado");
            exit;
        }

        $idAnimal = (int)$adocao['id_animal'];

        $pdo->beginTransaction();

        // 1. Atualiza o status na tabela adocao
        $stmtUpAdocao = $pdo->prepare("UPDATE adocao SET status = :status WHERE id_adocao = :id");
        $stmtUpAdocao->execute([
            ':status' => $novoStatus,
            ':id'     => $idAdocao
        ]);

        // 2. Sincroniza o status do pet na tabela animais_adocao
        if ($novoStatus === 'Aprovada') {
            $stmtUpAnimal = $pdo->prepare("UPDATE animais_adocao SET status_adocao = 'Adotado' WHERE id_animal = :id_animal");
            $stmtUpAnimal->execute([':id_animal' => $idAnimal]);
        } elseif ($novoStatus === 'Rejeitada') {
            // Verifica se há outras solicitações ainda pendentes para esse mesmo animal
            $stmtCheckOutros = $pdo->prepare("SELECT COUNT(*) FROM adocao WHERE id_animal = :id_animal AND status = 'Pendente' AND id_adocao <> :id_adocao");
            $stmtCheckOutros->execute([':id_animal' => $idAnimal, ':id_adocao' => $idAdocao]);
            $temOutrosPendentes = (int)$stmtCheckOutros->fetchColumn();

            if ($temOutrosPendentes > 0) {
                $stmtUpAnimal = $pdo->prepare("UPDATE animais_adocao SET status_adocao = 'Em processo' WHERE id_animal = :id_animal");
            } else {
                // Não tem mais ninguém na fila pendente, libera o animal para 'Disponível'
                $stmtUpAnimal = $pdo->prepare("UPDATE animais_adocao SET status_adocao = 'Disponível' WHERE id_animal = :id_animal");
            }
            $stmtUpAnimal->execute([':id_animal' => $idAnimal]);
        } elseif ($novoStatus === 'Pendente') {
            $stmtUpAnimal = $pdo->prepare("UPDATE animais_adocao SET status_adocao = 'Em processo' WHERE id_animal = :id_animal");
            $stmtUpAnimal->execute([':id_animal' => $idAnimal]);
        }

        $pdo->commit();

        header("Location: adocoes.php?status=atualizado");
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: adocoes.php?status=erro_sql");
        exit;
    }
}

// Mensagens de retorno via URL
if (isset($_GET['status'])) {
    $st = $_GET['status'];
    if ($st === 'atualizado') {
        $mensagem = "Status do processo de adoção atualizado com sucesso!";
    } elseif ($st === 'erro_dados') {
        $mensagem = "Dados inválidos para alteração de status.";
        $tipoMensagem = "erro";
    } elseif ($st === 'nao_encontrado') {
        $mensagem = "Solicitação de adoção não encontrada.";
        $tipoMensagem = "erro";
    } elseif ($st === 'erro_sql') {
        $mensagem = "Erro ao processar a atualização no banco de dados.";
        $tipoMensagem = "erro";
    }
}

// Filtro selecionado
$filtroStatus = trim($_GET['filtro'] ?? '');
$busca = trim($_GET['busca'] ?? '');

try {
    // Métricas gerais
    $metricas = [
        'total'     => (int)$pdo->query("SELECT COUNT(*) FROM adocao")->fetchColumn(),
        'pendentes' => (int)$pdo->query("SELECT COUNT(*) FROM adocao WHERE status = 'Pendente'")->fetchColumn(),
        'aprovadas' => (int)$pdo->query("SELECT COUNT(*) FROM adocao WHERE status = 'Aprovada'")->fetchColumn(),
        'rejeitadas' => (int)$pdo->query("SELECT COUNT(*) FROM adocao WHERE status = 'Rejeitada'")->fetchColumn(),
    ];

    // Monta a consulta de solicitações
    $sql = "SELECT 
                ad.id_adocao,
                ad.data_solicitacao,
                ad.status AS status_adocao,
                a.id_animal,
                a.nome AS nome_animal,
                a.especie,
                a.raca,
                a.status_adocao AS status_animal,
                u.id_usuario,
                u.nome AS nome_usuario,
                u.email AS email_usuario,
                u.telefone AS telefone_usuario,
                u.cidade AS cidade_usuario,
                u.estado AS estado_usuario,
                ab.nome AS nome_abrigo,
                fp.ds_img AS foto_pet
            FROM adocao ad
            INNER JOIN animais_adocao a ON ad.id_animal = a.id_animal
            INNER JOIN usuarios u ON ad.id_usuario = u.id_usuario
            LEFT JOIN abrigos ab ON a.id_abrigo = ab.id
            LEFT JOIN (
                SELECT id_animal, MIN(ds_img) AS ds_img
                FROM foto_animal
                GROUP BY id_animal
            ) fp ON fp.id_animal = a.id_animal
            WHERE 1=1";

    $params = [];

    if ($filtroStatus !== '' && in_array($filtroStatus, ['Pendente', 'Aprovada', 'Rejeitada'], true)) {
        $sql .= " AND ad.status = :filtro_status";
        $params[':filtro_status'] = $filtroStatus;
    }

    if ($busca !== '') {
        $sql .= " AND (u.nome LIKE :busca OR a.nome LIKE :busca OR u.email LIKE :busca OR ab.nome LIKE :busca)";
        $params[':busca'] = '%' . $busca . '%';
    }

    $sql .= " ORDER BY ad.id_adocao DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $solicitacoes = [];
    $metricas = ['total' => 0, 'pendentes' => 0, 'aprovadas' => 0, 'rejeitadas' => 0];
    $mensagem = "Erro ao carregar lista de adoções.";
    $tipoMensagem = "erro";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Adoções | Pet Vida Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@400;600&family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .filtros-adocoes-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }
        .botoes-filtro-status {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .btn-filtro-item {
            padding: 8px 16px;
            border-radius: 20px;
            border: 1px solid var(--borda);
            background: #FFFFFF;
            color: var(--texto);
            font-size: 0.9rem;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transicao);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-filtro-item:hover {
            border-color: var(--primaria);
            color: var(--primaria);
        }
        .btn-filtro-item.ativo {
            background-color: var(--primaria);
            color: #FFFFFF;
            border-color: var(--primaria);
        }
        .form-busca-adocoes {
            display: flex;
            gap: 8px;
        }
        .input-busca-admin {
            padding: 8px 14px;
            border: 1px solid var(--borda);
            border-radius: 8px;
            font-size: 0.9rem;
            min-width: 240px;
        }
        .btn-busca-admin {
            background: var(--primaria);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: var(--transicao);
        }
        .btn-busca-admin:hover {
            background: var(--primaria-escura);
        }
        /* Badges de Status */
        .badge-adocao {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .badge-adocao--pendente {
            background-color: #FEF3C7;
            color: #B45309;
        }
        .badge-adocao--aprovada {
            background-color: #DCFCE7;
            color: #15803D;
        }
        .badge-adocao--rejeitada {
            background-color: #FEE2E2;
            color: #B91C1C;
        }
        .status-animal-badge {
            font-size: 0.78rem;
            color: var(--texto-leve);
            display: block;
            margin-top: 3px;
        }
        /* Contatos do Adotante */
        .adotante-detalhes {
            font-size: 0.85rem;
            color: var(--texto-leve);
            line-height: 1.4;
            margin-top: 3px;
        }
        .adotante-detalhes a {
            color: var(--primaria);
            text-decoration: none;
        }
        .adotante-detalhes a:hover {
            text-decoration: underline;
        }
        .link-whatsapp {
            color: #25D366 !important;
            font-weight: 600;
        }
        /* Ações Rápidas */
        .acoes-adocao-form {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            align-items: center;
        }
        .btn-acao-status {
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transicao);
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-acao-aprovar {
            background-color: #DCFCE7;
            color: #15803D;
            border: 1px solid #BBF7D0;
        }
        .btn-acao-aprovar:hover {
            background-color: #15803D;
            color: #FFFFFF;
        }
        .btn-acao-rejeitar {
            background-color: #FEE2E2;
            color: #B91C1C;
            border: 1px solid #FECACA;
        }
        .btn-acao-rejeitar:hover {
            background-color: #B91C1C;
            color: #FFFFFF;
        }
        .btn-acao-reabrir {
            background-color: #FEF3C7;
            color: #B45309;
            border: 1px solid #FDE68A;
        }
        .btn-acao-reabrir:hover {
            background-color: #B45309;
            color: #FFFFFF;
        }
    </style>
</head>
<body>

    <?php $adminActivePage = 'adocoes'; require __DIR__ . '/../includes/menu-admin.php'; ?>

    <main class="content">
        <div class="header-acoes-admin header-acoes-admin--stack">
            <div>
                <h1>Gerenciar Processos de Adoção</h1>
                <p class="subtitulo">Acompanhe as solicitações enviadas pelos usuários e atualize o status de cada processo de adoção.</p>
            </div>
        </div>

        <?php if ($mensagem !== null): ?>
            <div class="alerta-admin <?php echo $tipoMensagem === "erro" ? "alerta-admin--erro" : "alerta-admin--sucesso"; ?>">
                <?php echo htmlspecialchars($mensagem, ENT_QUOTES, "UTF-8"); ?>
            </div>
        <?php endif; ?>

        <!-- Cards de Métricas -->
        <section class="dashboard-grid">
            <article class="card-metrica">
                <h3>Total de Pedidos</h3>
                <div class="valor-metrica"><?php echo $metricas["total"]; ?></div>
                <span class="legenda-metrica">Histórico geral de solicitações</span>
            </article>
            <article class="card-metrica">
                <h3>Em Análise</h3>
                <div class="valor-metrica" style="color: #D97706;"><?php echo $metricas["pendentes"]; ?></div>
                <span class="legenda-metrica">Aguardando decisão da equipe</span>
            </article>
            <article class="card-metrica">
                <h3>Aprovadas</h3>
                <div class="valor-metrica" style="color: #16A34A;"><?php echo $metricas["aprovadas"]; ?></div>
                <span class="legenda-metrica">Adoções concluídas com sucesso</span>
            </article>
            <article class="card-metrica">
                <h3>Rejeitadas</h3>
                <div class="valor-metrica" style="color: #DC2626;"><?php echo $metricas["rejeitadas"]; ?></div>
                <span class="legenda-metrica">Processos não finalizados</span>
            </article>
        </section>

        <!-- Filtros e Busca -->
        <div class="filtros-adocoes-bar">
            <div class="botoes-filtro-status">
                <a href="adocoes.php<?= $busca ? '?busca=' . urlencode($busca) : '' ?>" class="btn-filtro-item <?= $filtroStatus === '' ? 'ativo' : '' ?>">
                    Todos (<?= $metricas['total'] ?>)
                </a>
                <a href="adocoes.php?filtro=Pendente<?= $busca ? '&busca=' . urlencode($busca) : '' ?>" class="btn-filtro-item <?= $filtroStatus === 'Pendente' ? 'ativo' : '' ?>">
                    <i class="fas fa-clock"></i> Em Análise (<?= $metricas['pendentes'] ?>)
                </a>
                <a href="adocoes.php?filtro=Aprovada<?= $busca ? '&busca=' . urlencode($busca) : '' ?>" class="btn-filtro-item <?= $filtroStatus === 'Aprovada' ? 'ativo' : '' ?>">
                    <i class="fas fa-check-circle"></i> Aprovadas (<?= $metricas['aprovadas'] ?>)
                </a>
                <a href="adocoes.php?filtro=Rejeitada<?= $busca ? '&busca=' . urlencode($busca) : '' ?>" class="btn-filtro-item <?= $filtroStatus === 'Rejeitada' ? 'ativo' : '' ?>">
                    <i class="fas fa-times-circle"></i> Rejeitadas (<?= $metricas['rejeitadas'] ?>)
                </a>
            </div>

            <form action="adocoes.php" method="GET" class="form-busca-adocoes">
                <?php if ($filtroStatus): ?>
                    <input type="hidden" name="filtro" value="<?= htmlspecialchars($filtroStatus, ENT_QUOTES, 'UTF-8') ?>">
                <?php endif; ?>
                <input type="search" name="busca" class="input-busca-admin" placeholder="Buscar por adotante, pet ou abrigo..." value="<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn-busca-admin"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <!-- Tabela de Solicitações -->
        <section class="painel-card painel-card--usuarios">
            <div class="painel-card-topo">
                <div>
                    <h2>Solicitações de Adoção</h2>
                    <p>Atualize o status dos pedidos para manter adotantes e abrigos alinhados sobre o andamento de cada pet.</p>
                </div>
                <div class="painel-resumo-pill">
                    <?php echo count($solicitacoes); ?> registro(s) exibido(s)
                </div>
            </div>

            <div class="tabela-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Pet Solicitado</th>
                            <th>Adotante Interessado</th>
                            <th>Abrigo</th>
                            <th>Data do Pedido</th>
                            <th>Status da Adoção</th>
                            <th style="min-width: 220px;">Atualizar Processo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($solicitacoes)): ?>
                            <tr>
                                <td colspan="6" class="estado-vazio-tabela">Nenhuma solicitação de adoção encontrada com os filtros atuais.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($solicitacoes as $item): ?>
                                <?php
                                    $status = $item['status_adocao'];
                                    $data = !empty($item['data_solicitacao']) ? date('d/m/Y', strtotime($item['data_solicitacao'])) : 'Não informada';
                                    $fotoCapa = animalAdminFotoPublica($item['foto_pet'] ?? null);
                                    
                                    // Limpeza do telefone para link direto do WhatsApp
                                    $telefoneNumeros = preg_replace('/\D/', '', $item['telefone_usuario'] ?? '');
                                    
                                    $badgeClasse = 'badge-adocao--pendente';
                                    if ($status === 'Aprovada') $badgeClasse = 'badge-adocao--aprovada';
                                    elseif ($status === 'Rejeitada') $badgeClasse = 'badge-adocao--rejeitada';
                                ?>
                                <tr>
                                    <td>
                                        <div class="animal-listagem-celula">
                                            <img src="<?php echo htmlspecialchars($fotoCapa, ENT_QUOTES, 'UTF-8'); ?>"
                                                 alt="Foto do animal"
                                                 class="animal-listagem-thumb">
                                            <div class="animal-listagem-texto">
                                                <strong><?php echo htmlspecialchars($item["nome_animal"], ENT_QUOTES, "UTF-8"); ?></strong>
                                                <span><?php echo htmlspecialchars($item["especie"] . ($item["raca"] ? ' • ' . $item["raca"] : ''), ENT_QUOTES, "UTF-8"); ?></span>
                                                <span class="status-animal-badge">Pet no sistema: <strong><?= htmlspecialchars($item['status_animal'], ENT_QUOTES, 'UTF-8') ?></strong></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item["nome_usuario"], ENT_QUOTES, "UTF-8"); ?></strong>
                                        <div class="adotante-detalhes">
                                            <div><i class="far fa-envelope"></i> <?php echo htmlspecialchars($item["email_usuario"], ENT_QUOTES, "UTF-8"); ?></div>
                                            <?php if (!empty($item["telefone_usuario"])): ?>
                                                <div>
                                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($item["telefone_usuario"], ENT_QUOTES, "UTF-8"); ?>
                                                    <?php if (strlen($telefoneNumeros) >= 10): ?>
                                                        <a href="https://wa.me/55<?= $telefoneNumeros ?>" target="_blank" rel="noopener noreferrer" class="link-whatsapp" title="Abrir conversa no WhatsApp">
                                                            <i class="fab fa-whatsapp"></i> WhatsApp
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($item["cidade_usuario"])): ?>
                                                <div><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($item["cidade_usuario"] . ($item["estado_usuario"] ? '/' . $item["estado_usuario"] : ''), ENT_QUOTES, "UTF-8") ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span><?php echo htmlspecialchars($item["nome_abrigo"] ?? "Não informado", ENT_QUOTES, "UTF-8"); ?></span>
                                    </td>
                                    <td>
                                        <span><?php echo htmlspecialchars($data, ENT_QUOTES, "UTF-8"); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge-adocao <?php echo $badgeClasse; ?>">
                                            <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                            <?php echo htmlspecialchars($status, ENT_QUOTES, "UTF-8"); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="acoes-adocao-form">
                                            <?php if ($status !== 'Aprovada'): ?>
                                                <form action="adocoes.php" method="POST" onsubmit="return confirm('Deseja realmente APROVAR a adoção de <?= htmlspecialchars($item['nome_animal'], ENT_QUOTES, 'UTF-8') ?> para <?= htmlspecialchars($item['nome_usuario'], ENT_QUOTES, 'UTF-8') ?>? O animal passará para o status Adotado.');">
                                                    <input type="hidden" name="id_adocao" value="<?php echo (int)$item['id_adocao']; ?>">
                                                    <input type="hidden" name="novo_status" value="Aprovada">
                                                    <button type="submit" class="btn-acao-status btn-acao-aprovar" title="Aprovar e marcar o pet como Adotado">
                                                        <i class="fas fa-check"></i> Aprovar
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($status !== 'Rejeitada'): ?>
                                                <form action="adocoes.php" method="POST" onsubmit="return confirm('Deseja realmente REJEITAR esta solicitação? Se não houver outros pedidos pendentes, o animal voltará para Disponível.');">
                                                    <input type="hidden" name="id_adocao" value="<?php echo (int)$item['id_adocao']; ?>">
                                                    <input type="hidden" name="novo_status" value="Rejeitada">
                                                    <button type="submit" class="btn-acao-status btn-acao-rejeitar" title="Rejeitar pedido de adoção">
                                                        <i class="fas fa-times"></i> Rejeitar
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($status !== 'Pendente'): ?>
                                                <form action="adocoes.php" method="POST" onsubmit="return confirm('Deseja reabrir a análise deste pedido como Pendente? O pet voltará para Em processo.');">
                                                    <input type="hidden" name="id_adocao" value="<?php echo (int)$item['id_adocao']; ?>">
                                                    <input type="hidden" name="novo_status" value="Pendente">
                                                    <button type="submit" class="btn-acao-status btn-acao-reabrir" title="Reabrir processo de adoção">
                                                        <i class="fas fa-undo"></i> Reabrir
                                                    </button>
                                                </form>
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

