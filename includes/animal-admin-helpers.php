<?php

function animalAdminUploadDir(): string
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . "uploads";
}

function animalAdminUploadPublicPath(): string
{
    return '../uploads/';
}

function animalAdminPlaceholderFileName(): string
{
    return "not_image.png";
}

function animalAdminPlaceholderPath(): string
{
    return animalAdminUploadDir() . DIRECTORY_SEPARATOR . animalAdminPlaceholderFileName();
}

function animalAdminEnsureUploadDir(): void
{
    $uploadDir = animalAdminUploadDir();

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
}

function animalAdminEnsurePlaceholderImage(): string
{
    animalAdminEnsureUploadDir();

    $placeholderPath = animalAdminPlaceholderPath();

    if (!file_exists($placeholderPath)) {
        $logoPaths = [
            dirname(__DIR__) . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . "logo_petvida.png",
            dirname(__DIR__) . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . "Logo Pet Vida.png",
        ];

        foreach ($logoPaths as $logoPath) {
            if (file_exists($logoPath)) {
                copy($logoPath, $placeholderPath);
                break;
            }
        }
    }

    return animalAdminPlaceholderFileName();
}

function animalAdminFetchFotos(PDO $pdo, int $idAnimal): array
{
    $stmt = $pdo->prepare("
        SELECT id_foto, ds_img
        FROM foto_animal
        WHERE id_animal = :id_animal
        ORDER BY id_foto ASC
    ");
    $stmt->execute([":id_animal" => $idAnimal]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function animalAdminRemoverArquivoFoto(string $nomeArquivo): void
{
    if ($nomeArquivo === "" || $nomeArquivo === animalAdminPlaceholderFileName()) {
        return;
    }

    $caminho = animalAdminUploadDir() . DIRECTORY_SEPARATOR . $nomeArquivo;

    if (file_exists($caminho) && is_file($caminho)) {
        @unlink($caminho);
    }
}

function animalAdminGarantirFotoPadrao(PDO $pdo, int $idAnimal): void
{
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM foto_animal WHERE id_animal = :id_animal");
    $stmtCount->execute([":id_animal" => $idAnimal]);

    if ((int) $stmtCount->fetchColumn() > 0) {
        return;
    }

    $placeholder = animalAdminEnsurePlaceholderImage();

    $stmtInsert = $pdo->prepare("
        INSERT INTO foto_animal (id_animal, ds_img)
        VALUES (:id_animal, :ds_img)
    ");
    $stmtInsert->execute([
        ":id_animal" => $idAnimal,
        ":ds_img" => $placeholder,
    ]);
}

function animalAdminRemoverFotoPadraoSeHouver(PDO $pdo, int $idAnimal): void
{
    $stmt = $pdo->prepare("
        DELETE FROM foto_animal
        WHERE id_animal = :id_animal
          AND ds_img = :ds_img
    ");
    $stmt->execute([
        ":id_animal" => $idAnimal,
        ":ds_img" => animalAdminPlaceholderFileName(),
    ]);
}

function animalAdminNormalizarArquivosUpload(array $arquivos): array
{
    if (!isset($arquivos["name"])) {
        return [];
    }

    if (!is_array($arquivos["name"])) {
        return [[
            "name" => (string) ($arquivos["name"] ?? ""),
            "tmp_name" => (string) ($arquivos["tmp_name"] ?? ""),
            "error" => isset($arquivos["error"]) ? (int) $arquivos["error"] : UPLOAD_ERR_NO_FILE,
        ]];
    }

    $arquivosNormalizados = [];

    foreach ($arquivos["name"] as $indice => $nomeOriginal) {
        $arquivosNormalizados[] = [
            "name" => (string) $nomeOriginal,
            "tmp_name" => isset($arquivos["tmp_name"][$indice]) ? (string) $arquivos["tmp_name"][$indice] : "",
            "error" => isset($arquivos["error"][$indice]) ? (int) $arquivos["error"][$indice] : UPLOAD_ERR_NO_FILE,
        ];
    }

    return $arquivosNormalizados;
}

function animalAdminProcessarNovasFotos(array $arquivos): array
{
    animalAdminEnsureUploadDir();

    $arquivosNormalizados = animalAdminNormalizarArquivosUpload($arquivos);

    if ($arquivosNormalizados === []) {
        return [];
    }

    $extensoesPermitidas = ["jpg", "jpeg", "png", "webp", "avif"];
    $fotosSalvas = [];

    foreach ($arquivosNormalizados as $arquivo) {
        $nomeOriginal = trim((string) ($arquivo["name"] ?? ""));
        $tmpName = (string) ($arquivo["tmp_name"] ?? "");
        $erro = (int) ($arquivo["error"] ?? UPLOAD_ERR_NO_FILE);

        if ($erro !== UPLOAD_ERR_OK || $nomeOriginal === "" || $tmpName === "") {
            continue;
        }

        if (!is_uploaded_file($tmpName)) {
            continue;
        }

        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

        if (!in_array($extensao, $extensoesPermitidas, true)) {
            continue;
        }

        $nomeSeguro = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($nomeOriginal));
        $nomeArquivo = uniqid("", true) . "_" . $nomeSeguro;
        $destino = animalAdminUploadDir() . DIRECTORY_SEPARATOR . $nomeArquivo;

        if (move_uploaded_file($tmpName, $destino)) {
            $fotosSalvas[] = $nomeArquivo;
        }
    }

    return $fotosSalvas;
}

function animalAdminSalvarFotos(PDO $pdo, int $idAnimal, array $nomesArquivos): void
{
    if (empty($nomesArquivos)) {
        return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO foto_animal (id_animal, ds_img)
        VALUES (:id_animal, :ds_img)
    ");

    foreach ($nomesArquivos as $nomeArquivo) {
        $stmt->execute([
            ":id_animal" => $idAnimal,
            ":ds_img" => $nomeArquivo,
        ]);
    }
}

function animalAdminDescricaoBooleana($valor): string
{
    if ($valor === null || $valor === "") {
        return "Não informado";
    }

    return (int) $valor === 1 ? "Sim" : "Não";
}

function animalAdminClasseBooleana($valor): string
{
    if ($valor === null || $valor === "") {
        return "badge-neutro";
    }

    return (int) $valor === 1 ? "badge-positivo" : "badge-alerta";
}

function animalAdminFotoPublica(?string $nomeArquivo): string
{
    if ($nomeArquivo === null || trim($nomeArquivo) === "") {
        return animalAdminUploadPublicPath() . animalAdminEnsurePlaceholderImage();
    }

    $caminho = animalAdminUploadDir() . DIRECTORY_SEPARATOR . $nomeArquivo;

    if (!file_exists($caminho)) {
        return animalAdminUploadPublicPath() . animalAdminEnsurePlaceholderImage();
    }

    return animalAdminUploadPublicPath() . $nomeArquivo;
}
