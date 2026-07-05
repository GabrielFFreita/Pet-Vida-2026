# Observacoes

## Refatoracao estrutural

- As paginas publicas principais permaneceram na raiz para reduzir risco de quebra.
- O painel administrativo foi concentrado em `admin/`.
- Os assets fixos foram consolidados em `assets/`.
- Os wrappers na raiz foram mantidos para compatibilidade com rotas antigas.

## Material legado

- O conteudo antigo de `AVISO` foi preservado em `docs/legado/AVISO.txt`.
- Os prototipos HTML antigos foram movidos para `docs/legado/html/`.
- Arquivos antigos como `dados.php`, `validar_sessao.php`, `listagem-usuarios.php` e `entrar.php` foram mantidos na raiz por cautela, porque ainda podem servir como referencia tecnica durante a apresentacao ou revisao.
