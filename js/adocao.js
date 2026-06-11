/* ============================================================
   ADOTE COM AMOR — adocao.js
   Organizado em módulos funcionais, sem onclick inline.
   ============================================================ */

'use strict';

// ── Estado global ────────────────────────────────────────────
let petEscolhido = null;
let carrosselFotos = [];
let indiceFotoAtual = 0;
let filtrosAtivos = { abrigo: 'todos', tipo: 'todos' };

// ── Dados dos abrigos ────────────────────────────────────────
const abrigos = {
  centro: {
    nome: 'Abrigo Centro',
    endereco: 'Rua das Flores, 123 — Centro',
    telefone: '(47) 3221-4567',
    horario: 'Segunda a Sábado, 9h às 18h'
  },
  norte: {
    nome: 'Abrigo Zona Norte',
    endereco: 'Av. Norte, 456 — Zona Norte',
    telefone: '(47) 3221-7890',
    horario: 'Terça a Domingo, 10h às 17h'
  },
  sul: {
    nome: 'Abrigo Zona Sul',
    endereco: 'Rua Sul, 789 — Zona Sul',
    telefone: '(47) 3221-2345',
    horario: 'Segunda a Sexta, 8h às 17h'
  },
  leste: {
    nome: 'Abrigo Zona Leste',
    endereco: 'Av. Leste, 101 — Zona Leste',
    telefone: '(47) 3221-6789',
    horario: 'Quarta a Domingo, 9h às 16h'
  }
};

// ── Helpers ──────────────────────────────────────────────────
function qs(selector, root = document) { return root.querySelector(selector); }
function qsa(selector, root = document) { return [...root.querySelectorAll(selector)]; }

function setAriaHidden(el, hidden) {
  el.setAttribute('aria-hidden', hidden ? 'true' : 'false');
}

// ── Inicialização ────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  initImgLoading();
  initBusca();
  initSidebar();
  initFiltros();
  initCarrossel();
  initDetalhes();
  initModais();
  initNavMobile();
  aplicarFiltros();
});

// ── Loading de imagens ───────────────────────────────────────
function initImgLoading() {
  qsa('.pet-card img').forEach(img => {
    const loader = img.previousElementSibling;
    if (!loader || !loader.classList.contains('card-img-loader')) return;

    if (img.complete) {
      loader.style.display = 'none';
    } else {
      img.addEventListener('load', () => { loader.style.display = 'none'; });
      img.addEventListener('error', () => { loader.style.display = 'none'; });
    }
  });
}

// ── Barra de busca ───────────────────────────────────────────
function initBusca() {
  const input = qs('#busca-pet');
  const btnLimpar = qs('#busca-limpar');

  input.addEventListener('input', () => {
    const temValor = input.value.trim().length > 0;
    btnLimpar.hidden = !temValor;
    aplicarFiltros();
  });

  btnLimpar.addEventListener('click', () => {
    input.value = '';
    btnLimpar.hidden = true;
    input.focus();
    aplicarFiltros();
  });
}

// ── Sidebar ──────────────────────────────────────────────────
function initSidebar() {
  const fab      = qs('#fab-filtros');
  const sidebar  = qs('#sidebar-filtros');
  const overlay  = qs('.overlay');
  const fechar   = qs('#sidebar-fechar');

  fab.addEventListener('click', abrirSidebar);
  fechar.addEventListener('click', fecharSidebar);
  overlay.addEventListener('click', fecharSidebar);

  // Fechar com Escape
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && sidebar.classList.contains('mostrar')) fecharSidebar();
  });
}

function abrirSidebar() {
  const sidebar = qs('#sidebar-filtros');
  const overlay = qs('.overlay');
  const fab     = qs('#fab-filtros');

  sidebar.classList.add('mostrar');
  overlay.classList.add('mostrar');
  fab.classList.add('escondido');
  setAriaHidden(sidebar, false);
  qs('#sidebar-fechar').focus();
}

