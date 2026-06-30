<?php
// ============================================================
// QUIZ.PHP - ESTRUTURA UNIFICADA CORRIGIDA PARA PDO
// ============================================================
include("conexao.php");

// Bloco 1: RESPOSTA AO FETCH DO JAVASCRIPT (Processamento do resultado)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $input = file_get_contents("php://input");
    $dados = json_decode($input, true);

    if (!$dados || !is_array($dados) || count($dados) < 8) {
        http_response_code(400);
        echo "<p class='quiz-subtitulo'>Dados insuficientes para calcular o resultado.</p>";
        exit;
    }

    // Respostas vindas do JS
    $rotina       = $dados[0] ?? '';
    $frequencia   = $dados[1] ?? '';
    $investimento = $dados[2] ?? '';
    $motivacao    = $dados[3] ?? '';
    $atividade    = $dados[4] ?? '';
    $moradia      = $dados[5] ?? '';
    $tempoLivre   = $dados[6] ?? '';
    $sexo         = $dados[7] ?? '';

    $pontosGato = 0;
    $pontosCachorro = 0;

    // Lógica de pontuação original
    if ($rotina == "Muito corrida")      $pontosGato += 3;
    elseif ($rotina == "Moderada")       { $pontosGato += 1; $pontosCachorro += 1; }
    elseif ($rotina == "Tranquila")      $pontosCachorro += 2;

    if ($frequencia == "Todos os dias")              $pontosCachorro += 3;
    elseif ($frequencia == "Algumas vezes por semana") { $pontosGato += 1; $pontosCachorro += 1; }
    elseif ($frequencia == "Raramente")              $pontosGato += 3;

    if ($investimento == "Até R$100")            $pontosGato += 2;
    elseif ($investimento == "Entre R$100 e R$300") { $pontosGato += 1; $pontosCachorro += 2; }
    elseif ($investimento == "Mais de R$300")       $pontosCachorro += 3;

    if ($motivacao == "Ter companhia no dia a dia")       { $pontosGato += 2; $pontosCachorro += 1; }
    elseif ($motivacao == "Compartilhar momentos")       $pontosCachorro += 3;
    elseif ($motivacao == "Cuidar e oferecer um lar")   { $pontosGato += 2; $pontosCachorro += 2; }

    if ($atividade == "Relaxar em casa")                 $pontosGato += 3;
    elseif ($atividade == "Um pouco de tudo")            { $pontosGato += 1; $pontosCachorro += 2; }
    elseif ($atividade == "Atividades ao ar livre")      $pontosCachorro += 3;

    if ($moradia == "Moro em um espaço pequeno")       $pontosGato += 3;
    elseif ($moradia == "Moro em um espaço médio")       { $pontosGato += 1; $pontosCachorro += 2; }
    elseif ($moradia == "Moro em um espaço grande")      $pontosCachorro += 3;

    if ($tempoLivre == "Até 1 hora")       $pontosGato += 3;
    elseif ($tempoLivre == "De 1 a 3 horas") { $pontosGato += 1; $pontosCachorro += 2; }
    elseif ($tempoLivre == "Mais de 3 horas") $pontosCachorro += 3;

    $especieIdeal = ($pontosGato >= $pontosCachorro) ? 'Gato' : 'Cachorro';

    // Query adaptada perfeitamente para o padrão PDO do seu projeto
    $sql = "SELECT a.*, f.ds_img FROM animais_adocao a 
            LEFT JOIN foto_animal f ON a.id_animal = f.id_animal 
            WHERE a.status_adocao = 'Disponível' AND a.especie = :especie";

    $parametros = [':especie' => $especieIdeal];

    if ($sexo !== "Não tenho preferência") {
        $sql .= " AND a.sexo = :sexo";
        $parametros[':sexo'] = $sexo;
    }

    $sql .= " ORDER BY RAND() LIMIT 4";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);
        $animais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        http_response_code(500);
        echo "<p class='quiz-subtitulo'>Erro ao consultar o banco de dados.</p>";
        exit;
    }
    ?>

    <div class="resultado-header" style="text-align: center; margin-bottom: 30px; width: 100%;">
        <h1 class="quiz-titulo" style="margin-bottom: 10px;">Seus <em>Matches</em> Perfeitos! 🐾</h1>
        <p class="quiz-subtitulo">Com base na sua rotina, o pet ideal para si é um <strong><?= $especieIdeal ?></strong>!</p>
    </div>

    <?php if (count($animais) == 0): ?>
        <div style="text-align: center; color: var(--texto-leve); padding: 40px; background: var(--branco); border-radius: var(--radius); border: 1px solid var(--bege-esc); width: 100%;">
            <p>Nenhum <?= strtolower($especieIdeal) ?> correspondente encontrado no momento. Mas não desista de adotar!</p>
        </div>
    <?php else: ?>
        <div class="cards-resultado">
            <?php foreach ($animais as $animal): ?>
                <div class="card-resultado">
                    <?php 
                        $fotoCaminho = !empty($animal['ds_img']) ? 'uploads/' . $animal['ds_img'] : 'uploads/not_image.png';
                    ?>
                    <img src="<?= $fotoCaminho ?>" alt="Foto de <?= htmlspecialchars($animal['nome']) ?>">
                    <div class="card-body">
                        <h2><?= htmlspecialchars($animal['nome']) ?></h2>
                        <p><strong>Raça:</strong> <?= htmlspecialchars($animal['raca']) ?></p>
                        <p><strong>Idade:</strong> <?= htmlspecialchars($animal['idade']) ?></p>
                        <p><strong>Sexo:</strong> <?= htmlspecialchars($animal['sexo']) ?></p>
                        <p><strong>Porte:</strong> <?= htmlspecialchars($animal['porte']) ?></p>
                    </div>
                    <div style="padding: 0 20px 20px 20px; margin-top: auto;">
                        <a href="adocao.php?id=<?= $animal['id_animal'] ?>" class="botao" style="width: 100%; justify-content: center; text-decoration: none; display: inline-flex;">
                            Quero Conhecer
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="acoes-resultado">
        <a href="quiz.php" class="botao" style="text-decoration: none;">
            <i class="fa-solid fa-rotate-right"></i> Refazer Quiz
        </a>
        <a href="adocao.php" class="botao botao--laranja" style="text-decoration: none;">
            <i class="fa-solid fa-paw"></i> Ver todos os pets
        </a>
    </div>
    <?php
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Quiz - Pet Vida</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/adocao.css">
    <link rel="stylesheet" href="css/quiz.css">
