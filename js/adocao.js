/* ============================================================
   ADOTE COM AMOR — adocao.js
   Organizado em módulos funcionais, sem onclick inline.
   Inclui: Favoritos (localStorage), Filtros em barra horizontal,
           Status de adoção em tempo real.
   [ALTERADO #2] Novo filtro por sexo (Macho/Fêmea)
   [ALTERADO #3] Botão coração com animação bounce
   [ALTERADO #4] Toggle favoritos inline (sem sidebar)
   ============================================================ */

'use strict';

// ── Estado global ────────────────────────────────────────────
let petEscolhido    = null;
let petCardAtivo    = null;
let carrosselFotos  = [];
let indiceFotoAtual = 0;

// [ALTERADO #2] filtrosAtivos agora inclui sexo
// [ALTERADO #8] e agora também raça (pill), igual abrigo/tipo/sexo
let filtrosAtivos   = { abrigo: 'todos', tipo: 'todos', sexo: 'todos', raca: 'todos' };

let favoritos       = [];
// [ALTERADO #4] estado do toggle de favoritos
let mostrandoFavoritos = false;

// ── Dados dos abrigos (vindos do banco de verdade, injetados pelo PHP) ──
const abrigos = window.ABRIGOS_DADOS || {};

// ── Helpers ──────────────────────────────────────────────────
function qs(selector, root = document) { return root.querySelector(selector); }
function qsa(selector, root = document) { return [...root.querySelectorAll(selector)]; }

function setAriaHidden(el, hidden) {
  el.setAttribute('aria-hidden', hidden ? 'true' : 'false');
}

// ── Inicialização ────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  carregarFavoritos();
  initImgLoading();
  initBusca();
  initFiltros();
  initFavCard();
  initToggleFavoritos();  // [ALTERADO #4]
  initCarrossel();
  initDetalhes();
  initModais();
  initNavMobile();
  aplicarFiltros();
  atualizarContadorFavoritos();
});

// ── Loading de imagens ───────────────────────────────────────
function initImgLoading() {
  qsa('.pet-card img').forEach(img => {
    const loader = img.previousElementSibling;
    if (!loader || !loader.classList.contains('card-img-loader')) return;

    if (img.complete) {
      loader.style.display = 'none';
    } else {
      img.addEventListener('load',  () => { loader.style.display = 'none'; });
      img.addEventListener('error', () => { loader.style.display = 'none'; });
    }
  });
}

// ── Barra de busca ───────────────────────────────────────────
function initBusca() {
  const input    = qs('#busca-pet');
  const btnLimpar = qs('#busca-limpar');

  input.addEventListener('input', () => {
    const temValor = input.value.trim().length > 0;
    btnLimpar.hidden = !temValor;
    aplicarFiltros();
  });

  btnLimpar.addEventListener('click', () => {
    input.value  = '';
    btnLimpar.hidden = true;
    input.focus();
    aplicarFiltros();
  });
}

// ── Filtros horizontais (barra de pills) ─────────────────────
function initFiltros() {
  // Filtros por abrigo
  qsa('[data-filtro-abrigo]').forEach(btn => {
    btn.addEventListener('click', () => {
      filtrosAtivos.abrigo = btn.dataset.filtroAbrigo;
      atualizarBotoesAtivos('abrigo', btn);
      aplicarFiltros();
    });
  });

  // Filtros por tipo
  qsa('[data-filtro-tipo]').forEach(btn => {
    btn.addEventListener('click', () => {
      filtrosAtivos.tipo = btn.dataset.filtroTipo;
      atualizarBotoesAtivos('tipo', btn);
      aplicarFiltros();
    });
  });

  // [ALTERADO #2] Filtros por sexo
  qsa('[data-filtro-sexo]').forEach(btn => {
    btn.addEventListener('click', () => {
      filtrosAtivos.sexo = btn.dataset.filtroSexo;
      atualizarBotoesAtivos('sexo', btn);
      aplicarFiltros();
    });
  });

  // [ALTERADO #8] Filtros por raça (pills gerados dinamicamente pelo PHP)
  qsa('[data-filtro-raca]').forEach(btn => {
    btn.addEventListener('click', () => {
      filtrosAtivos.raca = btn.dataset.filtroRaca;
      atualizarBotoesAtivos('raca', btn);
      aplicarFiltros();
    });
  });

  // Limpar filtros
  qs('#btn-limpar-filtros').addEventListener('click', limparFiltros);
  qs('#btn-mostrar-todos').addEventListener('click', limparFiltros);
}

