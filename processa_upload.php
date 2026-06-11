<?php
require_once "conexao.php";
require_once "config_sessao.php";
verificarLogado();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    
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

    // Comandos da cURL (biblioteca do php que conversa com outros sites, ou seja prepara o bloco de código, e envia)
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
        
        echo "<script>alert('Animal cadastrado com sucesso!')</script>";
        
        // DICA: É essa variável $urlImagem que você guardaria no seu banco de dados,
        // ou usaria para renderizar na tela depois.

    } else {
        echo "Erro ao enviar para o ImgBB: " . ($resultado['error']['message'] ?? 'Erro desconhecido.');
    }
} else {
    echo "Nenhuma imagem foi enviada.";
}
?>