<?php
// ============================================================
// QUIZ - PROCESSADOR DE RESULTADOS
// ============================================================
include("conexao.php");

// Se não for POST, redireciona para o quiz
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../html/quiz.html");
    exit;
}

$input = file_get_contents("php://input");
$dados = json_decode($input, true);

if (!$dados || !is_array($dados) || count($dados) < 8) {
    http_response_code(400);
    echo "Dados insuficientes.";
    exit;
}

// Atribuição segura
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

// Lógica de pontuação (igual à original)
if ($rotina == "Muito corrida")      $pontosGato += 3;
elseif ($rotina == "Moderada")      { $pontosGato += 1; $pontosCachorro += 1; }
else                                 $pontosCachorro += 3;

if ($frequencia == "Todos os dias")  $pontosGato += 3;
elseif ($frequencia == "Algumas vezes por semana") { $pontosGato += 1; $pontosCachorro += 1; }
else                                 $pontosCachorro += 3;

if ($investimento == "Até R$100")    $pontosGato += 2;
elseif ($investimento == "Entre R$100 e R$300") { $pontosGato += 1; $pontosCachorro += 1; }
else                                 $pontosCachorro += 2;

if ($motivacao == "Ter companhia no dia a dia")      { $pontosGato += 2; $pontosCachorro += 1; }
elseif ($motivacao == "Compartilhar momentos e atividades") $pontosCachorro += 3;
else                                      { $pontosGato += 2; $pontosCachorro += 2; }

if ($atividade == "Relaxar e aproveitar a companhia em casa") $pontosGato += 3;
elseif ($atividade == "Um pouco de tudo")            { $pontosGato += 1; $pontosCachorro += 1; }
else                                                 $pontosCachorro += 3;

if ($pontosGato > $pontosCachorro)       $perfil = "Gato";
elseif ($pontosCachorro > $pontosGato)   $perfil = "Cachorro";
else                                     $perfil = "Ambos";

// Montagem da consulta SQL
$sql = "SELECT * FROM animais_adocao WHERE status_adocao='Disponível'";
if ($perfil != "Ambos") {
    $sql .= " AND especie='$perfil'";
}
if ($moradia == "Moro em um espaço pequeno") {
    $sql .= " AND porte='Pequeno'";
} elseif ($moradia == "Moro em um espaço médio") {
    $sql .= " AND porte IN('Pequeno','Médio')";
}
if ($tempoLivre == "Menos de 1 hora") {
    $sql .= " AND idade >= 7";
} elseif ($tempoLivre == "Entre 1 e 3 horas") {
    $sql .= " AND idade BETWEEN 2 AND 6";
} else {
    $sql .= " AND idade <= 2";
}
if ($sexo != "Não tenho preferência") {
    $sql .= " AND sexo='$sexo'";
}

$resultado = mysqli_query($conexao, $sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado do Quiz - Pet Vida</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../css/adocao.css">
    <link rel="stylesheet" href="../css/quiz.css">
</head>
<body>
    <div class="resultado-wrapper">
        <?php
        if ($perfil == "Gato") {
            echo '<h1 class="titulo-resultado"><i class="fa-solid fa-cat"></i> Seu perfil combina mais com gatos</h1>';
        } elseif ($perfil == "Cachorro") {
            echo '<h1 class="titulo-resultado"><i class="fa-solid fa-dog"></i> Seu perfil combina mais com cães</h1>';
        } else {
            echo '<h1 class="titulo-resultado"><i class="fa-solid fa-dog"></i> Seu perfil combina com cães e gatos <i class="fa-solid fa-cat"></i></h1>';
        }
        ?>
        <p class="subtitulo-resultado">Confira os animais disponíveis que combinam com seu perfil.</p>

        <?php if (mysqli_num_rows($resultado) == 0): ?>
            <div class="sem-resultado">
                <h2>Nenhum animal encontrado</h2>
                <p>Tente refazer o quiz alterando algumas respostas.</p>
            </div>
        <?php else: ?>
            <div class="cards-resultado">
                <?php while ($animal = mysqli_fetch_assoc($resultado)): ?>
                    <div class="card-resultado">
                        <img src="imagens/<?= $animal['foto'] ?>" alt="<?= $animal['nome'] ?>">
                        <div class="card-body">
                            <h2><?= $animal['nome'] ?></h2>
                            <p><strong>Espécie:</strong> <?= $animal['especie'] ?></p>
                            <p><strong>Raça:</strong> <?= $animal['raca'] ?></p>
                            <p><strong>Idade:</strong> <?= $animal['idade'] ?> anos</p>
                            <p><strong>Sexo:</strong> <?= $animal['sexo'] ?></p>
                            <p><strong>Porte:</strong> <?= $animal['porte'] ?></p>
                            <p><?= $animal['descricao'] ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <div class="acoes-resultado">
            <button class="botao" onclick="window.location.href='quiz.html'">
                <i class="fa-solid fa-rotate-right"></i> Refazer Quiz
            </button>
            <a href="../html/adocao.html" class="botao botao--laranja">
                <i class="fa-solid fa-paw"></i> Ver todos os pets
            </a>
        </div>
    </div>
</body>
</html>
<?php
mysqli_close($conexao);
?>