function atualizarBotoesAtivos(grupo, btnAtivo) {
  let seletor;
  if (grupo === 'abrigo') seletor = '[data-filtro-abrigo]';
  else if (grupo === 'tipo') seletor = '[data-filtro-tipo]';
  else if (grupo === 'sexo') seletor = '[data-filtro-sexo]'; // [ALTERADO #2]
  else seletor = '[data-filtro-raca]'; // [ALTERADO #8]

  qsa(seletor).forEach(b => b.classList.remove('filtro-ativo'));
  btnAtivo.classList.add('filtro-ativo');
}

function limparFiltros() {
  filtrosAtivos = { abrigo: 'todos', tipo: 'todos', sexo: 'todos', raca: 'todos' }; // [ALTERADO #2, #8]

  qs('#filtro-todos').classList.add('filtro-ativo');
  qs('#filtro-tipo-todos').classList.add('filtro-ativo');
  qs('#filtro-sexo-todos').classList.add('filtro-ativo'); // [ALTERADO #2]
  const filtroRacaTodos = qs('#filtro-raca-todos'); // [ALTERADO #8] só existe se houver raças
  if (filtroRacaTodos) filtroRacaTodos.classList.add('filtro-ativo');

  qsa('[data-filtro-abrigo]:not(#filtro-todos)').forEach(b => b.classList.remove('filtro-ativo'));
  qsa('[data-filtro-tipo]:not(#filtro-tipo-todos)').forEach(b => b.classList.remove('filtro-ativo'));
  qsa('[data-filtro-sexo]:not(#filtro-sexo-todos)').forEach(b => b.classList.remove('filtro-ativo')); // [ALTERADO #2]
  qsa('[data-filtro-raca]:not(#filtro-raca-todos)').forEach(b => b.classList.remove('filtro-ativo')); // [ALTERADO #8]

  // [ALTERADO #4] Desativa toggle favoritos ao limpar filtros
  if (mostrandoFavoritos) {
    mostrandoFavoritos = false;
    atualizarBotaoFavoritos();
  }

  const input = qs('#busca-pet');
  input.value = '';
  qs('#busca-limpar').hidden = true;

  aplicarFiltros();
}

// ── Aplicar filtros + busca combinados ───────────────────────
// [ALTERADO #2, #4] Agora inclui filtro de sexo e toggle favoritos
function aplicarFiltros() {
  const termo = qs('#busca-pet').value.toLowerCase().trim();
  const cards = qsa('.pet-card');
  let algumVisivel = false;

  cards.forEach((card, i) => {
    const matchAbrigo = filtrosAtivos.abrigo === 'todos'
      || card.dataset.abrigo === filtrosAtivos.abrigo;

    const matchTipo = filtrosAtivos.tipo === 'todos'
      || card.classList.contains(filtrosAtivos.tipo);

    const nome  = (card.dataset.nome || '').toLowerCase();
    const raca  = (card.dataset.raca || '').toLowerCase();
    const matchBusca = termo === '' || nome.includes(termo) || raca.includes(termo);

    // [ALTERADO #2] Filtro por sexo — normaliza para comparar
    const sexoCard = (card.dataset.sexo || '').toLowerCase().trim();
    let matchSexo = true;
    if (filtrosAtivos.sexo === 'macho') {
      matchSexo = sexoCard === 'macho';
    } else if (filtrosAtivos.sexo === 'femea') {
      // Considera "fêmea" e "femea"
      matchSexo = sexoCard === 'fêmea' || sexoCard === 'femea';
    }

    // [ALTERADO #8] Filtro por raça (pill) — comparação exata, sem diferenciar
    // maiúsculas/minúsculas, já que o valor do pill vem do mesmo campo do banco
    // que preenche data-raca no card.
    const matchRaca = filtrosAtivos.raca === 'todos'
      || raca === filtrosAtivos.raca.toLowerCase();

    // [ALTERADO #4] Filtro de favoritos inline
    const matchFavoritos = !mostrandoFavoritos || favoritos.includes(card.dataset.id);

    const visivel = matchAbrigo && matchTipo && matchBusca && matchSexo && matchRaca && matchFavoritos;

    if (visivel) {
      card.style.display = 'flex';
      card.classList.remove('visivel');
      void card.offsetWidth;
      card.style.animationDelay = `${i * 40}ms`;
      card.classList.add('visivel');
      algumVisivel = true;
    } else {
      card.style.display = 'none';
      card.classList.remove('visivel');
    }
  });

  const semRes = qs('.sem-resultados');
  semRes.style.display = algumVisivel ? 'none' : 'flex';
}

