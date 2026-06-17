<?php

include("conexao.php");

// [ALTERADO #4/#6] Suporta tanto o formato original (JSON array via fetch)
// quanto um envio de formulário HTML tradicional (POST com nomes de campo),
// sem alterar a lógica de pontuação ou a consulta SQL abaixo.
$jsonInput = json_decode(
    file_get_contents("php://input"),
    true
);

if (is_array($jsonInput) && isset($jsonInput[0])) {
    // Formato original: array JSON posicional
    $dados = $jsonInput;
} else {
    // Formato de formulário HTML: monta o array posicional a partir do $_POST
    $dados = [
        $_POST['rotina']       ?? null,
        $_POST['frequencia']   ?? null,
        $_POST['investimento'] ?? null,
        $_POST['motivacao']    ?? null,
        $_POST['atividade']    ?? null,
        $_POST['moradia']      ?? null,
        $_POST['tempoLivre']   ?? null,
        $_POST['sexo']         ?? null,
    ];
}

$rotina       = $dados[0];
$frequencia   = $dados[1];
$investimento = $dados[2];
$motivacao    = $dados[3];
$atividade    = $dados[4];

$moradia      = $dados[5];
$tempoLivre   = $dados[6];
$sexo         = $dados[7];

$pontosGato = 0;
$pontosCachorro = 0;


if($rotina == "Muito corrida"){

    $pontosGato += 3;
}
elseif($rotina == "Moderada"){
    $pontosGato += 1;
    $pontosCachorro += 1;
}
else{
    $pontosCachorro += 3;
}

if($frequencia == "Todos os dias"){
    $pontosGato += 3;
}
elseif($frequencia == "Algumas vezes por semana"){
    $pontosGato += 1;
    $pontosCachorro += 1;
}
else{
    $pontosCachorro += 3;
}

if($investimento == "Até R$100"){
    $pontosGato += 2;
}
elseif($investimento == "Entre R$100 e R$300"){
    $pontosGato += 1;
    $pontosCachorro += 1;
}
else{
    $pontosCachorro += 2;
}

if($motivacao == "Ter companhia no dia a dia"){
    $pontosGato += 2;
    $pontosCachorro += 1;
}
elseif($motivacao == "Compartilhar momentos e atividades"){
    $pontosCachorro += 3;
}
else{
    $pontosGato += 2;
    $pontosCachorro += 2;
}

if($atividade == "Relaxar e aproveitar a companhia em casa"){
    $pontosGato += 3;
}
elseif($atividade == "Um pouco de tudo"){
    $pontosGato += 1;
    $pontosCachorro += 1;
}
else{
    $pontosCachorro += 3;
}


if($pontosGato > $pontosCachorro){
    $perfil = "Gato";
}
elseif($pontosCachorro > $pontosGato){
    $perfil = "Cachorro";
}
else{
    $perfil = "Ambos";
}




$sql = "
SELECT *
FROM animais_adocao
WHERE status_adocao='Disponível'

";

if($perfil != "Ambos"){
    $sql .= "
    AND especie='$perfil'
    ";
}


if($moradia == "Moro em um espaço pequeno"){
    $sql .= "
    AND porte='Pequeno'
    ";
}
elseif($moradia == "Moro em um espaço médio"){
    $sql .= "
    AND porte IN('Pequeno','Médio')
    ";
}

if($tempoLivre == "Menos de 1 hora"){
    $sql .= "
    AND idade >= 7
    ";
}
elseif($tempoLivre == "Entre 1 e 3 horas"){
    $sql .= "
    AND idade BETWEEN 2 AND 6
    ";
}
else{
    $sql .= "
    AND idade <= 2
    ";
}

if($sexo != "Não tenho preferência"){
    $sql .= "
    AND sexo='$sexo'
    ";
}


