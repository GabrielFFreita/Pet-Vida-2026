<?php
/**
 * quiz.php
 * Página única do Pet Quiz: exibe o formulário interativo e, ao final,
 * processa as respostas, calcula o perfil (Gato/Cachorro/Ambos) e busca
 * os animais disponíveis no banco usando PDO com prepared statements.
 *
 * Quando a requisição é um POST com o corpo em JSON (enviado via fetch
 * pelo JavaScript do quiz), a página responde apenas com o fragmento HTML
 * dos resultados (cards), que é injetado dinamicamente na mesma página
 * pelo front-end — sem recarregar e sem perder header/footer.
 */

require_once "conexao.php";

/**
 * Escapa texto para saída segura em HTML.
 */
function e($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/**
 * Monta o HTML de um card de pet, reaproveitando exatamente as mesmas
 * classes usadas em adocao.html (.pet-card, .card-img-wrapper, .card-body...).
 */
function renderCardAnimal(array $animal): string {
    $nome      = e($animal['nome'] ?? 'Sem nome');
    $especie   = e($animal['especie'] ?? '');
    $raca      = e($animal['raca'] ?? 'Raça não informada');
    $idade     = $animal['idade'] !== null ? (int) $animal['idade'] . ' ano(s)' : 'Idade não informada';
    $sexo      = e($animal['sexo'] ?? '');
    $porte     = e($animal['porte'] ?? '');
    $descricao = e($animal['descricao'] ?? '');
    $abrigo    = !empty($animal['abrigo']) ? e($animal['abrigo']) : 'Localização não informada';

    // A tabela animais_adocao não possui campo de foto: usa imagem padrão.
    $foto = !empty($animal['ds_img']) ? '../uploads/' . e($animal['ds_img']) : '../static/pet-placeholder.jpg';

    $tagClasse = ($especie === 'Gato') ? 'tag-gato' : 'tag-cachorro';

    return <<<HTML
        <article class="pet-card">
          <div class="card-img-wrapper">
            <img src="{$foto}" alt="{$nome}, {$raca}" loading="lazy">
          </div>
          <div class="card-body">
            <h3 class="pet-nome">{$nome}</h3>
            <p class="pet-info"><span class="tag-tipo {$tagClasse}">{$especie}</span> · {$porte} · {$idade}</p>
            <p class="pet-info">{$raca} · {$sexo}</p>
            <p class="pet-localizacao">
              <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              {$abrigo}
            </p>
            <p class="pet-descricao-quiz">{$descricao}</p>
          </div>
        </article>
        HTML;
}

/**
 * Calcula o perfil do usuário (Gato, Cachorro ou Ambos) a partir das
 * respostas do quiz e busca no banco os animais disponíveis compatíveis.
 * Retorna o fragmento HTML dos resultados.
 */
function processarQuiz(PDO $pdo, array $respostas): string {

    $rotina       = $respostas[0] ?? '';
    $frequencia   = $respostas[1] ?? '';
    $investimento = $respostas[2] ?? '';
    $motivacao    = $respostas[3] ?? '';
    $atividade    = $respostas[4] ?? '';
    $moradia      = $respostas[5] ?? '';
    $tempoLivre   = $respostas[6] ?? '';
    $sexo         = $respostas[7] ?? '';

    $pontosGato     = 0;
    $pontosCachorro = 0;

    if ($rotina === "Muito corrida") {
        $pontosGato += 3;
    } elseif ($rotina === "Moderada") {
        $pontosGato += 1;
        $pontosCachorro += 1;
    } else {
        $pontosCachorro += 3;
    }

    if ($frequencia === "Todos os dias") {
        $pontosGato += 3;
    } elseif ($frequencia === "Algumas vezes por semana") {
        $pontosGato += 1;
        $pontosCachorro += 1;
    } else {
        $pontosCachorro += 3;
    }

    if ($investimento === "Até R\$100") {
        $pontosGato += 2;
    } elseif ($investimento === "Entre R\$100 e R\$300") {
        $pontosGato += 1;
        $pontosCachorro += 1;
    } else {
        $pontosCachorro += 2;
    }

    if ($motivacao === "Ter companhia no dia a dia") {
        $pontosGato += 2;
        $pontosCachorro += 1;
    } elseif ($motivacao === "Compartilhar momentos e atividades") {
        $pontosCachorro += 3;
    } else {
        $pontosGato += 2;
        $pontosCachorro += 2;
    }

    if ($atividade === "Relaxar e aproveitar a companhia em casa") {
        $pontosGato += 3;
    } elseif ($atividade === "Um pouco de tudo") {
        $pontosGato += 1;
        $pontosCachorro += 1;
    } else {
        $pontosCachorro += 3;
    }

    if ($pontosGato > $pontosCachorro) {
        $perfil = "Gato";
    } elseif ($pontosCachorro > $pontosGato) {
        $perfil = "Cachorro";
    } else {
        $perfil = "Ambos";
    }

        // ── Monta a consulta com PDO + prepared statements ──────────────
        // Usamos uma subquery correlacionada em vez de JOIN com foto_animal
        // porque um mesmo animal pode ter várias fotos cadastradas: um JOIN
        // normal traria uma linha (e um card) para cada foto. A subquery
        // pega só a foto de menor id_foto (a primeira registrada) de cada
        // animal, garantindo uma única linha por animal.
        $sql = "
        SELECT
            a.*,
            (
                SELECT f.ds_img
                FROM foto_animal f
                WHERE f.id_animal = a.id_animal
                ORDER BY f.id_foto ASC
                LIMIT 1
            ) AS ds_img
        FROM animais_adocao a
        WHERE a.status_adocao = 'Disponível'
        ";

        $params = [];

    if ($perfil !== "Ambos") {
        $sql .= " AND especie = :especie";
        $params[':especie'] = $perfil;
    }

    if ($moradia === "Moro em um espaço pequeno") {
        $sql .= " AND porte = 'Pequeno'";
    } elseif ($moradia === "Moro em um espaço médio") {
        $sql .= " AND porte IN ('Pequeno','Médio')";
    }

    if ($tempoLivre === "Menos de 1 hora") {
        $sql .= " AND idade >= 7";
    } elseif ($tempoLivre === "Entre 1 e 3 horas") {
        $sql .= " AND idade BETWEEN 2 AND 6";
    } else {
        $sql .= " AND idade <= 2";
    }

    if ($sexo !== "Não tenho preferência") {
        $sql .= " AND sexo = :sexo";
        $params[':sexo'] = $sexo;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $animais = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── Monta o HTML do resultado ────────────────────────────────────
    if ($perfil === "Gato") {
        $tituloResultado = '<i class="fa-solid fa-cat"></i> Seu perfil combina mais com gatos';
    } elseif ($perfil === "Cachorro") {
        $tituloResultado = '<i class="fa-solid fa-dog"></i> Seu perfil combina mais com cães';
    } else {
        $tituloResultado = '<i class="fa-solid fa-dog"></i> Seu perfil combina com cães e gatos <i class="fa-solid fa-cat"></i>';
    }

    ob_start();
    ?>
    <div class="resultado-quiz">
        <h1 class="titulo-resultado"><?= $tituloResultado ?></h1>
        <p class="subtitulo-resultado">Confira os animais disponíveis que combinam com seu perfil.</p>

        <?php if (empty($animais)): ?>
            <div class="sem-resultados-quiz">
                <div class="sem-resultados-icone" aria-hidden="true">🐾</div>
                <h2>Nenhum animal encontrado</h2>
                <p>Tente refazer o quiz alterando algumas respostas.</p>
            </div>
        <?php else: ?>
            <div class="pets-container">
                <?php foreach ($animais as $animal): ?>
                    <?= renderCardAnimal($animal) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="acoes-resultado-quiz">
            <button class="botao-secundario-quiz" id="btn-refazer-quiz">
                <i class="fa-solid fa-rotate-right"></i>
                Refazer Quiz
            </button>
            <a href="adocao.html" class="botao-primario-quiz">
                <i class="fa-solid fa-paw"></i>
                Ver todos os pets
            </a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// ══════════════════════════════════════════════════════════════════
// Requisição AJAX (fetch do JS do quiz): processa e devolve só o
// fragmento HTML dos resultados, sem o restante da página.
// ══════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $corpo     = file_get_contents("php://input");
    $respostas = json_decode($corpo, true);

    if (!is_array($respostas) || count($respostas) < 8) {
        http_response_code(400);
        echo '<p class="erro-quiz">Não foi possível processar suas respostas. Tente novamente.</p>';
        exit;
    }

    echo processarQuiz($pdo, $respostas);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Quiz — Descubra o pet ideal para você | Pet Vida</title>
    <meta name="description" content="Responda algumas perguntas rápidas e descubra qual pet combina mais com a sua rotina e seu estilo de vida.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,400;0,600;1,400&family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="./css/adocao.css">
    <style>
        /* ── Estilos exclusivos do Pet Quiz, usando as mesmas variáveis
           de cor/tipografia definidas em adocao.css para manter a
           identidade visual do site. ── */

        .quiz-main {
            max-width: 720px;
            margin: 0 auto;
            padding: 48px 24px 80px;
        }

        .titulo-quiz {
            font-family: 'Fraunces', serif;
            font-size: 2rem;
            color: var(--verde-esc);
            text-align: center;
        }

        .subtitulo-quiz {
            text-align: center;
            color: var(--texto-leve);
            margin-top: 8px;
            margin-bottom: 32px;
        }

        .quiz-card {
            background: var(--branco);
            border: 1px solid var(--bege-esc);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow);
            padding: 32px 28px;
        }

        .topo {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 24px;
        }

        #contador {
            font-size: .8rem;
            font-weight: 600;
            color: var(--laranja);
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .linha-progresso {
            display: flex;
            gap: 6px;
        }

        .circulo {
            flex: 1;
            height: 6px;
            border-radius: 999px;
            background: var(--bege-esc);
            transition: var(--transition);
        }

        .circulo.ativo { background: var(--laranja); }

        #pergunta {
            font-family: 'Fraunces', serif;
            font-size: 1.3rem;
            color: var(--verde-esc);
            margin-bottom: 20px;
        }

        .opcoes {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .opcoes.shake { animation: shakeOpcoes .35s ease; }
        @keyframes shakeOpcoes {
            0%, 100% { transform: translateX(0); }
            25%      { transform: translateX(-6px); }
            75%      { transform: translateX(6px); }
        }

        .opcao {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border: 1.5px solid var(--bege-esc);
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
        }

        .opcao i { color: var(--verde); font-size: 1.1rem; width: 22px; text-align: center; }

        .opcao:hover { border-color: var(--laranja); background: var(--bege); }

        .opcao.selecionada {
            border-color: var(--laranja);
            background: rgba(224,123,57,.08);
        }

        .opcao.selecionada i { color: var(--laranja); }

        .opcao p { font-size: .95rem; color: var(--texto); }

        .botoes {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 28px;
        }

        #voltar {
            background: none;
            border: none;
            color: var(--cinza);
            font-weight: 600;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-size: .9rem;
        }

        #voltar:hover { color: var(--verde-esc); }

        #btnResultado {
            background: linear-gradient(135deg, var(--laranja) 0%, var(--laranja-esc) 100%);
            color: var(--branco);
            border: none;
            border-radius: var(--radius);
            padding: 12px 26px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }

        #btnResultado:hover { transform: translateY(-2px); box-shadow: var(--shadow); }

        /* ── Carregando ── */
        .carregando-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 80px 20px;
            gap: 16px;
        }

        .carregando-spinner {
            width: 48px;
            height: 48px;
            border: 4px solid var(--bege-esc);
            border-top-color: var(--laranja);
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .carregando-wrapper p { color: var(--texto-leve); }

        /* ── Resultados ── */
        .resultado-quiz { max-width: 1280px; margin: 0 auto; }

        .titulo-resultado {
            font-family: 'Fraunces', serif;
            font-size: 1.8rem;
            color: var(--verde-esc);
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .titulo-resultado i { color: var(--laranja); }

        .subtitulo-resultado {
            text-align: center;
            color: var(--texto-leve);
            margin-top: 8px;
            margin-bottom: 32px;
        }

        .pet-descricao-quiz {
            font-size: .82rem;
            color: var(--texto-leve);
            margin-top: 6px;
            line-height: 1.5;
        }

        .sem-resultados-quiz {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
            text-align: center;
            gap: 12px;
        }

        .sem-resultados-quiz .sem-resultados-icone { font-size: 3rem; opacity: .3; }

        .sem-resultados-quiz h2 {
            font-family: 'Fraunces', serif;
            font-size: 1.4rem;
            color: var(--verde-esc);
        }

        .sem-resultados-quiz p { color: var(--cinza); }

        .acoes-resultado-quiz {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .botao-primario-quiz,
        .botao-secundario-quiz {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 26px;
            border-radius: var(--radius);
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            font-size: .9rem;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
            border: none;
        }

        .botao-primario-quiz {
            background: linear-gradient(135deg, var(--laranja) 0%, var(--laranja-esc) 100%);
            color: var(--branco);
        }

        .botao-primario-quiz:hover { transform: translateY(-2px); box-shadow: var(--shadow); }

        .botao-secundario-quiz {
            background: var(--bege-esc);
            color: var(--verde-esc);
        }

        .botao-secundario-quiz:hover { background: var(--cinza-claro); }

        .erro-quiz {
            text-align: center;
            color: var(--laranja-esc);
            padding: 40px 20px;
        }

        #area-resultado { margin-top: 32px; }

        @media (max-width: 600px) {
            .quiz-card { padding: 24px 18px; }
        }
    </style>
</head>
<body>

    <!-- ===== HEADER (mesmo header do site, para manter consistência) ===== -->
    <header role="banner">
        <div class="header-inner">
            <a href="adocao.php" class="logo" aria-label="Página inicial">
                <img src="./img/logo_petvida.png" alt="Pet Vida" class="logo-img">
                <span class="logo-text">Pet <em>Vida</em></span>
            </a>

            <div class="nav-busca-wrapper" aria-hidden="true"></div>

            <div class="nav-acoes">
                <a href="../html/adocao.html" class="btn-cta" aria-label="Ver todos os pets">
                    Ver todos os pets
                </a>
            </div>
        </div>
    </header>

    <main class="quiz-main" role="main">

        <div id="area-quiz">
            <h1 class="titulo-quiz">Pet Quiz</h1>
            <p class="subtitulo-quiz">Descubra quais pets combinam com seu perfil</p>

            <section class="quiz-card" aria-label="Formulário do Pet Quiz">
                <div class="topo">
                    <span id="contador">Pergunta 1 de 8</span>
                    <div class="linha-progresso" id="linhaProgresso"></div>
                </div>

                <h2 id="pergunta"></h2>

                <div class="opcoes" id="opcoes"></div>

                <div class="botoes">
                    <button id="voltar">← Voltar</button>
                    <button id="btnResultado" onclick="finalizarQuiz()" style="display:none;">
                        Dar Match
                        <i class="fa-solid fa-heart"></i>
                    </button>
                </div>
            </section>
        </div>

        <div id="area-resultado" aria-live="polite"></div>

    </main>

    <!-- ===== FOOTER (idêntico ao de adocao.html) ===== -->
    <footer role="contentinfo">
        <div class="footer-inner">
            <div class="footer-marca">
                <img src="./img/logo_petvida.png" alt="Pet Vida" class="footer-logo-img">
                <p class="footer-slogan">Adote amor.<br><em>Mude uma vida.</em></p>
            </div>
            <div class="footer-links">
                <h4>Institucional</h4>
                <a href="adocao.html">Sobre nós</a>
                <a href="adocao.html">Como adotar</a>
                <a href="adocao.html">Política de privacidade</a>
            </div>
            <div class="footer-links">
                <h4>Ajuda</h4>
                <a href="adocao.html">Dúvidas frequentes</a>
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
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2025 Adote com Amor · Todos os direitos reservados</p>
        </div>
    </footer>

    <script>
        const perguntas = [
            { pergunta: "Como é sua rotina durante a semana?", opcoes: ["Muito corrida", "Moderada", "Tranquila"] },
            { pergunta: "Com que frequência você sai de casa?", opcoes: ["Todos os dias", "Algumas vezes por semana", "Raramente"] },
            { pergunta: "Quanto pretende investir mensalmente nos cuidados do pet?", opcoes: ["Até R$100", "Entre R$100 e R$300", "Mais de R$300"] },
            { pergunta: "O que te motiva a querer um pet?", opcoes: ["Ter companhia no dia a dia", "Compartilhar momentos e atividades", "Cuidar e oferecer um lar"] },
            { pergunta: "Quais atividades você imagina fazer com seu pet?", opcoes: ["Relaxar e aproveitar a companhia em casa", "Um pouco de tudo", "Brincadeiras e atividades ao ar livre"] },
            { pergunta: "Qual é seu tipo de moradia?", opcoes: ["Moro em um espaço pequeno", "Moro em um espaço médio", "Moro em um espaço grande"] },
            { pergunta: "Quanto tempo livre você possui diariamente para dedicar ao pet?", opcoes: ["Menos de 1 hora", "Entre 1 e 3 horas", "Mais de 3 horas"] },
            { pergunta: "Você possui alguma preferência para o sexo do pet?", opcoes: ["Não tenho preferência", "Macho", "Fêmea"] }
        ];

        const imagens = {
            "Muito corrida": "fa-solid fa-person-running",
            "Moderada": "fa-solid fa-calendar-days",
            "Tranquila": "fa-solid fa-mug-hot",

            "Todos os dias": "fa-solid fa-car",
            "Algumas vezes por semana": "fa-solid fa-store",
            "Raramente": "fa-solid fa-bed",

            "Até R$100": "fa-solid fa-coins",
            "Entre R$100 e R$300": "fa-solid fa-wallet",
            "Mais de R$300": "fa-solid fa-sack-dollar",

            "Ter companhia no dia a dia": "fa-solid fa-heart",
            "Compartilhar momentos e atividades": "fa-solid fa-dog",
            "Cuidar e oferecer um lar": "fa-solid fa-hand-holding-heart",

            "Relaxar e aproveitar a companhia em casa": "fa-solid fa-couch",
            "Um pouco de tudo": "fa-solid fa-scale-balanced",
            "Brincadeiras e atividades ao ar livre": "fa-solid fa-bone",

            "Moro em um espaço pequeno": "fa-solid fa-building",
            "Moro em um espaço médio": "fa-solid fa-city",
            "Moro em um espaço grande": "fa-solid fa-house",

            "Menos de 1 hora": "fa-regular fa-clock",
            "Entre 1 e 3 horas": "fa-regular fa-clock",
            "Mais de 3 horas": "fa-regular fa-clock",

            "Não tenho preferência": "fa-solid fa-paw",
            "Macho": "fa-solid fa-mars",
            "Fêmea": "fa-solid fa-venus"
        };

        let perguntaAtual = 0;
        let respostas = [];

        carregarPergunta();

        function carregarPergunta() {
            const pergunta = perguntas[perguntaAtual];

            document.getElementById("contador").innerText =
                `Pergunta ${perguntaAtual + 1} de ${perguntas.length}`;

            document.getElementById("pergunta").innerText = pergunta.pergunta;

            atualizarProgresso();

            let html = "";
            pergunta.opcoes.forEach(opcao => {
                const selecionada = respostas[perguntaAtual] == opcao ? "selecionada" : "";
                html += `
                    <div class="opcao ${selecionada}" onclick="selecionarOpcao(this,'${opcao}')">
                        <i class="${imagens[opcao]}"></i>
                        <p>${opcao}</p>
                    </div>
                `;
            });

            document.getElementById("opcoes").innerHTML = html;

            document.getElementById("voltar").onclick =
                (perguntaAtual === 0) ? voltarInicio : voltarPergunta;

            const btnResultado = document.getElementById("btnResultado");
            btnResultado.style.display =
                (perguntaAtual === perguntas.length - 1 && respostas[perguntaAtual]) ? "block" : "none";
        }

        function atualizarProgresso() {
            let html = "";
            for (let i = 0; i < perguntas.length; i++) {
                html += i <= perguntaAtual ? '<div class="circulo ativo"></div>' : '<div class="circulo"></div>';
            }
            document.getElementById("linhaProgresso").innerHTML = html;
        }

        function selecionarOpcao(elemento, resposta) {
            document.querySelectorAll(".opcao").forEach(opcao => opcao.classList.remove("selecionada"));
            elemento.classList.add("selecionada");
            respostas[perguntaAtual] = resposta;

            if (perguntaAtual === perguntas.length - 1) {
                document.getElementById("btnResultado").style.display = "block";
                return;
            }

            setTimeout(() => {
                if (perguntaAtual < perguntas.length - 1) {
                    perguntaAtual++;
                }
                carregarPergunta();
            }, 300);
        }

        function voltarPergunta() {
            perguntaAtual--;
            carregarPergunta();
        }

        function voltarInicio() {
            window.location.href = "../html/adocao.html";
        }

        function finalizarQuiz() {
            if (!respostas[perguntaAtual]) return;

            const areaQuiz      = document.getElementById("area-quiz");
            const areaResultado = document.getElementById("area-resultado");

            areaQuiz.style.display = "none";
            areaResultado.innerHTML = `
                <div class="carregando-wrapper">
                    <div class="carregando-spinner"></div>
                    <p>Buscando os melhores pets para você...</p>
                </div>
            `;

            fetch("quiz.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(respostas)
            })
            .then(r => r.text())
            .then(html => {
                areaResultado.innerHTML = html;
                areaResultado.scrollIntoView({ behavior: "smooth", block: "start" });

                const btnRefazer = document.getElementById("btn-refazer-quiz");
                if (btnRefazer) {
                    btnRefazer.addEventListener("click", () => window.location.reload());
                }
            })
            .catch(() => {
                areaResultado.innerHTML = `<p class="erro-quiz">Ocorreu um erro ao buscar os pets. Tente novamente.</p>`;
            });
        }
    </script>

</body>
</html>