// ══════════════════════════════════════════════════════════════
// ── SISTEMA DE FAVORITOS ─────────────────────────────────────
// ══════════════════════════════════════════════════════════════

function carregarFavoritos() {
  try {
    const saved = localStorage.getItem('adocao_favoritos');
    favoritos = saved ? JSON.parse(saved) : [];
  } catch (e) {
    favoritos = [];
  }

  // [FIX] Remove IDs "fantasmas": favoritos salvos que não correspondem
  // a nenhum pet-card realmente presente na página atual (ex.: animal
  // removido do banco, ou banco resetado entre testes). Sem essa poda,
  // o contador soma esses IDs mesmo sem nenhum coração marcado.
  const idsNaPagina = new Set(qsa('.pet-card').map(card => card.dataset.id));
  const tamanhoOriginal = favoritos.length;
  favoritos = favoritos.filter(id => idsNaPagina.has(id));
  if (favoritos.length !== tamanhoOriginal) {
    salvarFavoritos();
  }

  qsa('.btn-fav-card').forEach(btn => {
    const id = btn.dataset.id;
    if (favoritos.includes(id)) {
      btn.classList.add('favoritado');
      btn.setAttribute('aria-label', `Remover ${getNomePetPorId(id)} dos favoritos`);
    }
  });
}

function salvarFavoritos() {
  try {
    localStorage.setItem('adocao_favoritos', JSON.stringify(favoritos));
  } catch (e) {
    console.warn('Não foi possível salvar favoritos:', e);
  }
}

function getNomePetPorId(id) {
  const card = qs(`.pet-card[data-id="${id}"]`);
  return card ? (card.dataset.nome || 'Pet') : 'Pet';
}

// [ALTERADO #7] Atualiza o contador e dispara animação de "pop" quando há mudança
function atualizarContadorFavoritos() {
  const count = qs('#favoritos-count');
  if (!count) return;

  if (favoritos.length > 0) {
    count.textContent = favoritos.length;
    count.hidden = false;
    // Animação sutil de pop ao adicionar/remover favorito
    count.classList.remove('pop');
    void count.offsetWidth; // reflow para reiniciar a animação
    count.classList.add('pop');
  } else {
    // [FIX] Zera o texto além de esconder — assim, mesmo que algum CSS
    // externo volte a sobrescrever o comportamento de [hidden], nunca vai
    // sobrar um número antigo ("1") congelado no contador.
    count.textContent = '0';
    count.hidden = true;
  }
}

// [ALTERADO #3] Botão coração com animação bounce
function initFavCard() {
  qs('#lista-pets').addEventListener('click', e => {
    const btn = e.target.closest('.btn-fav-card');
    if (!btn) return;

    e.stopPropagation();
    const id = btn.dataset.id;
    toggleFavorito(id, btn);
  });
}