$resultado = mysqli_query(
    $conexao,
    $sql
);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Resultado do Pet Quiz | Pet Vida</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,400;0,600;1,400&family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<!-- [ALTERADO #5] adocao.css é importado dentro de style_buscar.css para que os cards fiquem idênticos -->
<link rel="stylesheet" href="../css/style_buscar.css">
</head>
<body>

<!-- ===== HEADER SIMPLES (resultado do quiz) ===== -->
<header class="resultado-header" role="banner">
  <div class="resultado-header-inner">
    <a href="../adocao.html" class="logo" aria-label="Página inicial">
      <span class="logo-text">Pet <em>Vida</em></span>
    </a>
  </div>
</header>

<main class="resultado-main">

<?php
// [ALTERADO #5] Título sem emojis/ícones — apenas texto
if($perfil == "Gato"){

    echo "
    <h1 class='titulo'>
       Seu perfil combina mais com gatos
    </h1>
    ";

}
elseif($perfil == "Cachorro"){

    echo "
    <h1 class='titulo'>
        Seu perfil combina mais com cães
    </h1>
    ";

}
else{

    echo "
    <h1 class='titulo'>
       Seu perfil combina com cães e gatos
    </h1>
    ";

}
?>

<p class="subtitulo">
Confira os animais disponíveis que combinam com seu perfil.
</p>

<?php

if(mysqli_num_rows($resultado) == 0){

    echo "

    <div class='sem-resultado'>
        <h2>
            Nenhum animal encontrado
        </h2>

        <p>
            Tente refazer o quiz alterando algumas respostas.
        </p>
    </div>

    ";

}else{
/* ==========================================================================
        [ALTERADO #5] Cards agora usam o MESMO HTML/classes de adocao.html
        (.pet-card, .card-img-wrapper, .card-body, .tag-tipo, .pet-localizacao,
        .btn-detalhes), para que o visual fique idêntico ao restante do site.
        Campos que não existem na tabela animais_adocao (abrigo, peso, altura,
        vacinado, castrado, deficiência, fotos extras) usam valores de
        fallback para não quebrar o layout.
========================================================================== */
    echo "<div class='pets-container'>";

    while(
        $animal = mysqli_fetch_assoc($resultado)
    ){
        // Fallbacks seguros para campos que podem não existir na tabela
        $idAnimal      = htmlspecialchars($animal['id'] ?? '');
        $nome          = htmlspecialchars($animal['nome'] ?? '');
        $especie       = htmlspecialchars($animal['especie'] ?? '');
        $raca          = htmlspecialchars($animal['raca'] ?? '');
        $idade         = htmlspecialchars($animal['idade'] ?? '');
        $sexoAnimal    = htmlspecialchars($animal['sexo'] ?? '');
        $porte         = htmlspecialchars($animal['porte'] ?? '');
        $descricao     = htmlspecialchars($animal['descricao'] ?? '');
        $foto          = htmlspecialchars($animal['foto'] ?? '');
        $abrigoNome    = htmlspecialchars($animal['abrigo'] ?? 'Abrigo Pet Vida');

        // Classe de tipo (cachorro/gato) e tag visual, igual à página de adoção
        $classeTipo = ($especie === 'Gato') ? 'gato' : 'cachorro';
        $tagClasse  = ($especie === 'Gato') ? 'tag-gato' : 'tag-cachorro';

        echo "

        <article class='pet-card {$classeTipo}' data-id='{$idAnimal}'>

            <div class='card-img-wrapper'>
                <img src='../imagens/{$foto}' alt='{$nome}, {$raca}' loading='lazy'>
            </div>

            <div class='card-body'>
                <h3 class='pet-nome'>{$nome}</h3>
                <p class='pet-info'>
                    <span class='tag-tipo {$tagClasse}'>{$especie}</span>
                    · {$porte} · {$idade} anos
                </p>
                <p class='pet-info'>Sexo: {$sexoAnimal}</p>
                <p class='pet-info'>{$descricao}</p>
                <p class='pet-localizacao'>
                    <svg aria-hidden='true' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z'/><circle cx='12' cy='10' r='3'/></svg>
                    {$abrigoNome}
                </p>
                <button class='btn-detalhes' onclick=\"window.location.href='../adocao.html'\">Ver Detalhes</button>
            </div>

        </article>

        ";

    }

    echo "</div>";

}

?>

<div class="acoes">

    <button
        class="botao"
        onclick="window.location.href='../quiz.html'"
    >
        Refazer Quiz
    </button>

    <a
        href="../adocao.html"
        class="botao"
    >
        Ver todos os pets
    </a>

</div>

</main>

</body>
</html>