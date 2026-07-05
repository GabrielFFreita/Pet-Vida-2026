# Observações Técnicas

## Segurança e Controle de Acesso

### Autenticação

- Login por e-mail e senha
- Validação de senha com `password_verify`
- Cadastro com `password_hash`

### Sessão

- Sessão PHP nativa
- Dados como `id_usuario`, `nome_usuario`, `email`, `perfil` e `ultima_atividade`
- Expiração por inatividade de 30 minutos

### Autorização

- Área administrativa protegida por `verificarAdmin()`
- Operações autenticadas dependem da sessão ativa

### Banco de Dados

- Uso predominante de prepared statements
- Comunicação via PDO com MySQL

## Upload de Imagens

### Fluxo principal

- Upload local em `uploads/`
- Uso de `move_uploaded_file(...)`
- Persistência do nome do arquivo em `foto_animal.ds_img`

### Fallback e legado

- Imagem padrão `not_image.png` quando não há foto cadastrada
- Endpoint legado `api/upload.php` com exemplo de integração com ImgBB

## Frontend e Arquivos Relevantes

### JavaScript

| Arquivo | Responsabilidade |
|---|---|
| `assets/js/site.js` | Sessão visual, carrossel, favoritos, modais e interações gerais |
| `assets/js/auth.js` | Cadastro e login |
| `assets/js/perfil.js` | Leitura e atualização de perfil |
| `assets/js/adocao.js` | Filtros, detalhes do pet e confirmação de adoção |
| `assets/js/quiz.js` | Interatividade do quiz |

### CSS

| Arquivo | Responsabilidade |
|---|---|
| `assets/css/site.css` | Estilo geral do site |
| `assets/css/adocao.css` | Estilo da página de adoção |
| `assets/css/admin.css` | Estilo do painel administrativo |
| `assets/css/quiz.css` | Estilo do quiz |
| `assets/css/procura.css` | Estilos auxiliares de busca |

## Integrações Externas

| Recurso | Finalidade |
|---|---|
| Bootstrap CDN | Apoio visual de componentes |
| Font Awesome | Ícones |
| Google Fonts | Tipografia |
| VLibras | Acessibilidade em páginas públicas e no painel administrativo |
| Chart.js | Gráficos do dashboard |
| ImgBB | Upload legado externo |

## Cobertura de Acessibilidade

- O `VLibras` está ativo nas páginas públicas com layout compartilhado por meio de `includes/header.php`.
- As páginas `adocao.php` e `quiz.php` possuem inclusão explícita do widget no próprio arquivo.
- O painel administrativo recebe o `VLibras` a partir de `includes/menu-admin.php`, evitando repetição nas telas internas.

## Pontos Fortes para Apresentação

- Separação de responsabilidades por pasta
- Uso de PDO com prepared statements
- Senhas armazenadas com hash
- Controle de sessão com perfis `user` e `admin`
- Fluxo real de adoção com persistência em banco
- Dashboard administrativo com métricas e gráficos
- Upload múltiplo de imagens para animais

## Oportunidades de Evolução

| Ponto | Situação atual | Evolução sugerida |
|---|---|---|
| Schema SQL | `schema.sql` e `seed.sql` estão incompletos | Criar schema oficial do projeto |
| Credenciais | Configuradas em arquivo PHP | Migrar para variáveis de ambiente |
| CORS | Indícios de configuração ampla | Restringir origens em produção |
| Exibição de erros | Ambiente pode expor erros diretamente | Desativar `display_errors` em produção |
| CSRF | Não há padronização visível | Adicionar tokens CSRF em operações POST |
| Upload | Validação parcial | Validar MIME type, tamanho e nome de arquivo |
| Modelagem | Há nomes legados coexistindo | Consolidar nomenclatura de tabelas e fluxos |

## Validação Técnica Executada

Segundo a documentação-base analisada:

- Foi executado `php -l`
- Foram analisados 29 arquivos PHP
- Não foram identificados erros de sintaxe

## Material Legado

- `docs/legado/AVISO.txt` preserva notas históricas
- `docs/legado/html/` mantém protótipos HTML antigos
- Esse material deve ser tratado como referência, não como documentação principal
