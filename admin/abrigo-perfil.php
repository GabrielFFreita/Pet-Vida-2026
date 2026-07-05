<?php
require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../config/sessao.php";
verificarAdmin();

// 1. VERIFICAÇÃO E BUSCA DOS DADOS DO ABRIGO
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: abrigos.php");
    exit;
}

$id_abrigo = intval($_GET['id']);

try {
    $sql_abrigo = "SELECT * FROM abrigos WHERE id = :id";
    $stmt_abrigo = $pdo->prepare($sql_abrigo);
    $stmt_abrigo->execute([':id' => $id_abrigo]);
    $abrigo = $stmt_abrigo->fetch(PDO::FETCH_ASSOC);

    if (!$abrigo) {
        die("Abrigo não encontrado.");
    }
} catch (PDOException $e) {
    die("Erro ao buscar abrigo: " . $e->getMessage());
}

// 2. PROCESSAMENTO DO CADASTRO DO ANIMAL
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'cadastrar_animal') {
    
    $fotosAnimal = [];
    $extensoesPermitidas = ["jpg", "jpeg", "png", "webp"];
    $pasta = __DIR__ . "/../uploads/";

    if (!is_dir($pasta)) {
        mkdir($pasta, 0777, true);
    }

    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
        foreach ($_FILES['images']['name'] as $indice => $nomeOriginal) {
            if ($_FILES['images']['error'][$indice] !== UPLOAD_ERR_OK || empty($nomeOriginal)) {
                continue;
            }

            $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

            if (!in_array($extensao, $extensoesPermitidas)) {
                continue;
            }

            $nomeSeguro = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($nomeOriginal));
            $nomeFoto = uniqid('', true) . "_" . $nomeSeguro;

            if (move_uploaded_file($_FILES['images']['tmp_name'][$indice], $pasta . $nomeFoto)) {
                $fotosAnimal[] = $nomeFoto;
            }
        }
    }

    if (empty($fotosAnimal)) {
        $fotosAnimal[] = 'not_image.png';
    }

    $nome          = trim($_POST['nome_animal']);
    $especie       = trim($_POST['especie_animal']);
    $raca          = trim($_POST['raca_animal']);
    $idade         = trim($_POST['idade_animal']);
    $sexo          = trim($_POST['sexo_animal']);
    $descricao     = trim($_POST['descricao_animal']);
    $deficiencia   = trim($_POST['deficiencia_animal'] ?? '');
    $deficiencia   = ($deficiencia === '') ? null : $deficiencia;
    $peso          = trim($_POST['peso_animal']);
    $porte         = trim($_POST['porte_animal']);
    $data_cadastro = date('Y-m-d'); 
    $status_adocao = 'Disponível';
    $castrado      = (isset($_POST['castrado']) && $_POST['castrado'] !== '') ? intval($_POST['castrado']) : null;
    $vacinado      = (isset($_POST['vacinado']) && $_POST['vacinado'] !== '') ? intval($_POST['vacinado']) : null;

    try {
        $sql = "INSERT INTO animais_adocao (
            nome, especie, raca, idade, sexo, porte, descricao, deficiencia, status_adocao, castrado, vacinado, peso, id_abrigo, data_cadastro
        ) VALUES (
            :nome, :especie, :raca, :idade, :sexo, :porte, :descricao, :deficiencia, :status_adocao, :castrado, :vacinado, :peso, :id_abrigo, :data_cadastro
        );";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":especie", $especie);
        $stmt->bindParam(":raca", $raca);
        $stmt->bindParam(":idade", $idade);
        $stmt->bindParam(":sexo", $sexo);
        $stmt->bindParam(":porte", $porte);
        $stmt->bindParam(":descricao", $descricao);
        $stmt->bindParam(":deficiencia", $deficiencia);
        $stmt->bindParam(":status_adocao", $status_adocao);
        $stmt->bindParam(":castrado", $castrado);
        $stmt->bindParam(":vacinado", $vacinado);
        $stmt->bindParam(":peso", $peso);
        $stmt->bindParam(":id_abrigo", $id_abrigo);
        $stmt->bindParam(":data_cadastro", $data_cadastro);
        
        $stmt->execute();
        $idAnimal = $pdo->lastInsertId();

        $sqlfoto = "INSERT INTO foto_animal (id_animal, ds_img) VALUES (:id_animal, :foto_animal)";
        $stmtFoto = $pdo->prepare($sqlfoto);

        foreach ($fotosAnimal as $nomeFoto) {
            $stmtFoto->execute([
                ":id_animal" => $idAnimal,
                ":foto_animal" => $nomeFoto
            ]);
        }

        echo "<script>alert('Animal cadastrado com sucesso!'); window.location.href='abrigo-perfil.php?id=$id_abrigo';</script>";
    } catch (PDOException $e) {
        die('Erro ao salvar no banco: ' . $e->getMessage());
    }
}

