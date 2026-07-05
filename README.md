# Pet Vida 2026

## Visão Geral

O **Pet Vida 2026** é uma aplicação web monolítica desenvolvida em **PHP, MySQL, HTML, CSS e JavaScript** para apoiar o processo de adoção de animais. O sistema reúne área pública para visitantes e usuários cadastrados, painel administrativo para gestão operacional e uma API interna em PHP para ações assíncronas.

O projeto foi organizado sem framework, com páginas PHP renderizadas no servidor, sessão nativa do PHP para autenticação e persistência relacional via **PDO**.

## Principais Funcionalidades

- Página inicial com vitrine de animais disponíveis
- Página de adoção com filtros, detalhes do pet e solicitação de adoção
- Cadastro, login e manutenção de perfil do usuário
- Quiz de compatibilidade com pets
- Favoritos e registro de intenção de doação
- Painel administrativo para gestão de animais, abrigos, usuários e métricas

## Tecnologias Identificadas

| Categoria | Tecnologia | Uso no projeto |
|---|---|---|
| Backend | PHP | Páginas, endpoints, autenticação e regras de negócio |
| Banco de dados | MySQL | Persistência de usuários, animais, abrigos e processos |
| Acesso a dados | PDO | Prepared statements e comunicação com MySQL |
| Frontend | HTML, CSS e JavaScript | Interface pública, admin e interações no navegador |
| Bibliotecas | Bootstrap, Font Awesome, Chart.js | Componentes visuais, ícones e gráficos |
| Acessibilidade | VLibras | Widget de acessibilidade |
| Upload | `move_uploaded_file` | Armazenamento local de imagens em `uploads/` |

## Arquitetura Resumida

- Aplicação web monolítica simples
- Renderização server-side com páginas PHP
- API interna em `api/index.php`
- Controle de autenticação via sessão PHP
- Persistência relacional em MySQL
- Upload local de imagens de animais

## Estrutura do Repositório

```text
Pet-Vida-2026/
├── admin/
├── api/
├── assets/
├── config/
├── database/
├── docs/
├── includes/
├── uploads/
├── index.php
├── adocao.php
├── quiz.php
├── login.php
├── cadastro.php
├── perfil.php
├── api.php
├── solicitar_adocao.php
└── README.md
```

## Documentação Complementar

- [docs/estrutura.md](/C:/Reinaldo/Projetos/Gabriel/Pet-Vida-2026/docs/estrutura.md)  
  Arquitetura, responsabilidades por pasta e visão C4 do sistema.
- [docs/fluxo-sistema.md](/C:/Reinaldo/Projetos/Gabriel/Pet-Vida-2026/docs/fluxo-sistema.md)  
  Fluxos públicos, administrativos e interações com a API.
- [docs/banco-de-dados.md](/C:/Reinaldo/Projetos/Gabriel/Pet-Vida-2026/docs/banco-de-dados.md)  
  Modelo de dados inferido, entidades e schema sugerido.
- [docs/observacoes.md](/C:/Reinaldo/Projetos/Gabriel/Pet-Vida-2026/docs/observacoes.md)  
  Segurança, integrações, validações, riscos e oportunidades de evolução.

## Páginas Principais

| URL | Finalidade |
|---|---|
| `/index.php` | Página inicial |
| `/adocao.php` | Listagem e solicitação de adoção |
| `/quiz.php` | Quiz de compatibilidade |
| `/cadastro.php` | Cadastro de usuário |
| `/login.php` | Autenticação |
| `/perfil.php` | Perfil do usuário autenticado |
| `/admin/dashboard.php` | Dashboard administrativo |

## Como Executar Localmente

### Pré-requisitos

- PHP instalado
- MySQL disponível
- Servidor local como XAMPP, WAMP, Laragon, Apache ou Nginx com PHP

### Passos

1. Publicar a pasta do projeto no diretório do servidor local.
2. Criar o banco de dados MySQL.
3. Ajustar as credenciais em `config/conexao.php`.
4. Criar as tabelas com base no schema oficial ou no modelo inferido em `docs/banco-de-dados.md`.
5. Garantir permissão de escrita em `uploads/`.
6. Acessar `http://localhost/Pet-Vida-2026/`.

## Convenções Importantes

- Páginas públicas principais permanecem na raiz
- Painel administrativo permanece em `admin/`
- Assets fixos ficam em `assets/`
- Uploads dinâmicos ficam em `uploads/`
- Wrappers `api.php` e `solicitar_adocao.php` foram mantidos por compatibilidade

## Material Legado

O conteúdo histórico mantido apenas para referência está em [docs/legado/](/C:/Reinaldo/Projetos/Gabriel/Pet-Vida-2026/docs/legado).
