@echo off
setlocal
pushd "%~dp0" || (
    echo ERRO: nao foi possivel acessar a pasta do projeto.
    exit /b 1
)

where docker >nul 2>&1 || (
    echo ERRO: Docker nao foi encontrado no PATH.
    popd
    exit /b 1
)

docker info >nul 2>&1 || (
    echo ERRO: o Docker Engine nao esta acessivel.
    popd
    exit /b 1
)

docker compose down --remove-orphans
if errorlevel 1 (
    echo ERRO: nao foi possivel remover os containers e a network.
    popd
    exit /b 1
)

docker image inspect petvida2026-app:local >nul 2>&1 && docker image rm petvida2026-app:local

echo.
echo Container, network e imagem local removidos.
echo Os uploads locais e os dados do banco remoto nao foram alterados.

popd
endlocal