function toggleFavorito(id, btn) {
  const nome = getNomePetPorId(id);
  const idx  = favoritos.indexOf(id);

  // [ALTERADO #3] Animação bounce ao clicar
  btn.classList.remove('clicado');
  void btn.offsetWidth; // reflow
  btn.classList.add('clicado');
  btn.addEventListener('animationend', () => btn.classList.remove('clicado'), { once: true });

  if (idx === -1) {
    favoritos.push(id);
    btn.classList.add('favoritado');
    btn.setAttribute('aria-label', `Remover ${nome} dos favoritos`);
  } else {
    favoritos.splice(idx, 1);
    btn.classList.remove('favoritado');
    btn.setAttribute('aria-label', `Favoritar ${nome}`);

    // [ALTERADO #4] Se estava no modo favoritos, re-aplica filtro para esconder o card
    if (mostrandoFavoritos) {
      aplicarFiltros();
    }
  }

  salvarFavoritos();
  atualizarContadorFavoritos();

  // [FIX] Se a lista de favoritos zerou e o filtro "mostrar favoritos" está
  // ativo, desativa o filtro automaticamente para não deixar a grade vazia.
  if (favoritos.length === 0 && mostrandoFavoritos) {
    mostrandoFavoritos = false;
    atualizarBotaoFavoritos();
    aplicarFiltros();
  }
}

// ── [ALTERADO #4] Toggle de Favoritos Inline ─────────────────
// Sem sidebar, sem popup. Ativa/desativa filtro inline na grade.
function initToggleFavoritos() {
  const btn = qs('#btn-favoritos');
  if (!btn) return;

  btn.addEventListener('click', () => {
    mostrandoFavoritos = !mostrandoFavoritos;
    atualizarBotaoFavoritos();
    aplicarFiltros();
  });
}

function atualizarBotaoFavoritos() {
  const btn = qs('#btn-favoritos');
  if (!btn) return;

  if (mostrandoFavoritos) {
    btn.classList.add('ativo');
    btn.setAttribute('aria-pressed', 'true');
    btn.setAttribute('aria-label', 'Mostrar todos os pets');
  } else {
    btn.classList.remove('ativo');
    btn.setAttribute('aria-pressed', 'false');
    btn.setAttribute('aria-label', 'Mostrar apenas favoritos');
  }
}

// Mantém funções legadas para não quebrar nada (sidebar desativada visualmente)
function removerFavoritoById(id) {
  const idx = favoritos.indexOf(id);
  if (idx !== -1) favoritos.splice(idx, 1);
  salvarFavoritos();
  atualizarContadorFavoritos();

  const btn = qs(`.btn-fav-card[data-id="${id}"]`);
  if (btn) {
    btn.classList.remove('favoritado');
    const nome = getNomePetPorId(id);
    btn.setAttribute('aria-label', `Favoritar ${nome}`);
  }
}

// ── Modal de Favoritos (legado, mantido inativo) ─────────────
function initModalFavoritos() { /* desativado pelo #4 */ }
function abrirModalFavoritos() { /* desativado */ }
function fecharModalFavoritos() { /* desativado */ }
function renderizarFavoritos() { /* desativado */ }

// ══════════════════════════════════════════════════════════════
// ── DETALHES DO PET ──────────────────────────────────────────
// ══════════════════════════════════════════════════════════════

function initDetalhes() {
  qs('#lista-pets').addEventListener('click', e => {
    const btn = e.target.closest('.btn-detalhes');
    if (btn) verDetalhes(btn.closest('.pet-card'));
  });

  qs('#btn-voltar-det').addEventListener('click', voltarParaLista);
}

