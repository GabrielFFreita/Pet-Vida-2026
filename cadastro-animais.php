<?php
require_once "conexao.php";
require_once "config_sessao.php";
verificarLogado();

// Verificação se o usuário está ativo

    

    // Busca das informações colocadas no form do html, dentro de variávies do php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['image'])){
    
    //Para o sistema conversar com a API imgbb, está é a variável que guarda o valor da chave da API que foi entregue pela própria
    $apiKey = 'SUA_CHAVE_API_AQUI'; 
    
    // Por segurança guardamos o arquivo temporário que o php cria em JSON para evitar que a memória trave e proteger o servidor
    $imagemTemporaria = $_FILES['image']['tmp_name'];
    
    // A API imgbb pede que usemos o tipo base64 para o arquivo, logo usamos o comando de bse64_enconde para transformá-lo nesse valor
    $imagemBase64 = base64_encode(file_get_contents($imagemTemporaria));

    //Aqui preparamos os dados para serem enviados para o banco de dados 
    $dados = [
        'key' => $apiKey,
        'image' => $imagemBase64
    ];

    // Comandos da cURL (biblioteca do php que conversa with outros sites, ou seja prepara o bloco de código, e envia)
    //Inicialize o cURL para fazer a requisição HTTP
    $ch = curl_init();

    // Define o endereço da API do ImgBB para onde a imagem vai
    curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload');

    // Avisa que o envio será por POST (método usado para criar/enviar dados)
    curl_setopt($ch, CURLOPT_POST, true);

    // Coloca os dados do pacote na mesa: a chave de API e a imagem em texto
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dados);

    // Diz ao PHP: "Guarde a resposta do ImgBB em uma variável em vez de exibir na tela"
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Envia tudo de fato. O cURL vai ao ImgBB e volta com a resposta (sucesso ou erro)
    $resposta = curl_exec($ch);

    // Fecha a conexão e desliga o motor do cURL (boa prática de memória)
    curl_close($ch);

    // Transformamos a resposta JSON do ImgBB em um array PHP
    $resultado = json_decode($resposta, true);

    // Aquiverifica se o upload deu certo
    if (isset($resultado['success']) && $resultado['success'] == true) {
        
        //Essa é a url final
        $urlImagem = $resultado['data']['url'];

        // Aqui são pegas os valores do formulário e colocados em variáveis
        
        $nome          = trim($_POST['nome_animal']);
        $especie       = trim($_POST['especie_animal']);
        $raca          = trim($_POST['raca_animal']);
        $idade         = trim($_POST['idade_animal']);
        $sexo          = trim($_POST['sexo_animal']);
        $descricao     = trim($_POST['descricao_animal']);
        $peso          = trim($_POST['peso_animal']);
        $porte         = trim($_POST['porte_animal']);
        $data_cadastro = trim($_POST['data_cadastro']);


        // variável com o código em sql
        $sql = "INSERT INTO animais_adocao (
            nome, 
            especie, 
            raca, 
            idade, 
            sexo, 
            descricao, 
            peso, 
            porte, 
            data_cadastro, 
            origem
        ) VALUES (
            :nome, 
            :especie, 
            :raca, 
            :idade, 
            :sexo, 
            :descricao, 
            :peso, 
            :porte, 
            :data_cadastro, 

        );";
        // Preparação do código em sql
        $stmt = $pdo->prepare($sql);

        // Preparação das variáveis stmt para serem colocadas no sql
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":especie", $especie);
        $stmt->bindParam(":raca", $raca);
        $stmt->bindParam(":idade", $idade);
        $stmt->bindParam(":sexo", $sexo);
        $stmt->bindParam(":descricao", $descricao);
        $stmt->bindParam(":peso", $peso);
        $stmt->bindParam(":porte", $porte);
        $stmt->bindParam(":data_cadastro", $data_cadastro);
     

        // Execução do código sql
        $stmt->execute();
        
        // Aqui é pego o último id do banco de dados, com o LastInsertId
        $idAnimalCadastrado = $pdo->lastInsertId();
        
        $sqlImagem = "INSERT INTO foto_animal ( id_animal,url_img) VALUES ( id_animal :url_img);";
        $stmtImagem = $pdo->prepare($sqlImagem);
        
        $stmtImagem->bindParam(":animal_id", $idAnimalCadastrado);
        $stmtImagem->bindParam(":url_img", $urlImagem);
        
        $stmtImagem->execute();

        echo "<script>alert('Animal cadastrado com sucesso!')</script>";
        
        // DICA: É essa variável $urlImagem que você guardaria no seu banco de dados,
        // ou usaria para renderizar na tela depois.

    } else {
        echo "Erro ao enviar para o ImgBB: " . ($resultado['error']['message'] ?? 'Erro desconhecido.');
    }
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        echo "Nenhuma imagem foi enviada.";
    }
}
?>