function fecharSidebar() {
  const sidebar = qs('#sidebar-filtros');
  const overlay = qs('.overlay');
  const fab     = qs('#fab-filtros');

  sidebar.classList.remove('mostrar');
  overlay.classList.remove('mostrar');
  fab.classList.remove('escondido');
  setAriaHidden(sidebar, true);
  fab.focus();
}

// ── Filtros ──────────────────────────────────────────────────
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

  // Limpar filtros (sidebar)
  qs('#btn-limpar-filtros').addEventListener('click', limparFiltros);

  // Limpar filtros (mensagem sem resultados)
  qs('#btn-mostrar-todos').addEventListener('click', limparFiltros);
}

function atualizarBotoesAtivos(grupo, btnAtivo) {
  const seletor = grupo === 'abrigo'
    ? '[data-filtro-abrigo]'
    : '[data-filtro-tipo]';

  qsa(seletor).forEach(b => b.classList.remove('filtro-ativo'));
  btnAtivo.classList.add('filtro-ativo');
}

function limparFiltros() {
  filtrosAtivos = { abrigo: 'todos', tipo: 'todos' };

  // Reseta botões
  qs('#filtro-todos').classList.add('filtro-ativo');
  qs('#filtro-tipo-todos').classList.add('filtro-ativo');
  qsa('[data-filtro-abrigo]:not(#filtro-todos)').forEach(b => b.classList.remove('filtro-ativo'));
  qsa('[data-filtro-tipo]:not(#filtro-tipo-todos)').forEach(b => b.classList.remove('filtro-ativo'));

  // Limpa busca
  const input = qs('#busca-pet');
  input.value = '';
  qs('#busca-limpar').hidden = true;

  aplicarFiltros();
  fecharSidebar();
}

