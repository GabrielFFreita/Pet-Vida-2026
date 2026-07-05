# Estrutura do Projeto

## Pastas principais

- `config/`: conexao com banco e controle de sessao.
- `includes/`: componentes reutilizaveis e funcoes auxiliares.
- `admin/`: paginas administrativas.
- `api/`: endpoints e processamentos.
- `assets/`: CSS, JavaScript, imagens e videos fixos.
- `uploads/`: arquivos enviados no sistema.
- `database/`: scripts SQL.
- `docs/`: documentacao e material legado.

## Compatibilidade mantida

- As paginas publicas principais continuam na raiz.
- `api.php`, `solicitar_adocao.php` e `processa_upload.php` permanecem na raiz como wrappers.
- Os nomes antigos das paginas administrativas na raiz redirecionam para `admin/`.
