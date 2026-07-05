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
