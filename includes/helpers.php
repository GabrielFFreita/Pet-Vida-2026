<?php

function siteRelativePrefix(): string
{
    $scriptName = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"] ?? "");
    $scriptDir = trim(dirname($scriptName), "/.");

    return basename($scriptDir) === "admin" ? "../" : "";
}

function assetPath(string $path): string
{
    return siteRelativePrefix() . "assets/" . ltrim($path, "/");
}

function rootPath(string $path): string
{
    return siteRelativePrefix() . ltrim($path, "/");
}

function adminPath(string $path): string
{
    $normalizedPath = ltrim($path, "/");
    return basename(trim(dirname(str_replace("\\", "/", $_SERVER["SCRIPT_NAME"] ?? "")), "/.")) === "admin"
        ? $normalizedPath
        : "admin/" . $normalizedPath;
}

function renderToastFlash(?string $mensagem, string $tipo = "sucesso"): void
{
    if ($mensagem === null || $mensagem === "") {
        return;
    }

    $tipoSeguro = $tipo === "erro" ? "erro" : "sucesso";
    $mensagemJson = json_encode($mensagem, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    $tipoJson = json_encode($tipoSeguro);

    echo "<script>document.addEventListener(\"DOMContentLoaded\", function () {"
        . " if (window.mostrarToast) { mostrarToast(" . $mensagemJson . ", " . $tipoJson . "); }"
        . " });</script>\n";
}
