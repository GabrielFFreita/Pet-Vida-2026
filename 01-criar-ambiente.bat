@echo off
setlocal
REM 
REM 
pushd "%~dp0" || (
    echo ERRO: nao foi possivel acessar a pasta do projeto.
    exit /b 1
)

where docker >nul 2>&1 || (
    echo ERRO: Docker nao foi encontrado no PATH. Instale/inicie o Docker Desktop.
    popd
    exit /b 1
)

docker info >nul 2>&1 || (
    echo ERRO: o Docker Engine nao esta acessivel. Abra o Docker Desktop e aguarde iniciar.
    popd
    exit /b 1
)

docker compose version >nul 2>&1 || (
    echo ERRO: Docker Compose nao esta disponivel. Atualize o Docker Desktop.
    popd
    exit /b 1
)

echo.
echo Construindo a imagem PHP/Apache e iniciando MySQL, containers e network...
docker compose up -d --build
if errorlevel 1 (
    echo.
    echo ERRO: nao foi possivel criar ou iniciar o ambiente. Execute "docker compose logs" para detalhes.
    popd
    exit /b 1
)

echo.
echo Ambiente iniciado. Acesse: http://localhost:8080/
echo Verifique o status com: docker compose ps
popd
endlocal