// ── Aplicar filtros + busca combinados ───────────────────────
function aplicarFiltros() {
  const termo = qs('#busca-pet').value.toLowerCase().trim();
  const cards = qsa('.pet-card');
  let algumVisivel = false;

  cards.forEach((card, i) => {
    const matchAbrigo = filtrosAtivos.abrigo === 'todos'
      || card.dataset.abrigo === filtrosAtivos.abrigo;

    const matchTipo = filtrosAtivos.tipo === 'todos'
      || card.classList.contains(filtrosAtivos.tipo);

    const nome  = (card.dataset.nome  || '').toLowerCase();
    const raca  = (card.dataset.raca  || '').toLowerCase();
    const matchBusca = termo === '' || nome.includes(termo) || raca.includes(termo);

    const visivel = matchAbrigo && matchTipo && matchBusca;

    if (visivel) {
      card.style.display = 'flex';
      // Animação escalonada
      card.classList.remove('visivel');
      void card.offsetWidth; // reflow
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

// ── Detalhes do pet ──────────────────────────────────────────
function initDetalhes() {
  // Delegação de eventos para botões "Ver Detalhes"
  qs('#lista-pets').addEventListener('click', e => {
    const btn = e.target.closest('.btn-detalhes');
    if (btn) verDetalhes(btn.closest('.pet-card'));
  });

  qs('#btn-voltar-det').addEventListener('click', voltarParaLista);
}

function verDetalhes(card) {
  const abrigoId = card.dataset.abrigo;

  petEscolhido = {
    nome:       card.dataset.nome        || 'Sem nome',
    abrigoId,
    localizacao: abrigos[abrigoId]?.nome || 'Não informado',
    tipo:       card.dataset.tipo        || 'Não informado',
    raca:       card.dataset.raca        || 'Não informada',
    sexo:       card.dataset.sexo        || 'Não informado',
    peso:       card.dataset.peso        || 'Não informado',
    altura:     card.dataset.altura      || 'Não informada',
    idade:      card.dataset.idade       || 'Não informada',
    porte:      card.dataset.porte       || 'Não informado',
    vacinado:   card.dataset.vacinado    || 'Sim',
    castrado:   card.dataset.castrado    || 'Sim',
    deficiencia: card.dataset.deficiencia || 'Nenhuma',
    status:     card.dataset.status      || 'Disponível',
    descricao:  card.dataset.descricao   || 'Pet especial aguardando um lar.',
    fotos:      JSON.parse(card.dataset.fotos || '[]')
  };

  // Preenche os campos
  qs('#detalhes-nome').textContent      = petEscolhido.nome;
  qs('#detalhes-tipo').textContent      = petEscolhido.tipo;
  qs('#detalhes-raca').textContent      = petEscolhido.raca;
  qs('#detalhes-sexo').textContent      = petEscolhido.sexo;
  qs('#detalhes-peso').textContent      = petEscolhido.peso;
  qs('#detalhes-altura').textContent    = petEscolhido.altura;
  qs('#detalhes-idade').textContent     = petEscolhido.idade;
  qs('#detalhes-porte').textContent     = petEscolhido.porte;
  qs('#detalhes-vacinado').textContent  = petEscolhido.vacinado;
  qs('#detalhes-castrado').textContent  = petEscolhido.castrado;
  qs('#detalhes-deficiencia').textContent = petEscolhido.deficiencia;
  qs('#detalhes-localizacao').textContent = petEscolhido.localizacao;
  qs('#detalhes-descricao').textContent   = petEscolhido.descricao;

  // Badge de status
  const statusBadge = qs('#detalhes-status');
  statusBadge.textContent = petEscolhido.status;
  statusBadge.className = 'det-status-badge';
  if (petEscolhido.status.toLowerCase().includes('disponível')) {
    statusBadge.classList.add('disponivel');
  } else if (petEscolhido.status.toLowerCase().includes('adoção')) {
    statusBadge.classList.add('em-adocao');
  }

  // Carrossel (reset garantido)
  carrosselFotos = petEscolhido.fotos;
  indiceFotoAtual = 0;
  carregarCarrossel();

  // Exibe a tela
  const tela = qs('#detalhes-pet');
  tela.style.display = 'block';
  setAriaHidden(tela, false);
  document.body.style.overflow = 'hidden';
  tela.scrollTo(0, 0);
  qs('#btn-voltar-det').focus();
}

function voltarParaLista() {
  const tela = qs('#detalhes-pet');
  tela.style.display = 'none';
  setAriaHidden(tela, true);
  document.body.style.overflow = '';
  // petEscolhido é limpo apenas após confirmar adoção
}

// ── Carrossel ────────────────────────────────────────────────
function initCarrossel() {
  qs('#btn-anterior').addEventListener('click', fotoAnterior);
  qs('#btn-proximo').addEventListener('click', fotoProxima);

  // Swipe touch
  const cont = qs('#carrossel-imagens');
  let startX = 0;

  cont.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
  cont.addEventListener('touchend', e => {
    const diff = startX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 40) diff > 0 ? fotoProxima() : fotoAnterior();
  });

  // Clique avança (estilo Instagram)
  cont.addEventListener('click', e => {
    if (e.target.tagName === 'IMG') fotoProxima();
  });
}

function carregarCarrossel() {
  const cont  = qs('#carrossel-imagens');
  const indic = qs('#carrossel-indicadores');

  // Limpa tudo (garante reset ao abrir outro pet)
  cont.innerHTML  = '';
  indic.innerHTML = '';

  carrosselFotos.forEach((src, i) => {
    // Imagem
    const img = document.createElement('img');
    img.src   = src;
    img.alt   = `Foto ${i + 1} de ${petEscolhido?.nome || 'pet'}`;
    if (i === 0) img.classList.add('ativa');
    cont.appendChild(img);

    // Indicador
    const dot = document.createElement('button');
    dot.className = 'indicador' + (i === 0 ? ' ativo' : '');
    dot.setAttribute('role', 'tab');
    dot.setAttribute('aria-label', `Foto ${i + 1}`);
    dot.setAttribute('aria-selected', i === 0 ? 'true' : 'false');
    dot.addEventListener('click', () => irParaFoto(i));
    indic.appendChild(dot);
  });

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
  indiceFotoAtual = (indiceFotoAtual + 1) % carrosselFotos.length;
  atualizarCarrossel();
}

function fotoAnterior() {
  indiceFotoAtual = (indiceFotoAtual - 1 + carrosselFotos.length) % carrosselFotos.length;
  atualizarCarrossel();
}

function irParaFoto(i) {
  if (i >= 0 && i < carrosselFotos.length) {
    indiceFotoAtual = i;
    atualizarCarrossel();
  }
}

// ── Modais ───────────────────────────────────────────────────
function initModais() {
  // Modal de confirmação
  qs('#btn-adotar-det').addEventListener('click', abrirModalConfirmacao);
  qs('#btn-confirmar-adocao').addEventListener('click', confirmarAdocao);
  qs('#btn-cancelar-modal').addEventListener('click', fecharModalConfirmacao);
  qs('#modal-fechar-1').addEventListener('click', fecharModalConfirmacao);
  qs('#modal-bg').addEventListener('click', e => {
    if (e.target === qs('#modal-bg')) fecharModalConfirmacao();
  });

  // Modal final
  qs('#btn-fechar-final').addEventListener('click', fecharModalFinal);
  qs('#modal-fechar-2').addEventListener('click', fecharModalFinal);
  qs('#modal-final-bg').addEventListener('click', e => {
    if (e.target === qs('#modal-final-bg')) fecharModalFinal();
  });

  // Escape fecha modais
  document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    if (qs('#modal-bg').classList.contains('aberto'))       fecharModalConfirmacao();
    if (qs('#modal-final-bg').classList.contains('aberto')) fecharModalFinal();
  });
}

function abrirModalConfirmacao() {
  if (!petEscolhido) { console.warn('Nenhum pet selecionado.'); return; }

  const abrigo = abrigos[petEscolhido.abrigoId] || {};

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

  const abrigo = abrigos[petEscolhido.abrigoId] || {};
  const nome   = petEscolhido.nome;
  const tel    = abrigo.telefone || '—';
  const nomeAb = abrigo.nome || '—';

  fecharModalConfirmacao();

  // Aguarda animação de saída antes de abrir próximo modal
  setTimeout(() => {
    qs('#info-final').innerHTML = `
      <p><strong>Pet:</strong> ${nome}</p>
      <p><strong>Abrigo responsável:</strong> ${nomeAb}</p>
      <p><strong>Contato:</strong> ${tel}</p>
    `;

    petEscolhido = null; // ← limpa após capturar os dados

    abrirModal('modal-final-bg', 'modal-final');
  }, 340);
}

function fecharModalFinal() {
  fecharModal('modal-final-bg', 'modal-final');
  setTimeout(voltarParaLista, 300);
}

// Helpers de modal
function abrirModal(bgId, modalId) {
  const bg    = qs(`#${bgId}`);
  const modal = qs(`#${modalId}`);

  bg.classList.add('aberto');
  setAriaHidden(bg, false);
  document.body.style.overflow = 'hidden';

  requestAnimationFrame(() => {
    requestAnimationFrame(() => modal.classList.add('ativo'));
  });

  // Foca o primeiro elemento focável
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
  const toggle = qs('#nav-toggle');
  const nav    = qs('#nav-principal');

  toggle.addEventListener('click', () => {
    const aberta = nav.classList.toggle('aberta');
    toggle.setAttribute('aria-expanded', aberta ? 'true' : 'false');
  });

  // Fecha ao clicar em link
  qsa('#nav-principal a').forEach(a => {
    a.addEventListener('click', () => {
      nav.classList.remove('aberta');
      toggle.setAttribute('aria-expanded', 'false');
    });
  });
}
