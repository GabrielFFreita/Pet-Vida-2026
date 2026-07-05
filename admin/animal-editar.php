<?php
require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../config/sessao.php";
require_once __DIR__ . "/../includes/animal-admin-helpers.php";

verificarAdmin();

$idAnimal = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idAnimal = filter_input(INPUT_POST, "id_animal", FILTER_VALIDATE_INT);
}

if (!$idAnimal) {
    header("Location: animais.php?status=erro");
    exit;
}

$erro = null;
$animal = null;
$abrigos = [];
$fotos = [];

function carregarDadosAnimalEdicao(PDO $pdo, int $idAnimal): array
{
    $stmtAnimal = $pdo->prepare("
        SELECT
            a.*,
            ab.nome AS nome_abrigo
        FROM animais_adocao a
        LEFT JOIN abrigos ab ON ab.id = a.id_abrigo
        WHERE a.id_animal = :id_animal
        LIMIT 1
    ");
    $stmtAnimal->execute([":id_animal" => $idAnimal]);
    $animal = $stmtAnimal->fetch(PDO::FETCH_ASSOC);

    if (!$animal) {
        return [null, [], []];
    }

    $abrigos = $pdo->query("
        SELECT id, nome
        FROM abrigos
        ORDER BY nome ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $fotos = animalAdminFetchFotos($pdo, $idAnimal);

    return [$animal, $abrigos, $fotos];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim((string) ($_POST["nome"] ?? ""));
    $especie = trim((string) ($_POST["especie"] ?? ""));
    $raca = trim((string) ($_POST["raca"] ?? ""));
    $idade = trim((string) ($_POST["idade"] ?? ""));
    $sexo = trim((string) ($_POST["sexo"] ?? ""));
    $porte = trim((string) ($_POST["porte"] ?? ""));
    $descricao = trim((string) ($_POST["descricao"] ?? ""));
    $deficiencia = trim((string) ($_POST["deficiencia"] ?? ""));
    $statusAdocao = trim((string) ($_POST["status_adocao"] ?? ""));
    $pesoInformado = trim((string) ($_POST["peso"] ?? ""));
    $idAbrigo = filter_input(INPUT_POST, "id_abrigo", FILTER_VALIDATE_INT);
    $castradoBruto = $_POST["castrado"] ?? "";
    $vacinadoBruto = $_POST["vacinado"] ?? "";
    $fotosRemover = array_map("intval", $_POST["remover_fotos"] ?? []);

    $sexoPermitido = ["Macho", "Fêmea"];
    $portePermitido = ["Pequeno", "Médio", "Grande"];
    $statusPermitido = ["Disponível", "Em processo", "Adotado"];

    $castrado = $castradoBruto === "" ? null : (int) $castradoBruto;
    $vacinado = $vacinadoBruto === "" ? null : (int) $vacinadoBruto;
    $peso = $pesoInformado === "" ? null : (float) $pesoInformado;
    $deficiencia = $deficiencia === "" ? null : $deficiencia;

    if ($nome === "" || $especie === "" || $idade === "") {
        $erro = "Preencha nome, tipo e idade para salvar o animal.";
    } elseif (!in_array($sexo, $sexoPermitido, true)) {
        $erro = "Selecione um sexo válido para o animal.";
    } elseif (!in_array($porte, $portePermitido, true)) {
        $erro = "Selecione um porte válido para o animal.";
    } elseif (!in_array($statusAdocao, $statusPermitido, true)) {
        $erro = "Selecione um status de adoção válido.";
    } elseif (!$idAbrigo) {
        $erro = "Selecione o abrigo responsável pelo animal.";
    } elseif ($castrado !== null && !in_array($castrado, [0, 1], true)) {
        $erro = "Valor inválido para castração.";
    } elseif ($vacinado !== null && !in_array($vacinado, [0, 1], true)) {
        $erro = "Valor inválido para vacinação.";
    } elseif ($peso !== null && $peso < 0) {
        $erro = "Informe um peso válido para o animal.";
    } else {
        try {
            $stmtAbrigo = $pdo->prepare("
                SELECT nome
                FROM abrigos
                WHERE id = :id
                LIMIT 1
            ");
            $stmtAbrigo->execute([":id" => $idAbrigo]);
            $nomeAbrigo = $stmtAbrigo->fetchColumn();

            if ($nomeAbrigo === false) {
                $erro = "O abrigo selecionado não foi encontrado.";
            } else {
                $pdo->beginTransaction();

                $stmtUpdate = $pdo->prepare("
                    UPDATE animais_adocao
                    SET
                        nome = :nome,
                        especie = :especie,
                        raca = :raca,
                        idade = :idade,
                        sexo = :sexo,
                        porte = :porte,
                        descricao = :descricao,
                        deficiencia = :deficiencia,
                        status_adocao = :status_adocao,
                        castrado = :castrado,
                        vacinado = :vacinado,
                        peso = :peso,
                        id_abrigo = :id_abrigo,
                        abrigo = :abrigo
                    WHERE id_animal = :id_animal
                ");
                $stmtUpdate->execute([
                    ":nome" => $nome,
                    ":especie" => $especie,
                    ":raca" => $raca !== "" ? $raca : null,
                    ":idade" => $idade,
                    ":sexo" => $sexo,
                    ":porte" => $porte,
                    ":descricao" => $descricao !== "" ? $descricao : null,
                    ":deficiencia" => $deficiencia,
                    ":status_adocao" => $statusAdocao,
                    ":castrado" => $castrado,
                    ":vacinado" => $vacinado,
                    ":peso" => $peso,
                    ":id_abrigo" => $idAbrigo,
                    ":abrigo" => $nomeAbrigo,
                    ":id_animal" => $idAnimal,
                ]);

                if (!empty($fotosRemover)) {
                    $placeholders = implode(",", array_fill(0, count($fotosRemover), "?"));
                    $paramsBusca = array_merge([$idAnimal], $fotosRemover);
                    $stmtFotosRemover = $pdo->prepare("
                        SELECT id_foto, ds_img
                        FROM foto_animal
                        WHERE id_animal = ?
                          AND id_foto IN ($placeholders)
                    ");
                    $stmtFotosRemover->execute($paramsBusca);
                    $fotosSelecionadas = $stmtFotosRemover->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($fotosSelecionadas)) {
                        $stmtDeleteFotos = $pdo->prepare("
                            DELETE FROM foto_animal
                            WHERE id_animal = ?
                              AND id_foto IN ($placeholders)
                        ");
                        $stmtDeleteFotos->execute($paramsBusca);

                        foreach ($fotosSelecionadas as $fotoRemovida) {
                            animalAdminRemoverArquivoFoto((string) $fotoRemovida["ds_img"]);
                        }
                    }
                }

                $novasFotos = animalAdminProcessarNovasFotos($_FILES["novas_fotos"] ?? []);

                if (!empty($novasFotos)) {
                    animalAdminSalvarFotos($pdo, $idAnimal, $novasFotos);
                    animalAdminRemoverFotoPadraoSeHouver($pdo, $idAnimal);
                }

                animalAdminGarantirFotoPadrao($pdo, $idAnimal);
                $pdo->commit();

                header("Location: animais.php?status=editado");
                exit;
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $erro = "Não foi possível salvar as alterações do animal.";
        }
    }
}

try {
    [$animal, $abrigos, $fotos] = carregarDadosAnimalEdicao($pdo, $idAnimal);

    if (!$animal) {
        header("Location: animais.php?status=erro");
        exit;
    }
} catch (PDOException $e) {
    header("Location: animais.php?status=erro");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Animal | Pet Vida</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@400;600&family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

    <?php $adminActivePage = 'animais'; require __DIR__ . '/../includes/menu-admin.php'; ?>

    <main class="content">
        <div class="header-acoes-admin header-acoes-admin--stack">
            <div>
                <h1>Editar Animal</h1>
                <p class="subtitulo">Atualize os dados cadastrais e faça a manutenção das fotos já enviadas com uma galeria simples e direta.</p>
            </div>
            <a href="animais.php" class="btn-admin btn-editar btn-admin--voltar">Voltar para a listagem</a>
        </div>

        <?php if ($erro !== null): ?>
            <div class="alerta-admin alerta-admin--erro">
                <?php echo htmlspecialchars($erro, ENT_QUOTES, "UTF-8"); ?>
            </div>
        <?php endif; ?>

        <section class="painel-card painel-card--formulario">
            <div class="painel-card-topo">
                <div>
                    <h2><?php echo htmlspecialchars($animal["nome"], ENT_QUOTES, "UTF-8"); ?></h2>
                    <p>Fluxo de edição com cadastro textual, manutenção do abrigo e gerenciamento completo das imagens atuais.</p>
                </div>
                <span class="header-tag-admin"><?php echo htmlspecialchars($animal["status_adocao"], ENT_QUOTES, "UTF-8"); ?></span>
            </div>

            <form action="animal-editar.php" method="POST" enctype="multipart/form-data" class="modal-admin-form modal-admin-form--page">
                <input type="hidden" name="id_animal" value="<?php echo (int) $animal["id_animal"]; ?>">

                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="nome">Nome</label>
                        <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars((string) $animal["nome"], ENT_QUOTES, "UTF-8"); ?>">
                    </div>
                    <div class="form-grupo">
                        <label for="especie">Tipo</label>
                        <input type="text" id="especie" name="especie" required value="<?php echo htmlspecialchars((string) $animal["especie"], ENT_QUOTES, "UTF-8"); ?>">
                    </div>
                </div>

                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="raca">Raça</label>
                        <input type="text" id="raca" name="raca" value="<?php echo htmlspecialchars((string) ($animal["raca"] ?? ""), ENT_QUOTES, "UTF-8"); ?>">
                    </div>
                    <div class="form-grupo">
                        <label for="idade">Idade</label>
                        <input type="text" id="idade" name="idade" required value="<?php echo htmlspecialchars((string) $animal["idade"], ENT_QUOTES, "UTF-8"); ?>">
                    </div>
                </div>

                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="sexo">Sexo</label>
                        <select id="sexo" name="sexo" required>
                            <option value="Macho" <?php echo ($animal["sexo"] ?? "") === "Macho" ? "selected" : ""; ?>>Macho</option>
                            <option value="Fêmea" <?php echo ($animal["sexo"] ?? "") === "Fêmea" ? "selected" : ""; ?>>Fêmea</option>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label for="porte">Porte</label>
                        <select id="porte" name="porte" required>
                            <option value="Pequeno" <?php echo ($animal["porte"] ?? "") === "Pequeno" ? "selected" : ""; ?>>Pequeno</option>
                            <option value="Médio" <?php echo ($animal["porte"] ?? "") === "Médio" ? "selected" : ""; ?>>Médio</option>
                            <option value="Grande" <?php echo ($animal["porte"] ?? "") === "Grande" ? "selected" : ""; ?>>Grande</option>
                        </select>
                    </div>
                </div>

                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="peso">Peso (kg)</label>
                        <input type="number" step="0.01" min="0" id="peso" name="peso" value="<?php echo htmlspecialchars((string) ($animal["peso"] ?? ""), ENT_QUOTES, "UTF-8"); ?>">
                    </div>
                    <div class="form-grupo">
                        <label for="id_abrigo">Abrigo</label>
                        <select id="id_abrigo" name="id_abrigo" required>
                            <option value="">Selecione</option>
                            <?php foreach ($abrigos as $abrigo): ?>
                                <option value="<?php echo (int) $abrigo["id"]; ?>" <?php echo (int) ($animal["id_abrigo"] ?? 0) === (int) $abrigo["id"] ? "selected" : ""; ?>>
                                    <?php echo htmlspecialchars($abrigo["nome"], ENT_QUOTES, "UTF-8"); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="castrado">Castração</label>
                        <select id="castrado" name="castrado">
                            <option value="" <?php echo ($animal["castrado"] === null) ? "selected" : ""; ?>>Não informado</option>
                            <option value="1" <?php echo (string) ($animal["castrado"] ?? "") === "1" ? "selected" : ""; ?>>Sim</option>
                            <option value="0" <?php echo (string) ($animal["castrado"] ?? "") === "0" ? "selected" : ""; ?>>Não</option>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label for="vacinado">Vacinação</label>
                        <select id="vacinado" name="vacinado">
                            <option value="" <?php echo ($animal["vacinado"] === null) ? "selected" : ""; ?>>Não informado</option>
                            <option value="1" <?php echo (string) ($animal["vacinado"] ?? "") === "1" ? "selected" : ""; ?>>Sim</option>
                            <option value="0" <?php echo (string) ($animal["vacinado"] ?? "") === "0" ? "selected" : ""; ?>>Não</option>
                        </select>
                    </div>
                </div>

                <div class="form-linha-dupla">
                    <div class="form-grupo">
                        <label for="status_adocao">Status da adoção</label>
                        <select id="status_adocao" name="status_adocao" required>
                            <option value="Disponível" <?php echo ($animal["status_adocao"] ?? "") === "Disponível" ? "selected" : ""; ?>>Disponível</option>
                            <option value="Em processo" <?php echo ($animal["status_adocao"] ?? "") === "Em processo" ? "selected" : ""; ?>>Em processo</option>
                            <option value="Adotado" <?php echo ($animal["status_adocao"] ?? "") === "Adotado" ? "selected" : ""; ?>>Adotado</option>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label for="deficiencia">Deficiência</label>
                        <input type="text" id="deficiencia" name="deficiencia" value="<?php echo htmlspecialchars((string) ($animal["deficiencia"] ?? ""), ENT_QUOTES, "UTF-8"); ?>" placeholder="Deixe em branco se não houver">
                    </div>
                </div>

                <div class="form-grupo">
                    <label for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao" rows="4"><?php echo htmlspecialchars((string) ($animal["descricao"] ?? ""), ENT_QUOTES, "UTF-8"); ?></textarea>
                </div>

                <section class="bloco-fotos-admin">
                    <div class="bloco-fotos-admin__topo">
                        <div>
                            <h3>Fotos do animal</h3>
                            <p>Remova imagens antigas por miniatura e faça upload múltiplo de novas fotos no mesmo envio.</p>
                        </div>
                        <span class="painel-resumo-pill"><?php echo count($fotos); ?> imagem(ns)</span>
                    </div>

                    <div class="galeria-admin-fotos">
                        <?php foreach ($fotos as $foto): ?>
                            <label class="card-foto-admin">
                                <img src="<?php echo htmlspecialchars(animalAdminFotoPublica($foto["ds_img"]), ENT_QUOTES, "UTF-8"); ?>" alt="Foto cadastrada do animal">
                                <span class="card-foto-admin__nome"><?php echo htmlspecialchars($foto["ds_img"], ENT_QUOTES, "UTF-8"); ?></span>
                                <span class="card-foto-admin__acao">
                                    <input type="checkbox" name="remover_fotos[]" value="<?php echo (int) $foto["id_foto"]; ?>">
                                    Remover
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="upload-admin-box">
                        <label for="novas_fotos" class="upload-admin-box__label">Adicionar novas fotos</label>
                        <input type="file" id="novas_fotos" name="novas_fotos[]" accept="image/*" multiple>
                        <p class="upload-admin-box__hint">Formatos aceitos: JPG, JPEG, PNG e WEBP. As novas imagens são enviadas junto com a atualização do cadastro.</p>
                        <div id="previewNovasFotos" class="preview-novas-fotos" aria-live="polite"></div>
                    </div>
                </section>

                <div class="modal-admin-footer">
                    <a href="animais.php" class="btn-modal-cancelar btn-link-admin">Cancelar</a>
                    <button type="submit" class="btn-modal-salvar">Salvar Alterações</button>
                </div>
            </form>
        </section>
    </main>

    <script>
        const campoNovasFotos = document.getElementById("novas_fotos");
        const previewNovasFotos = document.getElementById("previewNovasFotos");

        campoNovasFotos.addEventListener("change", () => {
            previewNovasFotos.innerHTML = "";

            const arquivos = Array.from(campoNovasFotos.files || []);

            if (arquivos.length === 0) {
                return;
            }

            arquivos.forEach((arquivo) => {
                if (!arquivo.type.startsWith("image/")) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = (evento) => {
                    const item = document.createElement("div");
                    item.className = "preview-novas-fotos__item";
                    item.innerHTML = `
                        <img src="${evento.target.result}" alt="">
                        <span>${arquivo.name}</span>
                    `;
                    previewNovasFotos.appendChild(item);
                };
                reader.readAsDataURL(arquivo);
            });
        });
    </script>
</body>
</html>
