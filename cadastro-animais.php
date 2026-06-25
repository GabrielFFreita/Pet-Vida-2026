<?php
require_once "conexao.php";
// require_once "config_sessao.php";
// verificarLogado();

// Busca das informações colocadas no form do html, dentro de variáveis do php
if ($_SERVER["REQUEST_METHOD"] == "POST"){

    // Aqui são pegos os valores do formulário e colocados em variáveis
    $nome           = trim($_POST['nome_animal']);
    $especie        = trim($_POST['especie_animal']);
    $raca           = trim($_POST['raca_animal']);
    $idade          = trim($_POST['idade_animal']);
    $sexo           = trim($_POST['sexo_animal']);
    $descricao      = trim($_POST['descricao_animal']);
    $peso           = trim($_POST['peso_animal']);
    $porte          = trim($_POST['porte_animal']);
    $vacinado       = trim($_POST['vacinado_animal']); // ADICIONADO: Captura do campo vacinado
    $abrigo         = trim($_POST['id_abrigo']);       // ADICIONADO: Captura do abrigo associado
    $data_cadastro  = trim($_POST['data_cadastro']);

    try {
        // Query SQL atualizada para incluir vacinado e abrigo
        $sql = "INSERT INTO animais_adocao (
            nome,
            especie,
            raca,
            idade,
            sexo,
            descricao,
            peso,
            porte,
            vacinado,
            abrigo,
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
            :vacinado,
            :abrigo,
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
        $stmt->bindParam(":vacinado", $vacinado); // Vínculo adicionado
        $stmt->bindParam(":abrigo", $abrigo);     // Vínculo adicionado
        $stmt->bindParam(":data_cadastro", $data_cadastro);

        // Execução do código sql do animal
        $stmt->execute();

        // Recupera o ID do animal que acabou de ser inserido
        $idAnimal = $pdo->lastInsertId();

        // ── PROCESSAMENTO DE MÚLTIPLAS IMAGENS (CARROSSEL) ──
        
        $sqlfoto = "INSERT INTO foto_animal (id_animal, ds_img) VALUES (:id_animal, :foto_animal)";
        $stmtFoto = $pdo->prepare($sqlfoto);

        $fotosEnviadas = false;

        // Verifica se o array de imagens foi enviado e se possui arquivos
        if (isset($_FILES['image']) && is_array($_FILES['image']['name'])) {
            $totalArquivos = count($_FILES['image']['name']);
            $pasta = "uploads/";

            // Cria a pasta caso não exista
            if (!is_dir($pasta)) {
                mkdir($pasta, 0777, true);
            }

            // Loop para processar cada foto selecionada pelo usuário
            for ($i = 0; $i < $totalArquivos; $i++) {
                if ($_FILES['image']['error'][$i] == 0) {
                    $extensao = strtolower(pathinfo($_FILES['image']['name'][$i], PATHINFO_EXTENSION));
                    $extensoesPermitidas = array("jpg", "jpeg", "png");

                    if (in_array($extensao, $extensoesPermitidas)) {
                        // Gera um nome único para cada arquivo individualmente
                        $nomeFoto = uniqid() . "_" . basename($_FILES['image']['name'][$i]);

                        if (move_uploaded_file($_FILES['image']['tmp_name'][$i], $pasta . $nomeFoto)) {
                            $stmtFoto->bindParam(":id_animal", $idAnimal);
                            $stmtFoto->bindParam(":foto_animal", $nomeFoto);
                            $stmtFoto->execute();
                            $fotosEnviadas = true;
                        }
                    }
                }
            }
        }

        // Se nenhuma foto válida passou pelo upload, grava a imagem padrão do sistema
        if (!$fotosEnviadas) {
            $nomePadrao = 'not_image.png';
            $stmtFoto->bindParam(":id_animal", $idAnimal);
            $stmtFoto->bindParam(":foto_animal", $nomePadrao);
            $stmtFoto->execute();
        }

        echo "
        <script>
            alert('Animal cadastrado com sucesso!');
            window.location.href='adimpage.php';
        </script>
        ";

    } catch (PDOException $e) {
        die('Erro ao salvar no banco: ' . $e->getMessage());
    }
}
?>