# Pet Vida 2026

Projeto em PHP, MySQL, HTML, CSS e JavaScript para gerenciamento de adocao de animais.

## Visao Geral

O sistema possui:

- area publica para navegacao, adocao, quiz, login, cadastro e perfil
- area administrativa para gestao de animais, abrigos e usuarios
- API simples em PHP para autenticacao, sessao, favoritos, perfil e acoes auxiliares
- organizacao de assets e includes sem uso de framework

O projeto foi reorganizado para uma estrutura mais simples e previsivel, mantendo as paginas publicas principais na raiz.

## Tecnologias

- PHP
- MySQL
- HTML
- CSS
- JavaScript
- PDO para acesso ao banco

## Estrutura Final

```text
Pet-Vida-2026/
├── config/
│   ├── conexao.php
│   └── sessao.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── menu-admin.php
│   ├── helpers.php
│   └── animal-admin-helpers.php
├── admin/
│   ├── dashboard.php
│   ├── animais.php
│   ├── animal-cadastrar.php
│   ├── animal-editar.php
│   ├── animal-excluir.php
│   ├── animal-status.php
│   ├── abrigos.php
│   ├── abrigo-perfil.php
│   ├── usuarios.php
│   ├── usuario-editar.php
│   └── usuario-excluir.php
├── api/
│   ├── index.php
│   ├── solicitar-adocao.php
│   └── upload.php
├── assets/
│   ├── css/
│   ├── js/
│   ├── img/
│   └── video/
├── uploads/
│   └── .gitkeep
├── database/
│   ├── schema.sql
│   └── seed.sql
├── docs/
│   ├── estrutura.md
│   ├── banco-de-dados.md
│   ├── fluxo-sistema.md
│   ├── observacoes.md
│   └── legado/
├── index.php
├── adocao.php
├── quiz.php
├── login.php
├── cadastro.php
├── perfil.php
├── api.php
├── solicitar_adocao.php
├── README.md
└── .gitignore
```

## Responsabilidade de Cada Pasta

### `config/`

Arquivos centrais de infraestrutura:

- `conexao.php`: conexao PDO com o banco MySQL
- `sessao.php`: inicio e validacao de sessao, incluindo verificacao de usuario logado e admin

### `includes/`

Arquivos reutilizaveis do sistema:

- `header.php`: cabecalho padrao das paginas publicas
- `footer.php`: rodape padrao das paginas publicas
- `menu-admin.php`: menu lateral compartilhado do painel administrativo
- `helpers.php`: funcoes de caminho para assets, raiz e admin
- `animal-admin-helpers.php`: utilitarios de upload, placeholder e imagens de animais no admin

### `admin/`

Paginas administrativas:

- `dashboard.php`: visao geral do painel
- `animais.php`: listagem administrativa de animais
- `animal-cadastrar.php`: cadastro administrativo de animal
- `animal-editar.php`: edicao de animal
- `animal-excluir.php`: exclusao de animal
- `animal-status.php`: alteracao de status do animal
- `abrigos.php`: listagem e cadastro de abrigos
- `abrigo-perfil.php`: detalhes do abrigo e animais vinculados
- `usuarios.php`: listagem de usuarios
- `usuario-editar.php`: edicao de usuario
- `usuario-excluir.php`: exclusao de usuario

### `api/`

Endpoints PHP:

- `index.php`: endpoint principal para acoes via `api.php?acao=...`
- `solicitar-adocao.php`: fluxo de solicitacao de adocao
- `upload.php`: fluxo legado de upload

### `assets/`

Arquivos estaticos fixos do projeto.

#### `assets/css/`

- `site.css`: estilos gerais do site
- `admin.css`: estilos do painel administrativo
- `adocao.css`: estilos da pagina de adocao
- `quiz.css`: estilos especificos do quiz
- `procura.css`: estilos auxiliares de busca

#### `assets/js/`

- `site.js`: scripts principais do site
- `auth.js`: login e cadastro
- `perfil.js`: atualizacao de perfil
- `adocao.js`: interacao da pagina de adocao
- `quiz.js`: scripts auxiliares do quiz

#### `assets/img/`

Organizacao por tipo:

- `logo/`: logos do projeto
- `banners/`: banners da home e do quiz
- `equipe/`: imagens da equipe
- `qrcode/`: QR code de doacao
- `pets-exemplo/`: imagens fixas de exemplo

#### `assets/video/`

- videos fixos usados na interface

### `uploads/`

Arquivos enviados pelo sistema:

- fotos de animais
- imagens enviadas por formularios administrativos

Essa pasta nao deve ser usada para assets fixos.

