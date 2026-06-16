/* ============================================================
   ADOTE COM AMOR — adocao.js
   Organizado em módulos funcionais, sem onclick inline.
   Inclui: Favoritos (localStorage), Filtros em barra horizontal,
           Status de adoção em tempo real.
   ============================================================ */

   'use strict';

   // ── Estado global ────────────────────────────────────────────
   let petEscolhido    = null;       // Pet aberto na tela de detalhes
   let petCardAtivo    = null;       // Referência ao <article> do pet ativo
   let carrosselFotos  = [];
   let indiceFotoAtual = 0;
   let filtrosAtivos   = { abrigo: 'todos', tipo: 'todos' };
   let favoritos       = [];         // Array de data-id strings
   
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
     carregarFavoritos();       // 1. Carrega favoritos do localStorage
     initImgLoading();
     initBusca();
     initFiltros();
     initFavCard();             // 2. Botões coração nos cards
     initModalFavoritos();      // 3. Sidebar de favoritos
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
   
   // ── Barra de busca (agora na navbar, mesmo input #busca-pet) ──
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
   
     // Limpar filtros
     qs('#btn-limpar-filtros').addEventListener('click', limparFiltros);
   
     // Limpar filtros (mensagem sem resultados)
     qs('#btn-mostrar-todos').addEventListener('click', limparFiltros);
   }
   
   function atualizarBotoesAtivos(grupo, btnAtivo) {
     const seletor = grupo === 'abrigo' ? '[data-filtro-abrigo]' : '[data-filtro-tipo]';
     qsa(seletor).forEach(b => b.classList.remove('filtro-ativo'));
     btnAtivo.classList.add('filtro-ativo');
   }
   
   function limparFiltros() {
     filtrosAtivos = { abrigo: 'todos', tipo: 'todos' };
   
     qs('#filtro-todos').classList.add('filtro-ativo');
     qs('#filtro-tipo-todos').classList.add('filtro-ativo');
     qsa('[data-filtro-abrigo]:not(#filtro-todos)').forEach(b => b.classList.remove('filtro-ativo'));
     qsa('[data-filtro-tipo]:not(#filtro-tipo-todos)').forEach(b => b.classList.remove('filtro-ativo'));
   
     const input = qs('#busca-pet');
     input.value = '';
     qs('#busca-limpar').hidden = true;
   
     aplicarFiltros();
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
   
       const nome  = (card.dataset.nome || '').toLowerCase();
       const raca  = (card.dataset.raca || '').toLowerCase();
       const matchBusca = termo === '' || nome.includes(termo) || raca.includes(termo);
   
       const visivel = matchAbrigo && matchTipo && matchBusca;
   
       if (visivel) {
         card.style.display = 'flex';
         card.classList.remove('visivel');
         void card.offsetWidth; // reflow para animação
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
   
   /** Carrega favoritos do localStorage e aplica estado visual nos cards */
   function carregarFavoritos() {
     try {
       const saved = localStorage.getItem('adocao_favoritos');
       favoritos = saved ? JSON.parse(saved) : [];
     } catch (e) {
       favoritos = [];
     }
   
     // Aplica estado visual em todos os botões de coração
     qsa('.btn-fav-card').forEach(btn => {
       const id = btn.dataset.id;
       if (favoritos.includes(id)) {
         btn.classList.add('favoritado');
         btn.textContent = '❤️';
         btn.setAttribute('aria-label', `Remover ${getNomePetPorId(id)} dos favoritos`);
       }
     });
   }
   
   /** Salva array de favoritos no localStorage */
   function salvarFavoritos() {
     try {
       localStorage.setItem('adocao_favoritos', JSON.stringify(favoritos));
     } catch (e) {
       console.warn('Não foi possível salvar favoritos:', e);
     }
   }
   
   /** Retorna o nome do pet a partir do data-id, buscando no DOM */
   function getNomePetPorId(id) {
     const card = qs(`.pet-card[data-id="${id}"]`);
     return card ? (card.dataset.nome || 'Pet') : 'Pet';
   }
   
   /** Atualiza o contador visual no botão Favoritos da navbar */
   function atualizarContadorFavoritos() {
     const count = qs('#favoritos-count');
     if (!count) return;
   
     if (favoritos.length > 0) {
       count.textContent = favoritos.length;
       count.hidden = false;
     } else {
       count.hidden = true;
     }
   }
   
   /** Inicializa botões coração em cada card */
   function initFavCard() {
     // Delegação de eventos na lista de pets
     qs('#lista-pets').addEventListener('click', e => {
       const btn = e.target.closest('.btn-fav-card');
       if (!btn) return;
   
       e.stopPropagation(); // Não abre detalhes ao clicar no coração
       const id = btn.dataset.id;
       toggleFavorito(id, btn);
     });
   }
   
   /** Alterna favorito: adiciona ou remove */
   function toggleFavorito(id, btn) {
     const nome = getNomePetPorId(id);
     const idx  = favoritos.indexOf(id);
   
     if (idx === -1) {
       // Adicionar
       favoritos.push(id);
       btn.classList.add('favoritado');
       btn.textContent = '❤️';
       btn.setAttribute('aria-label', `Remover ${nome} dos favoritos`);
     } else {
       // Remover
       favoritos.splice(idx, 1);
       btn.classList.remove('favoritado');
       btn.textContent = '♡';
       btn.setAttribute('aria-label', `Favoritar ${nome}`);
     }
   
     salvarFavoritos();
     atualizarContadorFavoritos();
   }
   
   /** Remove pet dos favoritos e atualiza o coração no card original */
   function removerFavoritoById(id) {
     const idx = favoritos.indexOf(id);
     if (idx !== -1) favoritos.splice(idx, 1);
     salvarFavoritos();
     atualizarContadorFavoritos();
   
     // Atualiza o botão coração no card original
     const btn = qs(`.btn-fav-card[data-id="${id}"]`);
     if (btn) {
       btn.classList.remove('favoritado');
       btn.textContent = '♡';
       const nome = getNomePetPorId(id);
       btn.setAttribute('aria-label', `Favoritar ${nome}`);
     }
   }
   
   // ── Modal / Sidebar de Favoritos ─────────────────────────────
   function initModalFavoritos() {
     const btnAbrir  = qs('#btn-favoritos');
     const btnFechar = qs('#modal-fav-fechar');
     const overlay   = qs('#overlay');
   
     btnAbrir.addEventListener('click', abrirModalFavoritos);
     btnFechar.addEventListener('click', fecharModalFavoritos);
     overlay.addEventListener('click', fecharModalFavoritos);
   
     document.addEventListener('keydown', e => {
       if (e.key === 'Escape' && qs('#modal-favoritos').classList.contains('mostrar')) {
         fecharModalFavoritos();
       }
     });
   }
   
   function abrirModalFavoritos() {
     renderizarFavoritos();
   
     const modal   = qs('#modal-favoritos');
     const overlay = qs('#overlay');
   
     modal.classList.add('mostrar');
     overlay.classList.add('mostrar');
     setAriaHidden(modal, false);
     qs('#modal-fav-fechar').focus();
   }
   
   function fecharModalFavoritos() {
     const modal   = qs('#modal-favoritos');
     const overlay = qs('#overlay');
   
     modal.classList.remove('mostrar');
     overlay.classList.remove('mostrar');
     setAriaHidden(modal, true);
     qs('#btn-favoritos').focus();
   }
   
   /** Renderiza os cards de pets favoritos dentro do modal */
   function renderizarFavoritos() {
     const body  = qs('#modal-fav-body');
     const vazio = qs('#modal-fav-vazio');
   
     body.innerHTML = '';
   
     if (favoritos.length === 0) {
       body.style.display = 'none';
       vazio.hidden = false;
       return;
     }
   
     body.style.display = 'flex';
     vazio.hidden = true;
   
     favoritos.forEach(id => {
       const card = qs(`.pet-card[data-id="${id}"]`);
       if (!card) return; // Pet removido do DOM (improvável, mas seguro)
   
       const nome  = card.dataset.nome  || 'Sem nome';
       const tipo  = card.dataset.tipo  || '';
       const raca  = card.dataset.raca  || '';
       const fotos = JSON.parse(card.dataset.fotos || '[]');
       const img   = fotos[0] || '';
   
       const mini = document.createElement('div');
       mini.className = 'fav-mini-card';
       mini.innerHTML = `
         <img class="fav-mini-img" src="${img}" alt="${nome}" loading="lazy">
         <div class="fav-mini-info">
           <div class="fav-mini-nome">${nome}</div>
           <div class="fav-mini-sub">${tipo}${raca ? ' · ' + raca : ''}</div>
         </div>
         <div class="fav-mini-acoes">
           <button class="btn-fav-ver" data-id="${id}" aria-label="Ver detalhes de ${nome}">Ver</button>
           <button class="btn-fav-remover" data-id="${id}" aria-label="Remover ${nome} dos favoritos">Remover</button>
         </div>
       `;
   
       // Botão "Ver" — abre a tela de detalhes
       mini.querySelector('.btn-fav-ver').addEventListener('click', () => {
         fecharModalFavoritos();
         verDetalhes(card);
       });
   
       // Botão "Remover" — remove dos favoritos e re-renderiza
       mini.querySelector('.btn-fav-remover').addEventListener('click', () => {
         removerFavoritoById(id);
         renderizarFavoritos(); // Atualiza o modal
       });
   
       body.appendChild(mini);
     });
   }
   
   // ══════════════════════════════════════════════════════════════
   // ── DETALHES DO PET ──────────────────────────────────────────
   // ══════════════════════════════════════════════════════════════
   
   function initDetalhes() {
     // Delegação de eventos para botões "Ver Detalhes"
     qs('#lista-pets').addEventListener('click', e => {
       const btn = e.target.closest('.btn-detalhes');
       if (btn) verDetalhes(btn.closest('.pet-card'));
     });
   
     qs('#btn-voltar-det').addEventListener('click', voltarParaLista);
   }
   
   function verDetalhes(card) {
     petCardAtivo = card; // Guarda referência para atualizar status depois
     const abrigoId = card.dataset.abrigo;
   
     petEscolhido = {
       id:         card.dataset.id          || '',
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
     qs('#detalhes-nome').textContent        = petEscolhido.nome;
     qs('#detalhes-tipo').textContent        = petEscolhido.tipo;
     qs('#detalhes-raca').textContent        = petEscolhido.raca;
     qs('#detalhes-sexo').textContent        = petEscolhido.sexo;
     qs('#detalhes-peso').textContent        = petEscolhido.peso;
     qs('#detalhes-altura').textContent      = petEscolhido.altura;
     qs('#detalhes-idade').textContent       = petEscolhido.idade;
     qs('#detalhes-porte').textContent       = petEscolhido.porte;
     qs('#detalhes-vacinado').textContent    = petEscolhido.vacinado;
     qs('#detalhes-castrado').textContent    = petEscolhido.castrado;
     qs('#detalhes-deficiencia').textContent = petEscolhido.deficiencia;
     qs('#detalhes-localizacao').textContent = petEscolhido.localizacao;
     qs('#detalhes-descricao').textContent   = petEscolhido.descricao;
   
     // Badge de status
     atualizarStatusBadge(petEscolhido.status);
   
     // Carrossel
     carrosselFotos    = petEscolhido.fotos;
     indiceFotoAtual   = 0;
     carregarCarrossel();
   
     // Exibe a tela
     const tela = qs('#detalhes-pet');
     tela.style.display = 'block';
     setAriaHidden(tela, false);
     document.body.style.overflow = 'hidden';
     tela.scrollTo(0, 0);
     qs('#btn-voltar-det').focus();
   }
   
   /** Atualiza o badge de status na tela de detalhes */
   function atualizarStatusBadge(status) {
     const badge = qs('#detalhes-status');
     badge.textContent = status;
     badge.className   = 'det-status-badge';
   
     if (status.toLowerCase().includes('disponível')) {
       badge.classList.add('disponivel');
     } else if (status.toLowerCase().includes('adoção')) {
       badge.classList.add('em-adocao');
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
   
     cont.innerHTML  = '';
     indic.innerHTML = '';
   
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
   
   /** Confirma a adoção e atualiza status em tempo real na sessão */
   function confirmarAdocao() {
     if (!petEscolhido) return;
   
     const abrigo  = abrigos[petEscolhido.abrigoId] || {};
     const nome    = petEscolhido.nome;
     const tel     = abrigo.telefone || '—';
     const nomeAb  = abrigo.nome || '—';
     const novoStatus = 'Em adoção';
   
     // ── Atualiza status no card da lista (em tempo real, sem reload) ──
     if (petCardAtivo) {
       // Atualiza data-status no elemento
       petCardAtivo.dataset.status = novoStatus;
   
       // Atualiza o badge no card
       const badge = petCardAtivo.querySelector('.badge');
       if (badge) {
         badge.textContent = 'Em adoção';
         badge.className   = 'badge urgente'; // laranja
       } else {
         // Cria badge se não existia
         const imgWrapper = petCardAtivo.querySelector('.card-img-wrapper');
         if (imgWrapper) {
           const novoBadge = document.createElement('span');
           novoBadge.className = 'badge urgente';
           novoBadge.textContent = 'Em adoção';
           imgWrapper.appendChild(novoBadge);
         }
       }
     }
   
     // Atualiza o badge na tela de detalhes
     atualizarStatusBadge(novoStatus);
     if (petEscolhido) petEscolhido.status = novoStatus;
   
     fecharModalConfirmacao();
   
     // Aguarda animação de saída antes de abrir próximo modal
     setTimeout(() => {
       qs('#info-final').innerHTML = `
         <p><strong>Pet:</strong> ${nome}</p>
         <p><strong>Abrigo responsável:</strong> ${nomeAb}</p>
         <p><strong>Contato:</strong> ${tel}</p>
       `;
   
       abrirModal('modal-final-bg', 'modal-final');
     }, 340);
   }
   
   function fecharModalFinal() {
     fecharModal('modal-final-bg', 'modal-final');
   
     // Limpa estado apenas após confirmar
     petEscolhido  = null;
     petCardAtivo  = null;
   
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
   
     // Fecha ao clicar em qualquer botão dentro das ações
     acoes.addEventListener('click', e => {
       if (e.target.closest('button') || e.target.closest('a')) {
         // Não fecha se for o botão de favoritos (abre o modal)
         if (!e.target.closest('#btn-favoritos')) {
           acoes.classList.remove('aberta');
           toggle.setAttribute('aria-expanded', 'false');
         }
       }
     });
   }
   