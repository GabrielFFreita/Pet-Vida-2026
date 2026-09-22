<?php
require_once __DIR__ . '/config/conexao.php';
require_once __DIR__ . '/config/sessao.php';

// Apenas usuários logados podem acompanhar suas solicitações de adoção
verificarLogado();

$idUsuario = (int)$_SESSION['id_usuario'];
$pageTitle = 'Pet Vida - Minhas Adoções';

try {
    $sql = "SELECT 
                ad.id_adocao,
                ad.data_solicitacao,
                ad.status AS status_pedido,
                a.id_animal,
                a.nome AS nome_animal,
                a.especie,
                a.raca,
                a.sexo,
                a.idade,
                a.porte,
                a.status_adocao AS status_animal,
                ab.nome AS nome_abrigo,
                ab.localizacao AS localizacao_abrigo,
                ab.descricao AS descricao_abrigo,
                fp.ds_img AS foto_pet
            FROM adocao ad
            INNER JOIN animais_adocao a ON ad.id_animal = a.id_animal
            LEFT JOIN abrigos ab ON a.id_abrigo = ab.id
            LEFT JOIN (
                SELECT id_animal, MIN(ds_img) AS ds_img
                FROM foto_animal
                GROUP BY id_animal
            ) fp ON fp.id_animal = a.id_animal
            WHERE ad.id_usuario = :id_usuario
            ORDER BY ad.id_adocao DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_usuario' => $idUsuario]);
    $solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Contadores para resumo
    $totalSolicitacoes = count($solicitacoes);
    $totalPendentes = 0;
    $totalAprovadas = 0;
    $totalRejeitadas = 0;

    foreach ($solicitacoes as $s) {
        if ($s['status_pedido'] === 'Pendente') $totalPendentes++;
        elseif ($s['status_pedido'] === 'Aprovada') $totalAprovadas++;
        elseif ($s['status_pedido'] === 'Rejeitada') $totalRejeitadas++;
    }

} catch (PDOException $e) {
    $solicitacoes = [];
    $totalSolicitacoes = 0;
    $totalPendentes = 0;
    $totalAprovadas = 0;
    $totalRejeitadas = 0;
}

require __DIR__ . '/includes/header.php';
?>

