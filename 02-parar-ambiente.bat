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

docker compose stop
if errorlevel 1 (
    echo ERRO: nao foi possivel parar o ambiente.
    popd
    exit /b 1
)

echo Ambiente parado. Os uploads continuam na pasta local uploads e o banco remoto nao foi alterado.
popd
endlocal
