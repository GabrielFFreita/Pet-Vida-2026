# Fluxo do Sistema

## Área Pública

### Home

`index.php` apresenta a identidade visual do projeto e consulta animais disponíveis para montar a vitrine principal.

Responsabilidades:

- Iniciar sessão
- Consultar animais disponíveis
- Carregar foto de capa do animal
- Exibir seções institucionais e destaques

### Adoção

`adocao.php` concentra o fluxo principal de descoberta e solicitação de adoção.

Funcionalidades identificadas:

- Listagem de pets com dados de abrigo e fotos
- Filtros por abrigo, espécie, sexo, raça e texto
- Favoritos no navegador
- Visualização de detalhes do pet
- Carrossel de imagens
- Confirmação e envio da solicitação de adoção
- Carga direta do widget `VLibras` no próprio arquivo

### Quiz

`quiz.php` processa respostas do usuário para sugerir um animal compatível.

Pontos relevantes:

- Coleta respostas via interface web
- Processa regras no próprio backend PHP
- Retorna sugestão de pet conforme perfil inferido
- Carga direta do widget `VLibras` no próprio arquivo

### Autenticação e Perfil

- `cadastro.php` registra novos usuários
- `login.php` autentica por e-mail e senha
- `perfil.php` permite atualizar dados cadastrais do usuário logado
- `index.php`, `login.php`, `cadastro.php` e `perfil.php` recebem `VLibras` via `includes/header.php`

## Área Administrativa

O painel administrativo é restrito a usuários com perfil `admin` e depende de validação feita em `config/sessao.php`.

Além da autenticação, as telas administrativas compartilham a inclusão do widget `VLibras` por meio de `includes/menu-admin.php`.

### Dashboard

`admin/dashboard.php` apresenta visão operacional do sistema com métricas e gráficos.

Indicadores identificados:

- Animais disponíveis
- Animais em processo
- Abrigos cadastrados
- Pedidos pendentes
- Distribuição de status
- Distribuição por abrigo

### Gestão de Animais

Arquivos principais:

- `admin/animais.php`
- `admin/animal-cadastrar.php`
- `admin/animal-editar.php`
- `admin/animal-excluir.php`
- `admin/animal-status.php`

Operações:

- Cadastro
- Edição
- Exclusão controlada
- Alteração de status
- Gestão de múltiplas imagens

### Gestão de Abrigos

Arquivos principais:

- `admin/abrigos.php`
- `admin/abrigo-perfil.php`

Operações:

- Cadastro e listagem de abrigos
- Consulta de detalhes do abrigo
- Vinculação de animais ao abrigo

### Gestão de Usuários

Arquivos principais:

- `admin/usuarios.php`
- `admin/usuario-editar.php`
- `admin/usuario-excluir.php`

Operações:

- Listagem e métricas
- Edição de dados e perfil
- Exclusão condicionada à ausência de dependências ativas

## API Interna

O endpoint principal é exposto por `api.php`, que encaminha para `api/index.php`.

### Ações principais

- `login`
- `logout`
- `verificar_sessao`
- `cadastrar_usuario`
- `meus_dados`
- `atualizar_usuario`
- `toggle_favorito`
- `verificar_favorito`
- `listar_favoritos`
- `doar`

### Endpoint específico de adoção

`solicitar_adocao.php` encaminha para `api/solicitar-adocao.php`, responsável pelo fluxo transacional de solicitação de adoção.

## Fluxos Funcionais

### Fluxo de login

1. Usuário preenche e-mail e senha em `login.php`.
2. `assets/js/auth.js` envia JSON para `api.php?acao=login`.
3. O backend busca o usuário no banco.
4. A senha é validada com `password_verify`.
5. Em caso de sucesso, os dados são gravados na sessão.

### Fluxo de cadastro

1. Usuário preenche o formulário em `cadastro.php`.
2. `assets/js/auth.js` envia os dados para `api.php?acao=cadastrar_usuario`.
3. O backend grava o usuário com `password_hash`.

### Fluxo de adoção

1. Usuário navega por `adocao.php`.
2. Seleciona um pet e abre seus detalhes.
3. Confirma o interesse na adoção.
4. O navegador envia a solicitação para `solicitar_adocao.php`.
5. O backend registra o pedido e atualiza o status do animal em transação.

### Fluxo administrativo

1. Usuário admin autenticado acessa `admin/dashboard.php`.
2. O sistema valida o perfil `admin`.
3. O menu lateral direciona para animais, abrigos e usuários.
4. Cada tela consulta ou altera dados diretamente no banco via PDO.
