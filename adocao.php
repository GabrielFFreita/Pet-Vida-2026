<?php
require_once "conexao.php";

// Trazemos todas as colunas necessárias e agrupamos todas as fotos separadas por vírgula
$sql = "SELECT 
    a.id_animal, 
    a.nome, 
    a.idade, 
    a.raca, 
    a.especie, 
    a.sexo, 
    a.porte, 
    a.peso, 
    a.vacinado, 
    a.abrigo, 
    a.descricao, 
    a.status_adocao,
    GROUP_CONCAT(f.ds_img SEPARATOR ',') as todas_fotos
FROM animais_adocao a
LEFT JOIN foto_animal f ON a.id_animal = f.id_animal
GROUP BY a.id_animal";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$animais = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,400;0,600;1,400&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="./css/adocao.css">
</head>
<body>

  <header role="banner">
    <div class="header-inner">

      <a href="index.html" class="logo" aria-label="Adote com Amor — Página inicial">
        <span class="logo-icon" aria-hidden="true">🐾</span>
        <span class="logo-text">Adote <em>com Amor</em></span>
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
        <button class="btn-favoritos" id="btn-favoritos" aria-label="Ver pets favoritos">
          <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          <span>Favoritos</span>
          <span class="favoritos-count" id="favoritos-count" hidden>0</span>
        </button>

        <button class="btn-cta" aria-label="Pet Quiz">
          <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          Pet Quiz
        </button>
      </div>

    </div>
  </header>

  <div class="filtros-bar" id="filtros-bar" role="region" aria-label="Filtros de busca">
    <div class="filtros-bar-inner">

      <div class="filtros-grupo" role="group" aria-label="Filtrar por abrigo">
        <button data-filtro-abrigo="todos" class="filtro-pill filtro-ativo" id="filtro-todos">Todos os abrigos</button>
        <button data-filtro-abrigo="centro" class="filtro-pill" id="filtro-centro">Abrigo Centro</button>
        <button data-filtro-abrigo="norte" class="filtro-pill" id="filtro-norte">Zona Norte</button>
        <button data-filtro-abrigo="sul" class="filtro-pill" id="filtro-sul">Zona Sul</button>
        <button data-filtro-abrigo="leste" class="filtro-pill" id="filtro-leste">Zona Leste</button>
      </div>

      <div class="filtros-separador" aria-hidden="true"></div>

      <div class="filtros-grupo" role="group" aria-label="Filtrar por tipo de pet">
        <button data-filtro-tipo="todos" class="filtro-pill filtro-ativo" id="filtro-tipo-todos">🐾 Todos</button>
        <button data-filtro-tipo="cachorro" class="filtro-pill" id="filtro-cachorro">🐶 Cachorros</button>
        <button data-filtro-tipo="gato" class="filtro-pill" id="filtro-gato">🐱 Gatos</button>
      </div>

      <button class="filtro-limpar" id="btn-limpar-filtros" aria-label="Limpar todos os filtros">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.87"/></svg>
        Limpar
      </button>

    </div>
  </div>

  <div class="overlay" id="overlay" role="presentation"></div>

  <div class="modal-favoritos" id="modal-favoritos" aria-label="Meus favoritos" aria-hidden="true" role="dialog">
    <div class="modal-fav-header">
      <h2>❤️ Meus Favoritos</h2>
      <button class="modal-fav-fechar" id="modal-fav-fechar" aria-label="Fechar painel de favoritos">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-fav-body" id="modal-fav-body">
    </div>
    <div class="modal-fav-vazio" id="modal-fav-vazio" hidden>
      <div class="modal-fav-vazio-icone" aria-hidden="true">🐾</div>
      <p>Você ainda não adicionou nenhum pet aos favoritos.</p>
      <p>Clique no coração (♡) nos cards para favoritar!</p>
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

      <div class="pet-card <?= strtolower($animal['especie']) ?>"
        data-id="<?= $animal['id_animal'] ?>"
        data-nome="<?= htmlspecialchars($animal['nome']) ?>"
        data-raca="<?= htmlspecialchars($animal['raca']) ?>"
        data-tipo="<?= strtolower($animal['especie']) ?>"
        data-abrigo="<?= strtolower($animal['abrigo']) ?>">

        <div class="card-img-wrapper">

            <button class="btn-fav-card">
                ❤
            </button>

            <img src="uploads/<?= htmlspecialchars($animal['ds_img'] ?? 'not_image.png') ?>"
                 alt="<?= htmlspecialchars($animal['nome']) ?>">
        </div>

        <div class="card-body">

            <h3 class="pet-nome">
                <?= htmlspecialchars($animal['nome']) ?>
            </h3>

            <div class="pet-info">
                <span><?= htmlspecialchars($animal['idade']) ?> anos</span>
                •
                <span><?= htmlspecialchars($animal['raca']) ?></span>
            </div>

            <button class="btn-detalhes">
                Ver Detalhes
            </button>

        </div>

    </div>

    <?php endforeach; ?>
<?php else: ?>
    <p>Nenhum animal disponível para adoção no momento.</p>
<?php endif; ?>

      </div>
    </div>

  </main>

  <footer role="contentinfo">
    <div class="footer-inner">
      <div class="footer-marca">
        <span class="logo-icon" aria-hidden="true">🐾</span>
        <p class="footer-slogan">Adote amor.<br><em>Mude uma vida.</em></p>
      </div>
      <div class="footer-links">
        <h4>Institucional</h4>
        <a href="#">Sobre nós</a>
        <a href="#">Como adotar</a>
        <a href="#">Política de privacidade</a>
      </div>
      <div class="footer-links">
        <h4>Ajuda</h4>
        <a href="#">Dúvidas frequentes</a>
        <a href="mailto:contato@petvida.org.br">Fale conosco</a>
      </div>
      <div class="footer-contato">
        <h4>Contato</h4>
        <a href="mailto:contato@petvida.org.br">contato@petvida.org.br</a>
        <a href="tel:+554799756519">(47) 99756-5199</a>
        <div class="footer-social">
          <a href="#" aria-label="Email">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
          </a>
          <a href="#" aria-label="Instagram">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
          </a>
          <a href="#" aria-label="WhatsApp">
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
              <span class="info-label">Altura</span>
              <span class="info-valor" id="detalhes-altura"></span>
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

  <script src="js/adocao.js"></script>
</body>
</html>