### `database/`

- `schema.sql`: estrutura inicial do banco
- `seed.sql`: carga inicial de dados

### `docs/`

Documentacao complementar:

- `estrutura.md`
- `banco-de-dados.md`
- `fluxo-sistema.md`
- `observacoes.md`
- `legado/`: materiais antigos mantidos apenas para referencia

## Paginas Publicas na Raiz

As paginas principais continuam na raiz para evitar quebra de links, formularios e chamadas JavaScript:

- `index.php`
- `adocao.php`
- `quiz.php`
- `login.php`
- `cadastro.php`
- `perfil.php`

## Compatibilidade Mantida

Dois arquivos continuam na raiz como ponto de entrada conhecido:

- `api.php`
- `solicitar_adocao.php`

Eles funcionam como wrappers para:

- `api/index.php`
- `api/solicitar-adocao.php`

Isso preserva chamadas antigas de JavaScript e formularios que ainda usam os nomes anteriores.

## Fluxos Principais

### Fluxo Publico

1. O usuario acessa `index.php`.
2. Pode navegar para `adocao.php` ou `quiz.php`.
3. Pode criar conta em `cadastro.php`.
4. Pode autenticar em `login.php`.
5. Depois do login, pode atualizar dados em `perfil.php`.

### Fluxo de Adocao

1. Os animais sao exibidos em `adocao.php`.
2. A listagem e detalhes podem consumir `api.php?acao=...`.
3. A solicitacao de adocao pode usar `solicitar_adocao.php`.

### Fluxo Administrativo

1. Usuario admin autenticado acessa `admin/dashboard.php`.
2. O menu lateral leva para animais, abrigos e usuarios.
3. O admin pode cadastrar, editar, excluir e alterar status conforme a tela.

## Acoes da API

O endpoint principal funciona em:

```text
api.php?acao=...
```

Exemplos de acoes presentes no projeto:

- `listar_animais`
- `buscar_animal`
- `solicitar_adocao`
- `cadastrar_usuario`
- `login`
- `logout`
- `verificar_sessao`
- `meus_dados`
- `atualizar_usuario`
- `doar`
- `toggle_favorito`
- `verificar_favorito`
- `listar_favoritos`

## Como Executar

### 1. Requisitos

- PHP instalado
- MySQL em execucao
- servidor local como XAMPP, WAMP ou Apache/Nginx com PHP

### 2. Configurar banco

Edite:

- `config/conexao.php`

Ajuste host, nome do banco, usuario e senha.

### 3. Estrutura do banco

Use os arquivos:

- `database/schema.sql`
- `database/seed.sql`

Se necessario, substitua os placeholders por scripts reais do projeto.

### 4. Publicar localmente

Coloque a pasta do projeto no servidor local, por exemplo:

```text
http://localhost/Pet-Vida-2026/
```

## Convencoes Importantes

- includes e requires devem preferir `__DIR__`
- assets fixos devem ficar em `assets/`
- uploads dinamicos devem ficar em `uploads/`
- paginas publicas principais devem permanecer na raiz
- painel administrativo deve permanecer dentro de `admin/`

## Arquivos Importantes para Manutencao

- [config/conexao.php](C:/Reinaldo/Projetos/Gabriel/Pet-Vida-2026/config/conexao.php:1)
- [config/sessao.php](C:/Reinaldo/Projetos/Gabriel/Pet-Vida-2026/config/sessao.php:1)
- [includes/helpers.php](C:/Reinaldo/Projetos/Gabriel/Pet-Vida-2026/includes/helpers.php:1)
- [includes/menu-admin.php](C:/Reinaldo/Projetos/Gabriel/Pet-Vida-2026/includes/menu-admin.php:1)
- [api/index.php](C:/Reinaldo/Projetos/Gabriel/Pet-Vida-2026/api/index.php:1)
- [assets/js/site.js](C:/Reinaldo/Projetos/Gabriel/Pet-Vida-2026/assets/js/site.js:1)

## Uploads e Versionamento

Os uploads enviados pelo sistema nao devem ser versionados.

Configuracao atual em `.gitignore`:

```gitignore
uploads/*
!uploads/.gitkeep
```

## Validacao Tecnica Executada

Foi executada validacao de sintaxe PHP com:

```text
php -l
```

Sem erros de sintaxe nos arquivos PHP restantes da estrutura final.

## Observacoes

- O projeto permanece simples, didatico e sem framework.
- A pasta `docs/legado/` guarda material antigo apenas para consulta.
- Se novas rotas ou paginas forem criadas, elas devem seguir essa mesma organizacao.