function verDetalhes(card) {
  petCardAtivo = card;
  const abrigoId = card.dataset.abrigo;
  let fotos = [];

  try {
    fotos = JSON.parse(card.dataset.fotos || '[]');
  } catch (error) {
    fotos = [];
  }

  if (!Array.isArray(fotos) || fotos.length === 0) {
    fotos = ['uploads/not_image.png'];
  }

  petEscolhido = {
    id:         card.dataset.id          || '',
    nome:       card.dataset.nome        || 'Sem nome',
    abrigoId,
    localizacao: abrigos[abrigoId]?.nome || 'Não informado',
    tipo:       card.dataset.tipo        || 'Não informado',
    raca:       card.dataset.raca        || 'Não informada',
    sexo:       card.dataset.sexo        || 'Não informado',
    weight:     card.dataset.peso        || 'Não informado',
    idade:      card.dataset.idade       || 'Não informada',
    porte:      card.dataset.porte       || 'Não informado',
    vacinado:   card.dataset.vacinado    || 'Sim',
    castrado:   card.dataset.castrado    || 'Sim',
    deficiencia: card.dataset.deficiencia || 'Nenhuma',
    status:     card.dataset.status      || 'Disponível',
    descricao:  card.dataset.descricao   || 'Pet especial aguardando um lar.',
    fotos
  };

  qs('#detalhes-nome').textContent        = petEscolhido.nome;
  qs('#detalhes-tipo').textContent        = petEscolhido.tipo;
  qs('#detalhes-raca').textContent        = petEscolhido.raca;
  qs('#detalhes-sexo').textContent        = petEscolhido.sexo;
  qs('#detalhes-peso').textContent        = petEscolhido.peso;
  qs('#detalhes-idade').textContent       = petEscolhido.idade;
  qs('#detalhes-porte').textContent       = petEscolhido.porte;
  qs('#detalhes-vacinado').textContent    = petEscolhido.vacinado;
  qs('#detalhes-castrado').textContent    = petEscolhido.castrado;
  qs('#detalhes-deficiencia').textContent = petEscolhido.deficiencia;
  qs('#detalhes-localizacao').textContent = petEscolhido.localizacao;
  qs('#detalhes-descricao').textContent   = petEscolhido.descricao;

  atualizarStatusBadge(petEscolhido.status);

  carrosselFotos  = petEscolhido.fotos;
  indiceFotoAtual = 0;
  carregarCarrossel();

  const tela = qs('#detalhes-pet');
  tela.style.display = 'block';
  setAriaHidden(tela, false);
  document.body.style.overflow = 'hidden';
  tela.scrollTo(0, 0);
  qs('#btn-voltar-det').focus();
}

function atualizarStatusBadge(status) {
  const badge = qs('#detalhes-status');
  badge.textContent = status;
  badge.className   = 'det-status-badge';

  if (status.toLowerCase().includes('disponível')) {
    badge.classList.add('disponivel');
  } else if (status.toLowerCase().includes('processo') || status.toLowerCase().includes('solicitação')) {
    badge.classList.add('em-adocao');
  } else if (status.toLowerCase().includes('adotado')) {
    badge.classList.add('adotado');
  }
}

function voltarParaLista() {
  const tela = qs('#detalhes-pet');
  tela.style.display = 'none';
  setAriaHidden(tela, true);
  document.body.style.overflow = '';
}

// ── Carrossel ────────────────────────────────────────────────
function initCarrossel() {
  qs('#btn-anterior').addEventListener('click', fotoAnterior);
  qs('#btn-proximo').addEventListener('click',  fotoProxima);

  const cont = qs('#carrossel-imagens');
  let startX = 0;

  cont.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
  cont.addEventListener('touchend', e => {
    const diff = startX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 40) diff > 0 ? fotoProxima() : fotoAnterior();
  });

  cont.addEventListener('click', e => {
    if (e.target.tagName === 'IMG') fotoProxima();
  });
}

function carregarCarrossel() {
  const cont  = qs('#carrossel-imagens');
  const indic = qs('#carrossel-indicadores');
  const btnAnterior = qs('#btn-anterior');
  const btnProximo = qs('#btn-proximo');

  cont.innerHTML  = '';
  indic.innerHTML = '';

  if (!Array.isArray(carrosselFotos) || carrosselFotos.length === 0) {
    carrosselFotos = ['uploads/not_image.png'];
  }

  carrosselFotos.forEach((src, i) => {
    const img = document.createElement('img');
    img.src   = src;
    img.alt   = `Foto ${i + 1} de ${petEscolhido?.nome || 'pet'}`;
    if (i === 0) img.classList.add('ativa');
    cont.appendChild(img);

    const dot = document.createElement('button');
    dot.className = 'indicador' + (i === 0 ? ' ativo' : '');
    dot.setAttribute('role', 'tab');
    dot.setAttribute('aria-label', `Foto ${i + 1}`);
    dot.setAttribute('aria-selected', i === 0 ? 'true' : 'false');
    dot.addEventListener('click', () => irParaFoto(i));
    indic.appendChild(dot);
  });

  const temVariasFotos = carrosselFotos.length > 1;
  if (btnAnterior) btnAnterior.style.display = temVariasFotos ? '' : 'none';
  if (btnProximo) btnProximo.style.display = temVariasFotos ? '' : 'none';
  indic.style.display = temVariasFotos ? '' : 'none';

  indiceFotoAtual = 0;
}

