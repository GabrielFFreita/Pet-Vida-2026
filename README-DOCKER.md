# Dockerização — Pet Vida 2026

## Arquitetura

O Docker Compose executa somente a aplicação Pet Vida em um contêiner PHP 8.2 com Apache. O banco de dados continua sendo o MySQL remoto já existente; o Compose não cria MySQL local, volumes de banco, importações SQL nem scripts de inicialização.

A aplicação é publicada, por padrão, em `http://localhost:8080`. Os dados não sigilosos de conexão ficam no arquivo local `.env`; a senha fica separada em `secrets/db_password.txt` e é montada no contêiner como Docker secret. O MySQL Workbench pode continuar sendo usado separadamente para administrar o servidor remoto.

## Pré-requisitos

- Docker Desktop instalado e em execução, configurado para contêineres Linux.
- PowerShell.
- Acesso de rede do Docker Desktop ao servidor MySQL remoto.
- Uma porta local disponível, normalmente `8080`.

O servidor remoto pode exigir liberação do IP, regras de firewall, usuário autorizado para acesso remoto ou configuração de TLS. A configuração Docker não altera essas regras.

## Configurar o ambiente

Na raiz do projeto, crie seu arquivo local a partir do modelo:

```powershell
Set-Location 'C:\caminho\para\Pet-Vida-2026'
Copy-Item .env.example .env
notepad .env
New-Item -ItemType Directory -Force secrets
Copy-Item secrets/db_password.txt.example secrets/db_password.txt
notepad secrets/db_password.txt
```

Preencha `DB_HOST`, `DB_NAME` e `DB_USER` no `.env`. Grave somente a senha, sem aspas, em `secrets/db_password.txt`. `DB_PORT` usa `3306` como padrão e `APP_PORT` usa `8080`. Mantenha `APP_DEBUG=0` normalmente.

Não envie `.env` nem `secrets/db_password.txt` para o Git e não compartilhe suas credenciais. Ambos são ignorados pelo Git e pelo contexto de construção da imagem. O Compose monta o secret somente em `/run/secrets/db_password`, sem gravá-lo na imagem ou expô-lo como variável de ambiente.

## Validar a configuração

Depois de preencher `.env`, valide a interpolação do Compose sem iniciar contêineres:

```powershell
docker compose config
```

O comando deve listar apenas o serviço `app`. Se informar que uma variável obrigatória está ausente, complete o respectivo valor em `.env`.

## Construir e iniciar

Construa a imagem da aplicação:

```powershell
docker compose build
```

Inicie a aplicação:

```powershell
docker compose up -d
docker compose ps
```

Abra `http://localhost:8080` no navegador. O estado esperado em `docker compose ps` é `healthy`.

Para testar a conexão com o banco sem revelar a senha:

```powershell
docker compose exec -T app php -r "require '/var/www/html/config/conexao.php'; echo 'Conexao OK', PHP_EOL;"
```

## Logs e parada segura

Para consultar os logs da aplicação:

```powershell
docker compose logs --tail=100 app
```

Para parar a aplicação sem excluir imagens, contêineres ou dados remotos:

```powershell
docker compose stop
```

Os arquivos enviados pela aplicação permanecem na pasta local `uploads/`, montada no contêiner. O Compose não administra nem remove dados do banco remoto.

## Problemas de conexão remota

Se a aplicação não conseguir conectar ao banco, confira primeiro os valores preenchidos no `.env` e os logs do serviço `app`. Depois, confirme com o administrador do banco remoto que:

- o nome ou IP em `DB_HOST` é alcançável a partir do Docker Desktop;
- a porta configurada em `DB_PORT` está liberada;
- o usuário possui permissão para acessar o banco indicado em `DB_NAME` a partir da origem usada;
- as exigências de firewall, IP permitido e TLS do servidor foram atendidas.

Não execute scripts SQL, recrie bancos nem altere credenciais como tentativa de diagnóstico. Uma conexão remota só deve ser considerada confirmada após um teste real bem-sucedido com credenciais autorizadas.
