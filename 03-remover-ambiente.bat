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
echo Containers, network e imagem local removidos. O volume petvida2026_mysql_data foi preservado.
set /p REMOVER_VOLUME="Remover tambem o volume do banco e TODOS os dados? Digite SIM para confirmar: "
if /I "%REMOVER_VOLUME%"=="SIM" (
    docker volume rm petvida2026_mysql_data
    if errorlevel 1 (
        echo AVISO: nao foi possivel remover o volume. Verifique se ele nao esta em uso.
    ) else (
        echo Volume e dados do banco removidos.
    )
) else (
    echo Volume preservado.
)

popd
endlocal
