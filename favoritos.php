<?php
require_once "conexao.php"; 
session_start(); // Início das sessões

// Verificação para garantir que os dados estão vindo através do envio de um formulário POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Aqui em baixo é pego apenas o ID do animal vindo do formulário e colocado em uma variável
    $id_animal = $_POST["animal_id"]; 

    // Aqui inicia a estrutura try/catch para tentar buscar as informações completas do animal direto no banco de dados
    try {
        // Prepara a consulta SQL para selecionar o animal específico que possui o ID recebido
        $sql = "SELECT * FROM tb_animal WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $id_animal);
        $stmt->execute();
        
        // Organiza os dados encontrados do banco dentro de uma variável associativa chamada $animal
        $animal = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificação de segurança caso o ID não pertença a nenhum animal cadastrado no banco
        if (!$animal) {
            die("Animal não encontrado.");
        }

    } catch (PDOException $e) {
        die("Erro ao buscar dados do animal: " . $e->getMessage());
    }

    // Aqui em baixo as informações completas vindas do banco de dados são extraídas e guardadas em suas variáveis correspondentes
    $nome          = $animal["nm_animal"];     
    $especie       = $animal["ds_especie"];    
    $raca          = $animal["ds_raca"];       
    $idade         = $animal["nr_idade"];
    $sexo          = $animal["ds_sexo"];
    $descricao     = $animal["ds_descricao"];
    $peso          = $animal["vl_peso"];
    $porte         = $animal["ds_porte"];
    $data_cadastro = $animal["dt_cadastro"];
    $origem        = $animal["ds_origem"];

    
    // Aqui é feita a verificação se o item já foi adicionado aos favoritos da sessão
    if (!isset($_SESSION['favoritos'][$id_animal])) {
        
        // Se ele não foi adicionado antes, todas as variáveis com as informações do banco são salvas em um array dentro da sessão
        $_SESSION['favoritos'][$id_animal] = [
            'nome'          => $nome,
            'especie'       => $especie,
            'raca'          => $raca,
            'idade'         => $idade,
            'sexo'          => $sexo,
            'descricao'     => $descricao,
            'peso'          => $peso,
            'porte'         => $porte,
            'data_cadastro' => $data_cadastro,
            'origem'        => $origem
        ];
        
    } else {
        // Caso ele já esteja na lista, o item é removido dos favoritos da sessão (ação de desmarcar o coração)
        unset($_SESSION['favoritos'][$id_animal]);
    }
    
    // Aqui após a verificação de tudo e a atualização da sessão, o usuário é redirecionado para a página de favoritos
    header("Location: favoritos.php");
    exit();
}
?>