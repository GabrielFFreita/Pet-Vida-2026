<?php
require_once __DIR__ . "/config/conexao.php";

$petAutoOpenId = filter_input(INPUT_GET, 'pet', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;

$sqlAbrigos = "SELECT id, nome, localizacao FROM abrigos ORDER BY nome";
$stmtAbrigos = $pdo->prepare($sqlAbrigos);
$stmtAbrigos->execute();
$abrigos = $stmtAbrigos->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT
    a.*,
    ab.id AS abrigo_id,
    ab.nome AS abrigo_nome,
    ab.localizacao AS abrigo_localizacao,
    fotos.ds_img,
    fotos.fotos
FROM animais_adocao a
LEFT JOIN abrigos ab ON a.id_abrigo = ab.id
LEFT JOIN (
    SELECT
        id_animal,
        MIN(ds_img) AS ds_img,
        GROUP_CONCAT(ds_img ORDER BY ds_img SEPARATOR '||') AS fotos
    FROM foto_animal
    GROUP BY id_animal
) fotos ON a.id_animal = fotos.id_animal";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$animais = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lista de raças únicas presentes no banco, pra montar os pills do filtro
// (ordenadas alfabeticamente, ignorando vazias/nulas e duplicadas).
$racasUnicas = [];
foreach ($animais as $animal) {
    $raca = trim($animal['raca'] ?? '');
    if ($raca !== '' && !in_array($raca, $racasUnicas, true)) {
        $racasUnicas[] = $raca;
    }
}
sort($racasUnicas, SORT_LOCALE_STRING);

$abrigosJs = [];
foreach ($abrigos as $abrigo) {
    $abrigosJs[(string)$abrigo['id']] = [
        'nome' => $abrigo['nome'],
        'endereco' => $abrigo['localizacao'] ?? 'Não informado',
        'telefone' => 'Não informado',
        'horario' => 'Não informado'
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Adote com Amor — Pets disponíveis para adoção</title>
  <meta name="description" content="Encontre seu novo melhor amigo! Adote cães e gatos de todas as idades e raças. Transforme vidas com adoção responsável.">
  <meta name="keywords" content="adoção de pets, cachorros para adoção, gatos para adoção, adoção responsável, pet vida">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,400;0,600;1,400&family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/adocao.css">
</head>
<body>
  <div vw class="enabled">
    <div vw-access-button class="active"></div>
    <div vw-plugin-wrapper>
      <div class="vw-plugin-top-wrapper"></div>
    </div>
  </div>
  <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
  <script>new window.VLibras.Widget('https://vlibras.gov.br/app');</script>

  <header role="banner">
    <div class="header-inner">

      <a href="index.php" class="logo" aria-label="Página inicial">
        <img src="assets/img/logo/logo_petvida.png" alt="Pet Vida" class="logo-img">
        <span class="logo-text">Pet <em>Vida</em></span>
      </a>

      <div class="nav-busca-wrapper">
        <div class="nav-busca-inner">
          <label for="busca-pet" class="sr-only">Buscar pets por nome ou raça</label>
          <svg class="busca-icone" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="search" id="busca-pet" placeholder="Buscar por nome ou raça…" autocomplete="off">
          <button class="busca-limpar" id="busca-limpar" aria-label="Limpar busca" hidden>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
      </div>

      <button class="nav-toggle" id="nav-toggle" aria-label="Abrir menu" aria-expanded="false" aria-controls="nav-acoes">
        <span></span><span></span><span></span>
      </button>

      <div class="nav-acoes" id="nav-acoes">
        <button class="btn-favoritos" id="btn-favoritos" aria-label="Mostrar apenas favoritos" aria-pressed="false">
          <svg class="fav-icon-nav" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
          </svg>
          <span>Favoritos</span>
          <span class="favoritos-count" id="favoritos-count" hidden>0</span>
        </button>

        <a href="quiz.php" class="btn-cta" aria-label="Pet Quiz">
          Pet Quiz
        </a>
      </div>

    </div>
  </header>

  <div class="filtros-bar" id="filtros-bar" role="region" aria-label="Filtros de busca">
    <div class="filtros-bar-inner">

      <div class="filtros-grupo" role="group" aria-label="Filtrar por abrigo">
        <button data-filtro-abrigo="todos" class="filtro-pill filtro-ativo" id="filtro-todos">Todos os abrigos</button>
        <?php foreach ($abrigos as $abrigo): ?>
          <?php
            // Remove o prefixo "Abrigo " só na exibição do filtro (o nome
            // completo continua intacto em qualquer outro lugar da página),
            // pra caber tudo numa linha só como no mockup.
            $nomeFiltro = preg_replace('/^Abrigo\s+/i', '', $abrigo['nome']);
          ?>
          <button data-filtro-abrigo="<?= htmlspecialchars($abrigo['id'], ENT_QUOTES, 'UTF-8') ?>" class="filtro-pill">
            <?= htmlspecialchars($nomeFiltro, ENT_QUOTES, 'UTF-8') ?>
          </button>
        <?php endforeach; ?>
      </div>

      <div class="filtros-separador" aria-hidden="true"></div>

      <div class="filtros-grupo" role="group" aria-label="Filtrar por tipo de pet">
        <button data-filtro-tipo="todos" class="filtro-pill filtro-ativo" id="filtro-tipo-todos">Todos</button>
        <button data-filtro-tipo="cachorro" class="filtro-pill" id="filtro-cachorro">Cachorros</button>
        <button data-filtro-tipo="gato" class="filtro-pill" id="filtro-gato">Gatos</button>
      </div>

      <div class="filtros-separador" aria-hidden="true"></div>

      <div class="filtros-grupo" role="group" aria-label="Filtrar por sexo">
        <button data-filtro-sexo="todos" class="filtro-pill filtro-pill--sexo filtro-ativo" id="filtro-sexo-todos">Ambos os sexos</button>
        <button data-filtro-sexo="macho" class="filtro-pill filtro-pill--sexo" id="filtro-macho">Macho</button>
        <button data-filtro-sexo="femea" class="filtro-pill filtro-pill--sexo" id="filtro-femea">Fêmea</button>
      </div>

      <button class="filtro-limpar" id="btn-limpar-filtros" aria-label="Limpar todos os filtros">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.87"/></svg>
        Limpar
      </button>

    </div>
  </div>

  <div class="overlay" id="overlay" role="presentation"></div>

  <div class="modal-favoritos" id="modal-favoritos" aria-label="Meus favoritos" aria-hidden="true" role="dialog" style="display:none">
    <div class="modal-fav-header">
      <h2>❤️ Meus Favoritos</h2>
      <button class="modal-fav-fechar" id="modal-fav-fechar" aria-label="Fechar painel de favoritos">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-fav-body" id="modal-fav-body"></div>
    <div class="modal-fav-vazio" id="modal-fav-vazio" hidden>
      <div class="modal-fav-vazio-icone" aria-hidden="true">🐾</div>
      <p>Você ainda não adicionou nenhum pet aos favoritos.</p>
    </div>
  </div>

  <main class="conteudo-principal" id="conteudo-principal" role="main">

    <div id="lista-pets">
      <div class="pets-container">

        <div class="sem-resultados" aria-live="polite">
          <div class="sem-resultados-icone" aria-hidden="true">🐾</div>
          <h2>Nenhum pet encontrado</h2>
          <p>Tente ajustar os filtros ou a busca.</p>
          <button class="btn-mostrar-todos" id="btn-mostrar-todos">Mostrar todos</button>
        </div>

        <?php if (!empty($animais)): ?>
          <?php foreach ($animais as $animal): ?>
            <?php
              $fotos = array_values(array_filter(explode('||', $animal['fotos'] ?? '')));
              if (empty($fotos)) {
                  $fotos = ['not_image.png'];
              }
              $fotosCarrossel = array_map(function($foto) {
                  return 'uploads/' . $foto;
              }, $fotos);
              $fotoPrincipal = $fotosCarrossel[0];

              $vacinadoTexto  = isset($animal['vacinado']) ? ((int)$animal['vacinado'] === 1 ? 'Sim' : 'Não') : 'Não informado';
              $castradoTexto  = isset($animal['castrado']) ? ((int)$animal['castrado'] === 1 ? 'Sim' : 'Não') : 'Não informado';
              $abrigoId       = (string)($animal['abrigo_id'] ?? $animal['abrigo'] ?? '');
              $abrigoNome     = $animal['abrigo_nome'] ?? 'Não informado';
              $deficienciaTexto = !empty($animal['deficiencia']) ? $animal['deficiencia'] : 'Nenhuma';
              $statusAtual    = trim($animal['status_animal'] ?? $animal['status_adocao'] ?? 'Disponível');

              $especie        = strtolower($animal['especie'] ?? '');
              $especieClasse  = $especie === 'gato' ? 'gato' : 'cachorro';
              $tagClasse      = $especie === 'gato' ? 'tag-gato' : 'tag-cachorro';
              $tipoLabel      = $especie === 'gato' ? 'Gata' : 'Cachorro';

              // [FIX] Comparação normalizada para gerar a classe de badge correta
              // e permitir exibir o status direto da coluna do banco.
              $statusNormalizado = mb_strtolower($statusAtual, 'UTF-8');
              $statusClasseMap = [
                  'disponível'  => 'status-disponivel',
                  'disponivel'  => 'status-disponivel',
                  'novo'        => 'status-novo',
                  'em processo' => 'status-processo',
                  'em análise'  => 'status-processo',
                  'reservado'   => 'status-processo',
                  'adotado'     => 'status-adotado',
              ];
              $statusClasse = $statusClasseMap[$statusNormalizado] ?? 'status-padrao';
            ?>

            <article class="pet-card <?= $especieClasse ?>"
              data-id="<?= htmlspecialchars($animal['id_animal'], ENT_QUOTES, 'UTF-8') ?>"
              data-nome="<?= htmlspecialchars($animal['nome'], ENT_QUOTES, 'UTF-8') ?>"
              data-tipo="<?= htmlspecialchars($tipoLabel, ENT_QUOTES, 'UTF-8') ?>"
              data-raca="<?= htmlspecialchars($animal['raca'] ?? 'Não informada', ENT_QUOTES, 'UTF-8') ?>"
              data-sexo="<?= htmlspecialchars($animal['sexo'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>"
              data-peso="<?= htmlspecialchars(isset($animal['peso']) ? $animal['peso'] . ' kg' : 'Não informado', ENT_QUOTES, 'UTF-8') ?>"
              data-idade="<?= htmlspecialchars(isset($animal['idade']) ? $animal['idade'] . ' anos' : 'Não informada', ENT_QUOTES, 'UTF-8') ?>"
              data-porte="<?= htmlspecialchars($animal['porte'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>"
              data-vacinado="<?= htmlspecialchars($vacinadoTexto, ENT_QUOTES, 'UTF-8') ?>"
              data-castrado="<?= htmlspecialchars($castradoTexto, ENT_QUOTES, 'UTF-8') ?>"
              data-deficiencia="<?= htmlspecialchars($deficienciaTexto, ENT_QUOTES, 'UTF-8') ?>"
              data-abrigo="<?= htmlspecialchars($abrigoId, ENT_QUOTES, 'UTF-8') ?>"
              data-status="<?= htmlspecialchars($statusAtual, ENT_QUOTES, 'UTF-8') ?>"
              data-descricao="<?= htmlspecialchars($animal['descricao'] ?? 'Pet especial aguardando um lar.', ENT_QUOTES, 'UTF-8') ?>"
              data-fotos="<?= htmlspecialchars(json_encode($fotosCarrossel, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>">

              <div class="card-img-wrapper">
                <div class="card-img-loader" aria-hidden="true"></div>
                <img src="<?= htmlspecialchars($fotoPrincipal, ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($animal['nome'] . ', ' . ($animal['raca'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                     loading="lazy">

                <?php if ($statusClasse): ?>
                  <span class="badge-status-adocao <?= $statusClasse ?>" data-status-badge><?= htmlspecialchars($statusAtual, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>

                <button class="btn-fav-card" aria-label="Favoritar <?= htmlspecialchars($animal['nome'], ENT_QUOTES, 'UTF-8') ?>" data-id="<?= htmlspecialchars($animal['id_animal'], ENT_QUOTES, 'UTF-8') ?>">
                  <svg class="heart-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                  </svg>
                </button>
              </div>

              <div class="card-body">
                <h3 class="pet-nome"><?= htmlspecialchars($animal['nome'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="pet-info">
                  <span class="tag-tipo <?= $tagClasse ?>"><?= htmlspecialchars($tipoLabel, ENT_QUOTES, 'UTF-8') ?></span>
                  · <?= htmlspecialchars($animal['porte'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>
                  · <?= htmlspecialchars($animal['idade'] ?? '?', ENT_QUOTES, 'UTF-8') ?> anos
                </p>
                <p class="pet-localizacao">
                  <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                  <?= htmlspecialchars($abrigoNome, ENT_QUOTES, 'UTF-8') ?>
                </p>
                <button class="btn-detalhes" aria-label="Ver detalhes de <?= htmlspecialchars($animal['nome'], ENT_QUOTES, 'UTF-8') ?>">Ver Detalhes</button>
              </div>

            </article>

          <?php endforeach; ?>
        <?php endif; ?>

      </div>
    </div>

  </main>

  <footer role="contentinfo">
    <div class="footer-inner">
      <div class="footer-marca">
        <img src="assets/img/logo/logo_petvida.png" alt="Pet Vida" class="logo-img">
        <span class="logo-text">Pet <em>Vida</em></span>
      </div>
      <div class="footer-links">
        <h4>Institucional</h4>
        <a href="index.php">Sobre nós</a>
        <a href="index.php">Como adotar</a>
        <a href="index.php">Política de privacidade</a>
      </div>
      <div class="footer-links">
        <h4>Ajuda</h4>
        <a href="index.php">Dúvidas frequentes</a>
        <a href="mailto:contato@petvida.org.br">Fale conosco</a>
      </div>
      <div class="footer-contato">
        <h4>Contato</h4>
        <a href="mailto:contato@petvida.org.br" class="footer-contato-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          contato@petvida.org.br
        </a>
        <a href="tel:+554799756519" class="footer-contato-link">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.124.557 4.118 1.532 5.851L.057 23.428a.5.5 0 0 0 .614.614l5.594-1.464A11.945 11.945 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.907 0-3.693-.507-5.234-1.39l-.376-.219-3.892 1.02 1.038-3.786-.233-.391A9.937 9.937 0 0 1 2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
          (47) 99756-5199
        </a>
        <a href="#" class="footer-contato-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
          @petvida.oficial
        </a>
        <div class="footer-social">
          <a href="mailto:contato@petvida.org.br" aria-label="Email">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          </a>
          <a href="#" aria-label="Instagram">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
          </a>
          <a href="tel:+554799756519" aria-label="WhatsApp">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.124.557 4.118 1.532 5.851L.057 23.428a.5.5 0 0 0 .614.614l5.594-1.464A11.945 11.945 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.907 0-3.693-.507-5.234-1.39l-.376-.219-3.892 1.02 1.038-3.786-.233-.391A9.937 9.937 0 0 1 2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
          </a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2025 Adote com Amor · Todos os direitos reservados</p>
    </div>
  </footer>

  <section id="detalhes-pet" aria-label="Detalhes do pet" aria-hidden="true">
    <div class="detalhes-topo">
      <button class="btn-voltar-det" id="btn-voltar-det" aria-label="Voltar para lista de pets">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Voltar
      </button>
    </div>

    <div class="detalhes-container">
      <div class="carrossel-col">
        <div class="carrossel-container">
          <div class="carrossel-imagens" id="carrossel-imagens" role="img" aria-label="Fotos do pet"></div>
          <button class="carrossel-btn carrossel-prev" id="btn-anterior" aria-label="Foto anterior">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
          </button>
          <button class="carrossel-btn carrossel-next" id="btn-proximo" aria-label="Próxima foto">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
          <div class="carrossel-indicadores" id="carrossel-indicadores" role="tablist" aria-label="Navegar entre fotos"></div>
        </div>
      </div>

      <div class="info-col">
        <div class="info-pet">
          <h2 id="detalhes-nome" class="det-nome"></h2>
          <p class="det-status-line"><span id="detalhes-status" class="det-status-badge"></span></p>

          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">Tipo</span>
              <span class="info-valor" id="detalhes-tipo"></span>
            </div>
            <div class="info-item">
              <span class="info-label">Raça</span>
              <span class="info-valor" id="detalhes-raca"></span>
            </div>
            <div class="info-item">
              <span class="info-label">Sexo</span>
              <span class="info-valor" id="detalhes-sexo"></span>
            </div>
            <div class="info-item">
              <span class="info-label">Idade</span>
              <span class="info-valor" id="detalhes-idade"></span>
            </div>
            <div class="info-item">
              <span class="info-label">Porte</span>
              <span class="info-valor" id="detalhes-porte"></span>
            </div>
            <div class="info-item">
              <span class="info-label">Peso</span>
              <span class="info-valor" id="detalhes-peso"></span>
            </div>
            <div class="info-item">
              <span class="info-label">Vacinado</span>
              <span class="info-valor" id="detalhes-vacinado"></span>
            </div>
            <div class="info-item">
              <span class="info-label">Castrado</span>
              <span class="info-valor" id="detalhes-castrado"></span>
            </div>
            <div class="info-item">
              <span class="info-label">Deficiência</span>
              <span class="info-valor" id="detalhes-deficiencia"></span>
            </div>
            <div class="info-item info-item--wide">
              <span class="info-label">Localização</span>
              <span class="info-valor" id="detalhes-localizacao"></span>
            </div>
          </div>

          <div class="descricao-pet">
            <h3>Sobre</h3>
            <p id="detalhes-descricao"></p>
          </div>

          <div class="acoes-pet">
            <button class="btn-adotar" id="btn-adotar-det">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              Quero adotar
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div id="modal-bg" class="modal-bg" role="dialog" aria-modal="true" aria-labelledby="modal-titulo" aria-hidden="true">
    <div class="modal" id="modal-confirmacao">
      <button class="modal-fechar" id="modal-fechar-1" aria-label="Fechar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
      <div class="modal-icone" aria-hidden="true">🐾</div>
      <h3 id="modal-titulo">Confirmar interesse</h3>
      <p>Você escolheu adotar este pet. Confirmar?</p>
      <div class="info-adocao" id="info-adocao"></div>
      <div class="modal-acoes">
        <button class="btn-sim" id="btn-confirmar-adocao">Sim, quero adotar!</button>
        <button class="btn-nao" id="btn-cancelar-modal">Cancelar</button>
      </div>
    </div>
  </div>

  <div id="modal-final-bg" class="modal-bg" role="dialog" aria-modal="true" aria-labelledby="modal-final-titulo" aria-hidden="true">
    <div class="modal modal--sucesso" id="modal-final">
      <button class="modal-fechar" id="modal-fechar-2" aria-label="Fechar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
      <div class="modal-icone sucesso-icone" aria-hidden="true">🎉</div>
      <h3 id="modal-final-titulo">Interesse registrado!</h3>
      <p>Sua solicitação foi enviada com sucesso.</p>
      <div class="info-adocao" id="info-final"></div>
      <p class="modal-nota">Nossa equipe entrará em contato em até 48 horas para confirmar os detalhes.</p>
      <div class="modal-acoes">
        <button class="btn-sim" id="btn-fechar-final">Entendi, obrigado!</button>
      </div>
    </div>
  </div>

  <script>
    window.ABRIGOS_DADOS = <?= json_encode($abrigosJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    window.PET_AUTO_OPEN_ID = <?= json_encode($petAutoOpenId, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  </script>
  <script src="assets/js/adocao.js"></script>
</body>
</html>