<main class="minhas-adocoes-main">
    <div class="adocoes-shell">
        <header class="adocoes-cabecalho-secao">
            <div>
                <span class="adocoes-badge-topo"><i class="fas fa-paw"></i> Acompanhamento</span>
                <h1>Meus Processos de Adoção</h1>
                <p>Acompanhe aqui o andamento de cada solicitação que você enviou aos nossos abrigos parceiros.</p>
            </div>
            <a href="adocao.php" class="btn-adocoes-novo">
                <i class="fas fa-search"></i> Explorar mais pets
            </a>
        </header>

        <!-- Cards de Resumo -->
        <div class="adocoes-resumo-grid">
            <div class="card-resumo-adocao">
                <div class="resumo-icone icone-total"><i class="fas fa-folder-open"></i></div>
                <div class="resumo-conteudo">
                    <span class="resumo-valor"><?= $totalSolicitacoes ?></span>
                    <span class="resumo-rotulo">Total de Pedidos</span>
                </div>
            </div>
            <div class="card-resumo-adocao">
                <div class="resumo-icone icone-pendente"><i class="fas fa-clock"></i></div>
                <div class="resumo-conteudo">
                    <span class="resumo-valor"><?= $totalPendentes ?></span>
                    <span class="resumo-rotulo">Em Análise</span>
                </div>
            </div>
            <div class="card-resumo-adocao">
                <div class="resumo-icone icone-aprovada"><i class="fas fa-check-circle"></i></div>
                <div class="resumo-conteudo">
                    <span class="resumo-valor"><?= $totalAprovadas ?></span>
                    <span class="resumo-rotulo">Aprovadas</span>
                </div>
            </div>
            <div class="card-resumo-adocao">
                <div class="resumo-icone icone-rejeitada"><i class="fas fa-times-circle"></i></div>
                <div class="resumo-conteudo">
                    <span class="resumo-valor"><?= $totalRejeitadas ?></span>
                    <span class="resumo-rotulo">Não Concluídas</span>
                </div>
            </div>
        </div>

        <?php if (empty($solicitacoes)): ?>
            <div class="adocoes-vazio">
                <div class="adocoes-vazio-icone"><i class="fas fa-heart-broken"></i></div>
                <h2>Você ainda não solicitou nenhuma adoção</h2>
                <p>Navegue pela nossa vitrine de animais disponíveis, conheça suas histórias e encontre o companheiro perfeito para a sua família!</p>
                <a href="adocao.php" class="btn-adotar-inicio">
                    <i class="fas fa-paw"></i> Ver pets disponíveis
                </a>
            </div>
        <?php else: ?>
            <div class="adocoes-lista">
                <?php foreach ($solicitacoes as $item): ?>
                    <?php
                        $status = $item['status_pedido']; // Pendente, Aprovada, Rejeitada
                        $dataFormatada = !empty($item['data_solicitacao']) ? date('d/m/Y', strtotime($item['data_solicitacao'])) : 'Data não informada';
                        $foto = !empty($item['foto_pet']) ? 'uploads/' . ltrim($item['foto_pet'], '/\\') : 'assets/img/pets-exemplo/gato1.avif';
                        
                        // Classes e textos para a Timeline
                        $badgeClasse = 'badge-status--pendente';
                        $statusTexto = 'Em Análise';
                        if ($status === 'Aprovada') {
                            $badgeClasse = 'badge-status--aprovada';
                            $statusTexto = 'Adoção Aprovada';
                        } elseif ($status === 'Rejeitada') {
                            $badgeClasse = 'badge-status--rejeitada';
                            $statusTexto = 'Solicitação Rejeitada';
                        }
                    ?>
                    <article class="card-processo-adocao">
                        <div class="card-processo-header">
                            <div class="processo-pet-info">
                                <img src="<?= htmlspecialchars($foto, ENT_QUOTES, 'UTF-8') ?>" 
                                     alt="Foto de <?= htmlspecialchars($item['nome_animal'], ENT_QUOTES, 'UTF-8') ?>" 
                                     class="processo-pet-thumb"
                                     onerror="this.src='uploads/not_image.png'">
                                <div>
                                    <h2 class="processo-pet-nome"><?= htmlspecialchars($item['nome_animal'], ENT_QUOTES, 'UTF-8') ?></h2>
                                    <p class="processo-pet-detalhes">
                                        <span><i class="fas fa-tag"></i> <?= htmlspecialchars($item['raca'] ?: 'SRD', ENT_QUOTES, 'UTF-8') ?></span> •
                                        <span><i class="fas fa-venus-mars"></i> <?= htmlspecialchars($item['sexo'] ?: 'Não informado', ENT_QUOTES, 'UTF-8') ?></span> •
                                        <span><i class="fas fa-birthday-cake"></i> <?= htmlspecialchars($item['idade'] ?: '?', ENT_QUOTES, 'UTF-8') ?></span>
                                    </p>
                                    <p class="processo-pet-abrigo">
                                        <i class="fas fa-home"></i> <strong><?= htmlspecialchars($item['nome_abrigo'] ?? 'Abrigo parceiro', ENT_QUOTES, 'UTF-8') ?></strong>
                                        <?php if (!empty($item['localizacao_abrigo'])): ?>
                                            - <span class="abrigo-loc"><?= htmlspecialchars($item['localizacao_abrigo'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <div class="processo-meta">
                                <span class="badge-status-adocao-card <?= $badgeClasse ?>">
                                    <i class="fas fa-circle"></i> <?= htmlspecialchars($statusTexto, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <span class="processo-data-solicitacao">
                                    <i class="far fa-calendar-alt"></i> Solicitado em: <?= $dataFormatada ?>
                                </span>
                            </div>
                        </div>

                        <!-- Stepper / Linha do Tempo do Processo -->
                        <div class="stepper-adocao">
                            <!-- Etapa 1: Pedido Enviado -->
                            <div class="step-item step--completed">
                                <div class="step-marker"><i class="fas fa-check"></i></div>
                                <div class="step-text">
                                    <strong>Solicitação Enviada</strong>
                                    <span><?= $dataFormatada ?></span>
                                </div>
                            </div>

                            <div class="step-line <?= $status !== 'Pendente' ? 'line--active' : '' ?>"></div>

                            <!-- Etapa 2: Em Análise -->
                            <div class="step-item <?= $status === 'Pendente' ? 'step--active' : 'step--completed' ?>">
                                <div class="step-marker">
                                    <?php if ($status === 'Pendente'): ?>
                                        <i class="fas fa-spinner fa-spin"></i>
                                    <?php else: ?>
                                        <i class="fas fa-check"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="step-text">
                                    <strong>Análise pelo Abrigo</strong>
                                    <span><?= $status === 'Pendente' ? 'Em andamento' : 'Concluída' ?></span>
                                </div>
                            </div>

                            <div class="step-line <?= ($status === 'Aprovada' || $status === 'Rejeitada') ? 'line--active' : '' ?>"></div>

                            <!-- Etapa 3: Resposta Final -->
                            <?php if ($status === 'Aprovada'): ?>
                                <div class="step-item step--completed step--aprovado">
                                    <div class="step-marker"><i class="fas fa-heart"></i></div>
                                    <div class="step-text">
                                        <strong>Adoção Aprovada!</strong>
                                        <span>Pronto para o novo lar 🎉</span>
                                    </div>
                                </div>
                            <?php elseif ($status === 'Rejeitada'): ?>
                                <div class="step-item step--rejeitado">
                                    <div class="step-marker"><i class="fas fa-times"></i></div>
                                    <div class="step-text">
                                        <strong>Não Aprovada</strong>
                                        <span>Processo finalizado</span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="step-item step--waiting">
                                    <div class="step-marker"><i class="fas fa-hourglass-half"></i></div>
                                    <div class="step-text">
                                        <strong>Aguardando Decisão</strong>
                                        <span>Contato em até 48h</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Caixa de orientações ao adotante conforme o status -->
                        <div class="processo-orientacao-box">
                            <?php if ($status === 'Pendente'): ?>
                                <div class="orientacao-item orientacao--alerta">
                                    <i class="fas fa-info-circle"></i>
                                    <div>
                                        <strong>O que acontece agora?</strong>
                                        <p>A equipe do <strong><?= htmlspecialchars($item['nome_abrigo'] ?? 'abrigo', ENT_QUOTES, 'UTF-8') ?></strong> está avaliando sua solicitação de adoção. Eles entrarão em contato por telefone, WhatsApp ou e-mail para validar seus dados e agendar uma conversa!</p>
                                    </div>
                                </div>
                            <?php elseif ($status === 'Aprovada'): ?>
                                <div class="orientacao-item orientacao--sucesso">
                                    <i class="fas fa-check-circle"></i>
                                    <div>
                                        <strong>Parabéns! Sua adoção foi aprovada!</strong>
                                        <p>O abrigo parceiro já confirmou seu pedido. Caso ainda não tenha feito, entre em contato para combinar a retirada do seu novo amigo de 4 patas. Parabéns pelo gesto de amor!</p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="orientacao-item orientacao--aviso">
                                    <i class="fas fa-info-circle"></i>
                                    <div>
                                        <strong>Adoção não concluída desta vez</strong>
                                        <p>Infelizmente este processo não seguiu em frente. Isso pode ocorrer caso outro adotante já tenha sido selecionado ou por critérios específicos de adaptação ao perfil do pet. Mas não desanime, temos muitos outros animais esperando por carinho!</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