function atualizarCarrossel() {
  const imagens = qsa('#carrossel-imagens img');
  const dots    = qsa('#carrossel-indicadores .indicador');

  imagens.forEach((img, i) => {
    img.classList.toggle('ativa', i === indiceFotoAtual);
  });

  dots.forEach((dot, i) => {
    dot.classList.toggle('ativo', i === indiceFotoAtual);
    dot.setAttribute('aria-selected', i === indiceFotoAtual ? 'true' : 'false');
  });
}

function fotoProxima() {
  if (!carrosselFotos.length) return;
  indiceFotoAtual = (indiceFotoAtual + 1) % carrosselFotos.length;
  atualizarCarrossel();
}

function fotoAnterior() {
  if (!carrosselFotos.length) return;
  indiceFotoAtual = (indiceFotoAtual - 1 + carrosselFotos.length) % carrosselFotos.length;
  atualizarCarrossel();
}

function irParaFoto(i) {
  if (i >= 0 && i < carrosselFotos.length) {
    indiceFotoAtual = i;
    atualizarCarrossel();
  }
}

// ══════════════════════════════════════════════════════════════
// ── MODAIS ───────────────────────────────────────────────────
// ══════════════════════════════════════════════════════════════

function initModais() {
  qs('#btn-adotar-det').addEventListener('click',    abrirModalConfirmacao);
  qs('#btn-confirmar-adocao').addEventListener('click', confirmarAdocao);
  qs('#btn-cancelar-modal').addEventListener('click',  fecharModalConfirmacao);
  qs('#modal-fechar-1').addEventListener('click',      fecharModalConfirmacao);
  qs('#modal-bg').addEventListener('click', e => {
    if (e.target === qs('#modal-bg')) fecharModalConfirmacao();
  });

  qs('#btn-fechar-final').addEventListener('click', fecharModalFinal);
  qs('#modal-fechar-2').addEventListener('click',   fecharModalFinal);
  qs('#modal-final-bg').addEventListener('click', e => {
    if (e.target === qs('#modal-final-bg')) fecharModalFinal();
  });

  document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    if (qs('#modal-bg').classList.contains('aberto'))       fecharModalConfirmacao();
    if (qs('#modal-final-bg').classList.contains('aberto')) fecharModalFinal();
  });
}

function abrirModalConfirmacao() {
  if (!petEscolhido) { console.warn('Nenhum pet selecionado.'); return; }


  const abrigo = abrigos[petEscolhido.abrigoId] || {};

  const msgErro = qs('#mensagem-erro-adocao');
  if (msgErro) {
    msgErro.style.display = 'none';
    msgErro.textContent   = '';
  }

  qs('#info-adocao').innerHTML = `
    <p><strong>Pet:</strong> ${petEscolhido.nome} (${petEscolhido.raca})</p>
    <p><strong>Abrigo:</strong> ${abrigo.nome || '—'}</p>
    <p><strong>Endereço:</strong> ${abrigo.endereco || '—'}</p>
    <p><strong>Telefone:</strong> ${abrigo.telefone || '—'}</p>
    <p><strong>Horário:</strong> ${abrigo.horario || '—'}</p>
  `;

  abrirModal('modal-bg', 'modal-confirmacao');
}

function fecharModalConfirmacao() {
  fecharModal('modal-bg', 'modal-confirmacao');
}