// 3. BUSCA DOS ANIMAIS PERTENCENTES A ESTE ABRIGO (Trazendo a foto associada)
try {
    $sql_animais = "SELECT a.*, fp.ds_img FROM animais_adocao a 
                    LEFT JOIN (
                        SELECT id_animal, MIN(ds_img) AS ds_img
                        FROM foto_animal
                        GROUP BY id_animal
                    ) fp ON a.id_animal = fp.id_animal 
                    WHERE a.id_abrigo = :id_abrigo 
                    ORDER BY a.id_animal DESC";
    $stmt_animais = $pdo->prepare($sql_animais);
    $stmt_animais->execute([':id_abrigo' => $id_abrigo]);
    $animais = $stmt_animais->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $animais = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Abrigo | Pet Vida</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@400;600&family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

    <?php $adminActivePage = 'abrigos'; require __DIR__ . '/../includes/menu-admin.php'; ?>

    <main class="content">
        <div class="header-acoes-admin">
            <div>
                <h1><?php echo htmlspecialchars($abrigo['nome']); ?></h1>
                <p class="subtitulo">Gerencie as informações e os animais vinculados a este local.</p>
            </div>
            <button class="btn-adicionar-novo" onclick="abrirModal()">
                <span>🐾</span> Cadastrar Animal
            </button>
        </div>

        <div class="container-perfil">
            <section class="card-info-abrigo">
                <h2>Informações do Abrigo</h2>
                <hr style="border: 0; border-top: 1px solid var(--borda); margin-bottom: 15px;">
                <div class="info-grid">
                    <div class="info-item">
                        <strong>CNPJ</strong>
                        <p><?php echo htmlspecialchars($abrigo['cnpj'] ?? 'Não informado'); ?></p>
                    </div>
                    <div class="info-item">
                        <strong>CEP</strong>
                        <p><?php echo htmlspecialchars($abrigo['cep']); ?></p>
                    </div>
                    <div class="info-item">
                        <strong>Endereço</strong>
                        <p><?php echo htmlspecialchars($abrigo['localizacao']); ?></p>
                    </div>
                </div>
                <div class="info-item" style="margin-top: 20px;">
                    <strong>Descrição / Histórico</strong>
                    <p><?php echo nl2br(htmlspecialchars($abrigo['descricao'] ?? 'Sem descrição disponível.')); ?></p>
                </div>
            </section>

            <section class="secao-animais-abrigo">
                <h2>Animais Resgatados Neste Local</h2>
                
                <?php if (empty($animais)): ?>
                    <div style="text-align: center; color: var(--texto-leve); padding: 40px; background: var(--fundo-card); border-radius: var(--raio); border: 1px solid var(--borda);">
                        <p>Nenhum animal cadastrado para este abrigo ainda.</p>
                    </div>
                <?php else: ?>
                    <div class="grid-animais-perfil">
                        <?php foreach ($animais as $animal): ?>
                            <article class="card-animal-pequeno">
                                <div class="animal-thumb">
                                    <?php 
                                        $fotoCaminho = !empty($animal['ds_img']) ? '../uploads/' . $animal['ds_img'] : '../uploads/not_image.png';
                                    ?>
                                    <img src="<?php echo $fotoCaminho; ?>" alt="Foto de <?php echo htmlspecialchars($animal['nome']); ?>">
                                    <span class="badge-status"><?php echo htmlspecialchars($animal['status_adocao']); ?></span>
                                </div>
                                <div class="animal-corpo">
                                    <h3 class="animal-nome"><?php echo htmlspecialchars($animal['nome']); ?></h3>
                                    
                                    <div class="animal-tags">
                                        <span class="tag-info"><?php echo htmlspecialchars($animal['especie']); ?></span>
                                        <span class="tag-info"><?php echo htmlspecialchars($animal['raca']); ?></span>
                                        <span class="tag-info"><?php echo htmlspecialchars($animal['sexo']); ?></span>
                                        <span class="tag-info"><?php echo htmlspecialchars($animal['porte']); ?></span>
                                        <span class="tag-info"><?php echo htmlspecialchars($animal['idade']); ?> anos</span>
                                        <span class="tag-info"><?php echo htmlspecialchars($animal['peso']); ?> kg</span>
                                        
                                        <?php if ($animal['castrado'] === 1): ?>
                                            <span class="tag-info marcador">Castrado</span>
                                        <?php endif; ?>
                                        <?php if ($animal['vacinado'] === 1): ?>
                                            <span class="tag-info marcador">Vacinado</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <div id="modalAnimal" class="modal-admin-overlay" onclick="fecharModalExterno(event)">
        <div class="modal-admin-content" style="max-width: 650px;">
            <div class="modal-admin-header">
                <h2>Cadastrar Animal para este Abrigo</h2>
                <button class="btn-modal-fechar" onclick="fecharModal()">&times;</button>
            </div>
            <form action="abrigo-perfil.php?id=<?php echo $id_abrigo; ?>" method="POST" enctype="multipart/form-data" class="modal-admin-form">
                <input type="hidden" name="action" value="cadastrar_animal">
                
                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="nome_animal">Nome do Animal</label>
                        <input type="text" id="nome_animal" name="nome_animal" required placeholder="Ex: Thor">
                    </div>
                    <div class="form-grupo">
                        <label for="especie_animal">Espécie</label>
                        <input type="text" id="especie_animal" name="especie_animal" required placeholder="Ex: Cachorro">
                    </div>
                </div>

                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="raca_animal">Raça</label>
                        <input type="text" id="raca_animal" name="raca_animal" required placeholder="Ex: Labrador">
                    </div>
                    <div class="form-grupo">
                        <label for="idade_animal">Idade</label>
                        <input type="text" id="idade_animal" name="idade_animal" required placeholder="Ex: 3 anos">
                    </div>
                </div>

                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="sexo_animal">Sexo</label>
                        <select id="sexo_animal" name="sexo_animal" required>
                            <option value="Macho">Macho</option>
                            <option value="Fêmea">Fêmea</option>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label for="porte_animal">Porte</label>
                        <select id="porte_animal" name="porte_animal" required>
                            <option value="Pequeno">Pequeno</option>
                            <option value="Médio">Médio</option>
                            <option value="Grande">Grande</option>
                        </select>
                    </div>
                </div>

                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="peso_animal">Peso (kg)</label>
                        <input type="number" step="0.01" id="peso_animal" name="peso_animal" required placeholder="Ex: 32.50">
                    </div>
                    <div class="form-grupo">
                        <label for="images">Fotos do Animal</label>
                        <input type="file" id="images" name="images[]" accept="image/*" multiple>
                    </div>
                </div>

                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="castrado">Castrado?</label>
                        <select id="castrado" name="castrado">
                            <option value="1">Sim</option>
                            <option value="0">Não</option>
                            <option value="">Não informado</option>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label for="vacinado">Vacinado?</label>
                        <select id="vacinado" name="vacinado">
                            <option value="1">Sim</option>
                            <option value="0">Não</option>
                            <option value="">Não informado</option>
                        </select>
                    </div>
                </div>

                <div class="form-grupo">
                    <label for="descricao_animal">Descrição</label>
                    <textarea id="descricao_animal" name="descricao_animal" rows="3" placeholder="Conte mais sobre o comportamento do animal..."></textarea>
                </div>

                <div class="form-grupo">
                    <label for="deficiencia_animal">Deficiência (opcional)</label>
                    <input type="text" id="deficiencia_animal" name="deficiencia_animal" placeholder="Ex: Cego de um olho, três patas... deixe em branco se não houver">
                </div>

                <div class="modal-admin-footer">
                    <button type="button" class="btn-modal-cancelar" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn-modal-salvar">Salvar Animal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModal() {
            document.getElementById('modalAnimal').classList.add('ativo');
        }
        function fecharModal() {
            document.getElementById('modalAnimal').classList.remove('ativo');
        }
        function fecharModalExterno(event) {
            if (event.target === document.getElementById('modalAnimal')) {
                fecharModal();
            }
        }
    </script>
</body>
</html>