</head>
<body>

<div class="quiz-wrapper">
    <div class="quiz-card" id="quizConteudoPrincipal">
        <h1 class="quiz-titulo">Pet <em>Quiz</em></h1>
        <p class="quiz-subtitulo">Descubra quais pets combinam com seu perfil!</p>

        <div class="topo-quiz">
            <span id="contador">Pergunta 1 de 8</span>
            <div class="linha-progresso" id="linhaProgresso"></div>
        </div>

        <h2 id="pergunta"></h2>

        <div class="opcoes-quiz" id="opcoes"></div>

        <div class="botoes-quiz">
            <button id="voltar">Voltar</button>
            <button id="btnResultado">Dar Match <i class="fa-solid fa-heart"></i></button>
        </div>
    </div>
</div>

<div class="carregando-wrapper-total" id="loadingScreen" style="display:none;">
    <svg class="pata-svg-gigante" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <ellipse class="dedo" id="d1" cx="13.5" cy="44" rx="13.5" ry="17.5" />
        <ellipse class="dedo" id="d2" cx="34.5" cy="17.5" rx="13.5" ry="17.5" />
        <ellipse class="dedo" id="d3" cx="65.5" cy="17.5" rx="13.5" ry="17.5" />
        <ellipse class="dedo" id="d4" cx="86.5" cy="44" rx="13.5" ry="17.5" />
        <path class="almofada-central" d="M 50,40.5 
                 C 34.5,40.5 27.5,53.5 27.5,60.5
                 C 27.5,67 15,75.5 15,86
                 C 15,96 32,100 50,93.5
                 C 68,100 85,96 85,86
                 C 85,75.5 72.5,67 72.5,60.5
                 C 72.5,53.5 65.5,40.5 50,40.5 Z" />
    </svg>
</div>

<script src="js/quiz.js"></script>
</body>
</html>