function confirmarAdocao() {
  if (!petEscolhido) return;

  const btnConfirmar = qs('#btn-confirmar-adocao');
  const msgErro       = qs('#mensagem-erro-adocao');

  if (msgErro) {
    msgErro.style.display = 'none';
    msgErro.textContent   = '';
  }
  if (btnConfirmar) {
    btnConfirmar.disabled    = true;
    btnConfirmar.textContent = 'Enviando...';
  }

  const formData = new FormData();
  formData.append('id_animal', petEscolhido.id);

  fetch('solicitar_adocao.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json().then(dados => ({ ok: res.ok, dados })))
    .then(({ ok, dados }) => {
      

      if (!dados.sucesso) {
        if (msgErro) {
          msgErro.textContent   = dados.mensagem || 'Não foi possível enviar seu pedido.';
          msgErro.style.display = 'block';
        }
        return;
      }

      // Sucesso: atualiza o status dinamicamente na tela para 'Em processo'
      const abrigo = abrigos[petEscolhido.abrigoId] || {};
      const nome   = petEscolhido.nome;
      const tel    = abrigo.telefone || '—';
      const nomeAb = abrigo.nome || '—';
      const statusVisual = 'Em processo';

      if (petCardAtivo) {
        // 1. Atualiza o status gravado no elemento 'article' correspondente ao pet ativo
        petCardAtivo.dataset.status = statusVisual;

        // 2. Localiza o selo visual (badge) do card e altera o texto e a classe CSS
        const badge = petCardAtivo.querySelector('[data-status-badge]') || petCardAtivo.querySelector('.badge-status-adocao');
        if (badge) {
          badge.textContent = statusVisual;
          badge.className   = 'badge-status-adocao status-processo'; // Aplica os estilos correspondentes mapeados no PHP
        } else {
          const imgWrapper = petCardAtivo.querySelector('.card-img-wrapper');
          if (imgWrapper) {
            const novoBadge = document.createElement('span');
            novoBadge.className = 'badge-status-adocao status-processo';
            novoBadge.setAttribute('data-status-badge', '');
            novoBadge.textContent = statusVisual;
            imgWrapper.appendChild(novoBadge);
          }
        }
      }

      atualizarStatusBadge(statusVisual);
      petEscolhido.status = statusVisual;

      fecharModalConfirmacao();

      setTimeout(() => {
        qs('#info-final').innerHTML = `
          <p><strong>Pet:</strong> ${nome}</p>
          <p><strong>Abrigo responsável:</strong> ${nomeAb}</p>
          <p><strong>Contato:</strong> ${tel}</p>
        `;

        abrirModal('modal-final-bg', 'modal-final');
      }, 340);
    })
    .catch(() => {
      if (msgErro) {
        msgErro.textContent   = 'Erro de conexão. Tente novamente.';
        msgErro.style.display = 'block';
      }
    })
    .finally(() => {
      if (btnConfirmar) {
        btnConfirmar.disabled    = false;
        btnConfirmar.textContent = 'Sim, quero adotar!';
      }
    });
}

function fecharModalFinal() {
  fecharModal('modal-final-bg', 'modal-final');

  petEscolhido  = null;
  petCardAtivo  = null;

  setTimeout(voltarParaLista, 300);
}

function abrirModal(bgId, modalId) {
  const bg    = qs(`#${bgId}`);
  const modal = qs(`#${modalId}`);

  bg.classList.add('aberto');
  setAriaHidden(bg, false);
  document.body.style.overflow = 'hidden';

  requestAnimationFrame(() => {
    requestAnimationFrame(() => modal.classList.add('ativo'));
  });

  const focusEl = modal.querySelector('button, [href], input, select, textarea');
  if (focusEl) setTimeout(() => focusEl.focus(), 60);
}

function fecharModal(bgId, modalId) {
  const bg    = qs(`#${bgId}`);
  const modal = qs(`#${modalId}`);

  modal.classList.remove('ativo');
  setAriaHidden(bg, true);

  setTimeout(() => {
    bg.classList.remove('aberto');
    document.body.style.overflow = '';
  }, 300);
}

// ── Nav mobile ───────────────────────────────────────────────
function initNavMobile() {
  const toggle  = qs('#nav-toggle');
  const acoes   = qs('#nav-acoes');

  toggle.addEventListener('click', () => {
    const aberta = acoes.classList.toggle('aberta');
    toggle.setAttribute('aria-expanded', aberta ? 'true' : 'false');
  });

  acoes.addEventListener('click', e => {
    if (e.target.closest('button') || e.target.closest('a')) {
      if (!e.target.closest('#btn-favoritos')) {
        acoes.classList.remove('aberta');
        toggle.setAttribute('aria-expanded', 'false');
      }
    }
  });
}