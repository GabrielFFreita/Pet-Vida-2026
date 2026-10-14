@echo off
setlocal
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
if not exist ".env" (
    echo ERRO: arquivo .env nao encontrado. Copie .env.example para .env e configure o ambiente.
    popd
    exit /b 1
)

if not exist "secrets\db_password.txt" (
    echo ERRO: secret do banco nao encontrado em secrets\db_password.txt.
    popd
    exit /b 1
)

echo Construindo a imagem PHP/Apache e iniciando o container e a network...
docker compose up -d --build --wait --wait-timeout 60
if errorlevel 1 (
    echo.
    echo ERRO: nao foi possivel criar ou iniciar o ambiente. Execute "docker compose logs" para detalhes.
    popd
    exit /b 1
)

set "APP_PORT=8080"
for /f "usebackq tokens=1,* delims==" %%A in (".env") do (
    if /I "%%A"=="APP_PORT" set "APP_PORT=%%B"
)
set "APP_URL=http://localhost:%APP_PORT%/"

echo.
echo Ambiente iniciado em %APP_URL%
echo Verifique o status com: docker compose ps

where chrome >nul 2>&1 && (
    start "" chrome "%APP_URL%"
    goto :finalizar
)

if exist "%ProgramFiles%\Google\Chrome\Application\chrome.exe" (
    start "" "%ProgramFiles%\Google\Chrome\Application\chrome.exe" "%APP_URL%"
    goto :finalizar
)

if exist "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" (
    start "" "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" "%APP_URL%"
    goto :finalizar
)

if exist "%LocalAppData%\Google\Chrome\Application\chrome.exe" (
    start "" "%LocalAppData%\Google\Chrome\Application\chrome.exe" "%APP_URL%"
    goto :finalizar
)

echo AVISO: Google Chrome nao foi encontrado. Abra manualmente: %APP_URL%

:finalizar
popd
endlocal
