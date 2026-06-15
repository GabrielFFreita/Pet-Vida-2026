<?php
require_once "conexao.php";
require_once "config_sessao.php";
verificarLogado();

// Verificação se o usuário está ativo

// Busca das informações colocadas no form do html, dentro de variávies do php
if ($_SERVER["REQUEST_METHOD"] == "POST"){

    // Aqui criamos uma variável para colocar o nome das imagens de upload
    // Definimos o valor padrão caso nenhuma foto válida seja enviada
    $nomeFoto = 'not_image.png';

    // Aqui verificamos se o arquivo trazido do form é um file (imagem) e se ele veio ou não com algum erro
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {

        // Validação do formato: pega a extensão e converte para minúsculo
        $extensao = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $extensoesPermitidas = array("jpg", "jpeg", "png");

        // Só faz o upload se a extensão for permitida
        if (in_array($extensao, $extensoesPermitidas)) {

            // Aqui criamos caso ainda não exista, uma pasta para guardar os uploads
            $pasta = "uploads/";

            if (!is_dir($pasta)) {
                mkdir($pasta, 0777, true);
            }

            $nomeFoto = uniqid() . "_" . basename($_FILES['image']['name']);

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                $pasta . $nomeFoto
            );
        }
    }

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

    try {

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
            data_cadastro
    
        ) VALUES (
            :nome,
            :especie,
            :raca,
            :idade,
            :sexo,
            :descricao,
            :peso,
            :porte,
            :data_cadastro
  
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

     $idAnimal = $pdo->lastInsertId();

        $sqlfoto = "
        INSERT INTO foto_animal (
            id_animal,
            ds_img
        ) VALUES (
            :id_animal,
            :foto_animal
        )";

        $stmtFoto = $pdo->prepare($sqlfoto);

        $stmtFoto->bindParam(":id_animal", $idAnimal);
        $stmtFoto->bindParam(":foto_animal", $nomeFoto);

        $stmtFoto->execute();

        echo "
        <script>
            alert('Animal cadastrado com sucesso!');
            window.location.href='index.php';
        </script>
        ";


    } catch (PDOException $e) {
        die('Erro ao salvar no banco: ' . $e->getMessage());
    }
}
?>