let usuarioLogado = null;
let animalSelecionado = null;

// ========== CARROSSEL ==========
let slideAtual = 0;
let intervaloCarrossel;

function mostrarSlide(indice) {
    const slides = document.querySelectorAll('.slide-banner');
    const indicadores = document.querySelectorAll('.indicador');
    if (indice >= slides.length) slideAtual = 0;
    else if (indice < 0) slideAtual = slides.length - 1;
    else slideAtual = indice;
    
    slides.forEach(slide => slide.classList.remove('ativo'));
    indicadores.forEach(indicador => indicador.classList.remove('ativo'));
    slides[slideAtual]?.classList.add('ativo');
    indicadores[slideAtual]?.classList.add('ativo');
}

function mudarSlide(direcao) {
    pararCarrossel();
    mostrarSlide(slideAtual + direcao);
    iniciarCarrossel();
}

function irParaSlide(indice) {
    pararCarrossel();
    mostrarSlide(indice);
    iniciarCarrossel();
}

function iniciarCarrossel() {
    if (intervaloCarrossel) clearInterval(intervaloCarrossel);
    intervaloCarrossel = setInterval(() => mudarSlide(1), 5000);
}

function pararCarrossel() {
    clearInterval(intervaloCarrossel);
}

// ========== FUNÇÕES DA API ==========
async function fetchAPI(endpoint, options = {}) {
    try {
        const response = await fetch(`${endpoint}`, {
            headers: { 'Content-Type': 'application/json' },
            ...options
        });
        return await response.json();
    } catch (error) {
        console.error('Erro na API:', error);
        return { error: error.message };
    }
}

async function carregarAnimais(containerId, limite = null, filtros = {}) {
    const container = document.getElementById(containerId);
    if (!container) return;

    // Se o PHP já renderizou cards, não sobrescreve — apenas atualiza favoritos
    const temCardsPhp = container.querySelectorAll('.cartao-animal').length > 0;
    if (temCardsPhp && Object.keys(filtros).length === 0) {
        container.querySelectorAll('.cartao-animal').forEach(card => {
            const id = card.dataset.id;
            if (id) atualizarIconeFavorito(id);
        });
        return;
    }

    container.innerHTML = '<div class="loading"><div class="spinner"></div> Carregando...</div>';
    
    let url = 'api.php?acao=listar_animais';
    if (filtros.especie) url += `&especie=${encodeURIComponent(filtros.especie)}`;
    if (filtros.sexo) url += `&sexo=${encodeURIComponent(filtros.sexo)}`;
    if (filtros.porte) url += `&porte=${encodeURIComponent(filtros.porte)}`;
    
    const result = await fetchAPI(url);
    
    if (result.error) {
        container.innerHTML = '<p style="text-align:center; color:red;">Erro ao carregar animais.</p>';
        return;
    }
    
    let animais = result;
    if (limite) animais = animais.slice(0, limite);
    
    if (!animais || animais.length === 0) {
        container.innerHTML = '<p style="text-align:center;">Nenhum animal disponível para adoção no momento.</p>';
        return;
    }
    
    container.innerHTML = animais.map(animal => criarCardAnimal(animal)).join('');
    
    animais.forEach(animal => {
        atualizarIconeFavorito(animal.id_animal);
    });
}

function criarCardAnimal(animal) {
    const sexoClass = animal.sexo === 'Macho' ? 'macho' : 'femea';
    const idadeFormatada = animal.idade ? `${animal.idade} ${animal.idade == 1 ? 'ano' : 'anos'}` : 'Idade não informada';
    
    // CORREÇÃO: Mapeia para ds_img buscando na pasta uploads/ igual ao index.php
    const foto = animal.ds_img ? 'uploads/' + animal.ds_img : 'https://placehold.co/400x400?text=Sem+Foto';
    
    return `
        <div class="cartao-animal" data-id="${animal.id_animal}" onclick="abrirDetalhesAnimal(${animal.id_animal})">
            <div class="imagem-animal">
                <img src="${foto}" alt="${animal.nome}" onerror="this.src='https://placehold.co/400x400?text=Sem+Imagem'">
                <span class="etiqueta-sexo ${sexoClass}">${animal.sexo === 'Macho' ? '♂' : '♀'} ${animal.sexo}</span>
                <button class="btn-favorito" onclick="event.stopPropagation(); toggleFavorito(${animal.id_animal}, this)">
                    <i class="fas fa-heart"></i>
                </button>
            </div>
            <div class="info-animal">
                <h3 class="nome-animal">${animal.nome}</h3>
                <div class="raca-animal"><i class="fas fa-tag"></i> ${animal.raca || 'SRD'}</div>
                <div class="detalhes-animal">
                    <div class="detalhe-item"><i class="fas fa-birthday-cake"></i> ${idadeFormatada}</div>
                    <div class="detalhe-item"><i class="fas fa-${animal.especie === 'Cachorro' ? 'dog' : 'cat'}"></i> ${animal.especie}</div>
                </div>
                <button class="btn-adotar" onclick="event.stopPropagation(); abrirDetalhesAnimal(${animal.id_animal})">
                    <i class="fas fa-heart"></i> Quero adotar
                </button>
            </div>
        </div>
    `;
}

// ========== FAVORITOS ==========
async function toggleFavorito(idAnimal, elemento) {
    if (!usuarioLogado) {
        alert('Você precisa fazer login para favoritar animais.');
        abrirLogin();
        return;
    }
    
    const result = await fetchAPI('api.php?acao=toggle_favorito', {
        method: 'POST',
        body: JSON.stringify({
            id_usuario: usuarioLogado.id_usuario,
            id_animal: idAnimal
        })
    });
    
    if (result.success) {
        if (result.favoritado) {
            elemento.classList.add('favoritado');
        } else {
            elemento.classList.remove('favoritado');
        }
    } else {
        alert('Erro ao favoritar. Tente novamente.');
    }
}

async function atualizarIconeFavorito(idAnimal) {
    if (!usuarioLogado) return;
    
    const result = await fetchAPI(`api.php?acao=verificar_favorito&id_usuario=${usuarioLogado.id_usuario}&id_animal=${idAnimal}`);
    
    if (result.success && result.favoritado) {
        const botoes = document.querySelectorAll(`.cartao-animal[data-id="${idAnimal}"] .btn-favorito`);
        botoes.forEach(btn => btn.classList.add('favoritado'));
    }
}

async function carregarFavoritos() {
    const container = document.getElementById('gradeFavoritos');
    if (!container) return;
    
    if (!usuarioLogado) {
        container.innerHTML = '<p style="text-align:center;">Faça login para ver seus animais favoritados.</p>';
        return;
    }
    
    container.innerHTML = '<div class="loading"><div class="spinner"></div> Carregando...</div>';
    
    const result = await fetchAPI(`api.php?acao=listar_favoritos&id_usuario=${usuarioLogado.id_usuario}`);
    
    if (result.error) {
        container.innerHTML = '<p style="text-align:center;">Erro ao carregar favoritos.</p>';
        return;
    }
    
    if (result.length === 0) {
        container.innerHTML = '<p style="text-align:center;">Você não tem animais favoritados.</p>';
        return;
    }
    
    container.innerHTML = result.map(animal => criarCardAnimal(animal)).join('');
}

// ========== MODAL DOAÇÃO ==========
let tipoDoacaoSelecionado = null;

function abrirModalDoacao() {
    document.getElementById('modalDoacao').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function fecharModalDoacao() {
    document.getElementById('modalDoacao').style.display = 'none';
    document.body.style.overflow = '';
    tipoDoacaoSelecionado = null;
    document.querySelectorAll('.opcao-doacao').forEach(opt => opt.classList.remove('selecionado'));
    document.getElementById('valorDoacaoDiv').style.display = 'none';
    document.getElementById('outroDoacao').style.display = 'none';
    if (document.getElementById('valorDoacao')) document.getElementById('valorDoacao').value = '';
    if (document.getElementById('descricaoOutro')) document.getElementById('descricaoOutro').value = '';
}

function selecionarTipoDoacao(tipo, elemento) {
    const valorDiv = document.getElementById('valorDoacaoDiv');
    const outroDiv = document.getElementById('outroDoacao');
    
    document.querySelectorAll('.opcao-doacao').forEach(opt => opt.classList.remove('selecionado'));
    elemento.classList.add('selecionado');
    tipoDoacaoSelecionado = tipo;
    
    if (tipo === 'Dinheiro') {
        valorDiv.style.display = 'block';
        outroDiv.style.display = 'none';
    } else if (tipo === 'Outro') {
        valorDiv.style.display = 'none';
        outroDiv.style.display = 'block';
    } else {
        valorDiv.style.display = 'none';
        outroDiv.style.display = 'none';
    }
}

async function enviarDoacao() {
    if (!usuarioLogado) {
        alert('Você precisa fazer login para realizar uma doação.');
        fecharModalDoacao();
        abrirLogin();
        return;
    }
    
    if (!tipoDoacaoSelecionado) {
        alert('Selecione um tipo de doação.');
        return;
    }
    
    let descricao = '';
    let valor = null;
    
    if (tipoDoacaoSelecionado === 'Dinheiro') {
        valor = parseFloat(document.getElementById('valorDoacao').value);
        if (isNaN(valor) || valor <= 0) {
            alert('Informe um valor válido para doação.');
            return;
        }
        descricao = `Doação de R$ ${valor.toFixed(2)}`;
    } else if (tipoDoacaoSelecionado === 'Outro') {
        descricao = document.getElementById('descricaoOutro').value;
        if (!descricao) {
            alert('Descreva o que você deseja doar.');
            return;
        }
    } else {
        descricao = `Doação de ${tipoDoacaoSelecionado}`;
    }
    
    const result = await fetchAPI('api.php?acao=doar', {
        method: 'POST',
        body: JSON.stringify({
            id_usuario: usuarioLogado.id_usuario,
            tipo_doacao: tipoDoacaoSelecionado,
            descricao: descricao,
            valor: valor
        })
    });
    
    if (result.success) {
        alert('Doação registrada com sucesso! Muito obrigado!');
        fecharModalDoacao();
    } else {
        alert('Erro ao registrar doação. Tente novamente.');
    }
}

// ========== DETALHES ANIMAL ==========
async function abrirDetalhesAnimal(id) {
    const result = await fetchAPI(`api.php?acao=buscar_animal&id=${id}`);
    if (result.error) {
        alert('Erro ao carregar detalhes do animal');
        return;
    }
    
    animalSelecionado = result;
    
    // CORREÇÃO: Ajustado também no modal de detalhes
    const foto = animalSelecionado.ds_img ? 'uploads/' + animalSelecionado.ds_img : 'https://placehold.co/400x400?text=Sem+Foto';
    
    document.getElementById('modalAnimalImg').src = foto;
    document.getElementById('modalAnimalNome').textContent = animalSelecionado.nome;
    document.getElementById('modalAnimalRaca').textContent = animalSelecionado.raca || 'SRD';
    document.getElementById('modalAnimalSexo').innerHTML = `<i class="fas fa-${animalSelecionado.sexo === 'Macho' ? 'mars' : 'venus'}"></i> ${animalSelecionado.sexo}`;
    document.getElementById('modalAnimalIdade').textContent = animalSelecionado.idade || '?';
    document.getElementById('modalAnimalEspecie').innerHTML = `<i class="fas fa-${animalSelecionado.especie === 'Cachorro' ? 'dog' : 'cat'}"></i> ${animalSelecionado.especie}`;
    document.getElementById('modalAnimalPeso').textContent = animalSelecionado.peso || '?';
    document.getElementById('modalAnimalPorte').textContent = animalSelecionado.porte || 'Não informado';
    document.getElementById('modalAnimalVacinado').innerHTML = animalSelecionado.vacinado ? '<i class="fas fa-check-circle" style="color:#4caf50"></i> Sim' : '<i class="fas fa-times-circle" style="color:#f44336"></i> Não';
    document.getElementById('modalAnimalDescricao').textContent = animalSelecionado.descricao || 'Sem descrição disponível.';
    
    document.getElementById('modalAnimal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function fecharModalAnimal() {
    document.getElementById('modalAnimal').style.display = 'none';
    document.body.style.overflow = '';
}

async function solicitarAdocao() {
    if (!usuarioLogado) {
        alert('Você precisa fazer login para solicitar uma adoção.');
        fecharModalAnimal();
        abrirLogin();
        return;
    }
    
    if (!animalSelecionado) return;
    
    const confirmar = confirm(`Você realmente deseja adotar ${animalSelecionado.nome}?`);
    
    if (confirmar) {
        const result = await fetchAPI('api.php?acao=solicitar_adocao', {
            method: 'POST',
            body: JSON.stringify({
                id_usuario: usuarioLogado.id_usuario,
                id_animal: animalSelecionado.id_animal
            })
        });
        
        if (result.success) {
            alert('Solicitação de adoção enviada com sucesso!');
            fecharModalAnimal();
            carregarAnimais('gradeAnimaisDestaque', 4);
            if (document.getElementById('gradeTodosAnimais')) {
                carregarAnimais('gradeTodosAnimais');
            }
        } else {
            alert('Erro ao enviar solicitação. Tente novamente.');
        }
    }
}

// ========== LER MAIS GERAL (UM ÚNICO BOTÃO) ==========
function toggleLerMaisGeral() {
    const secao = document.querySelector('.secao-sobre');
    const btn = document.querySelector('.btn-ler-mais-sobre');
    secao.classList.toggle('expandido');
    
    if (secao.classList.contains('expandido')) {
        btn.innerHTML = '<i class="fas fa-chevron-up"></i> Ler menos';
    } else {
        btn.innerHTML = '<i class="fas fa-chevron-down"></i> Ler mais';
    }
}

// ========== NAVEGAÇÃO ==========
function verTodosAnimais() {
    const banner = document.querySelector('.banner-principal');
    const secaoAnimais = document.getElementById('secaoAnimais');
    const secaoSobre = document.querySelector('.secao-sobre');
    const secaoEquipe = document.querySelector('.secao-equipe');
    const paginaTodos = document.getElementById('paginaTodosAnimais');
    const paginaFavoritos = document.getElementById('paginaFavoritos');
    
    if (banner) banner.style.display = 'none';
    if (secaoAnimais) secaoAnimais.style.display = 'none';
    if (secaoSobre) secaoSobre.style.display = 'none';
    if (secaoEquipe) secaoEquipe.style.display = 'none';
    if (paginaTodos) paginaTodos.style.display = 'block';
    if (paginaFavoritos) paginaFavoritos.style.display = 'none';
    
    carregarAnimais('gradeTodosAnimais');
    window.scrollTo(0, 0);
}

function verFavoritos() {
    if (!usuarioLogado) {
        alert('Faça login para ver seus favoritos.');
        abrirLogin();
        return;
    }
    
    const banner = document.querySelector('.banner-principal');
    const secaoAnimais = document.getElementById('secaoAnimais');
    const secaoSobre = document.querySelector('.secao-sobre');
    const secaoEquipe = document.querySelector('.secao-equipe');
    const paginaTodos = document.getElementById('paginaTodosAnimais');
    const paginaFavoritos = document.getElementById('paginaFavoritos');
    
    if (banner) banner.style.display = 'none';
    if (secaoAnimais) secaoAnimais.style.display = 'none';
    if (secaoSobre) secaoSobre.style.display = 'none';
    if (secaoEquipe) secaoEquipe.style.display = 'none';
    if (paginaTodos) paginaTodos.style.display = 'none';
    if (paginaFavoritos) paginaFavoritos.style.display = 'block';
    
    carregarFavoritos();
    window.scrollTo(0, 0);
}

function voltarParaHome() {
    const banner = document.querySelector('.banner-principal');
    const secaoAnimais = document.getElementById('secaoAnimais');
    const secaoSobre = document.querySelector('.secao-sobre');
    const secaoEquipe = document.querySelector('.secao-equipe');
    const paginaTodos = document.getElementById('paginaTodosAnimais');
    const paginaFavoritos = document.getElementById('paginaFavoritos');
    
    if (banner) banner.style.display = 'block';
    if (secaoAnimais) secaoAnimais.style.display = 'block';
    if (secaoSobre) secaoSobre.style.display = 'block';
    if (secaoEquipe) secaoEquipe.style.display = 'block';
    if (paginaTodos) paginaTodos.style.display = 'none';
    if (paginaFavoritos) paginaFavoritos.style.display = 'none';
    
    window.scrollTo(0, 0);
}

function atualizarInterfaceUsuario() {
    const botaoUsuario = document.getElementById('botaoUsuario');
    if (!botaoUsuario) return;

    // Garante que o wrapper sempre seja row (não herda column do acao-cabecalho)
    botaoUsuario.style.cssText = 'display:flex;flex-direction:row;align-items:center;gap:20px;cursor:pointer;background:none;border:none;padding:0;';

    if (usuarioLogado) {
        // Exibe o botão "Painel Admin" apenas se o perfil do usuário for 'admin'
        const btnAdmin = usuarioLogado.perfil === 'admin'
            ? `<a href="adimpage.php" id="btnPainelAdmin"
                    style="display:inline-flex;align-items:center;gap:6px;
                           background:var(--primaria);color:#fff;border:none;border-radius:20px;
                           padding:6px 14px;font-size:0.78rem;font-weight:700;
                           text-decoration:none;white-space:nowrap;cursor:pointer;
                           transition:background 0.2s,transform 0.2s;">
                    <i class="fas fa-shield-alt"></i> Painel Admin
               </a>`
            : '';

        botaoUsuario.innerHTML = `
            <div style="display:flex;flex-direction:column;align-items:center;line-height:1;">
                <i class="far fa-user" id="iconeUsuario" style="font-size:1.4rem;margin-bottom:4px;color:#2d3748;"></i>
                <span id="textoUsuario" style="font-size:0.8rem;font-weight:600;color:#333;white-space:nowrap;">${usuarioLogado.nome}</span>
            </div>
            ${btnAdmin}
            <button id="btnSairEfetivo" onclick="deslogarUsuario(event)"
                style="display:inline-flex;flex-direction:row;align-items:center;gap:6px;background:transparent;border:none;outline:none;color:#e53e3e;padding:0;font-size:1rem;font-weight:700;text-transform:uppercase;cursor:pointer;white-space:nowrap;">
                <i class="fas fa-sign-out-alt" style="font-size:1.3rem;color:#e53e3e;"></i> Sair
            </button>
        `;
    } else {
        botaoUsuario.innerHTML = `
            <div style="display:flex;flex-direction:column;align-items:center;line-height:1;">
                <i class="far fa-user" id="iconeUsuario" style="font-size:1.4rem;margin-bottom:4px;color:#2d3748;"></i>
                <span id="textoUsuario" style="font-size:0.8rem;font-weight:600;color:#333;white-space:nowrap;">Entrar/Cadastrar</span>
            </div>
        `;
    }
}

// Trata o clique no botão Sair sem abrir o modal de login
function deslogarUsuario(event) {
    event.stopPropagation();
    usuarioLogado = null;
    atualizarInterfaceUsuario();
    alert('Sessão encerrada com sucesso.');
}
function abrirLogin() {
    document.getElementById('modalLogin').style.display = 'flex';
    alternarFormulario('cadastro');
    document.body.style.overflow = 'hidden';
}

function fecharModal(idModal) {
    document.getElementById(idModal).style.display = 'none';
    document.body.style.overflow = '';
}

function alternarFormulario(tipo) {
    const formCadastro = document.getElementById('formCadastro');
    const formLogin = document.getElementById('formLogin');
    const modalTitle = document.getElementById('modalTitle');
    
    if (tipo === 'cadastro') {
        if (formCadastro) formCadastro.style.display = 'block';
        if (formLogin) formLogin.style.display = 'none';
        if (modalTitle) modalTitle.textContent = 'Crie sua Conta';
    } else {
        if (formCadastro) formCadastro.style.display = 'none';
        if (formLogin) formLogin.style.display = 'block';
        if (modalTitle) modalTitle.textContent = 'Faça seu Login';
    }
}

async function fazerLogout() {
    const result = await fetchAPI('api.php?acao=logout');
    usuarioLogado = null;
    atualizarInterfaceUsuario();
    alert('Sessão encerrada com sucesso!');
    window.location.href = 'index.php'; // Força o redirecionamento imediato e limpa o estado
}

// ========== AJUDA ==========
function alternarDuvida(elemento) {
    elemento.classList.toggle('ativo');
    const conteudo = elemento.nextElementSibling; // CORRIGIDO: de element para elemento
    conteudo.classList.toggle('aberto');
}
function abrirCentralAjuda() {
    const modal = document.getElementById('modal-Ajuda');
    if (modal) modal.style.display = 'flex';
}

function abrirWhatsApp() {
    window.open('https://wa.me/5547997565199', '_blank');
}

function abrirEmail() {
    window.location.href = 'mailto:sac@petvida.org.br';
}

function abrirHorarioAtendimento() {
    alert('Horário de Atendimento:\nSegunda a Sexta: 8h às 18h\nSábado: 9h às 13h');
}

function abrirSobreNos() {
    const modal = document.getElementById('modalSobre');
    if (modal) modal.style.display = 'flex';
}

// ========== FILTROS ==========
async function filtrarAnimais() {
    const especie = document.getElementById('filtroEspecie')?.value || '';
    const sexo = document.getElementById('filtroSexo')?.value || '';
    const porte = document.getElementById('filtroPorte')?.value || '';
    
    await carregarAnimais('gradeTodosAnimais', null, { especie, sexo, porte });
}

// ========== BUSCA ==========
async function buscarAnimais() {
    const termo = document.getElementById('campo-busca')?.value.toLowerCase();
    if (!termo) return;
    
    verTodosAnimais();
    
    setTimeout(async () => {
        const result = await fetchAPI('api.php?acao=listar_animais');
        if (result.error) return;
        
        const filtrados = result.filter(a => 
            a.nome.toLowerCase().includes(termo) || 
            (a.raca && a.raca.toLowerCase().includes(termo))
        );
        
        const container = document.getElementById('gradeTodosAnimais');
        if (filtrados.length > 0) {
            container.innerHTML = filtrados.map(animal => criarCardAnimal(animal)).join('');
        } else {
            container.innerHTML = '<p style="text-align:center;">Nenhum animal encontrado.</p>';
        }
    }, 100);
}

// ========== VERIFICAR SESSÃO ==========
async function verificarSessao() {
    const result = await fetchAPI('api.php?acao=verificar_sessao');
    if (result.success && result.usuario) {
        usuarioLogado = result.usuario;
        atualizarInterfaceUsuario();
    }
}

// ========== EVENTOS E INICIALIZAÇÃO ==========
document.addEventListener('DOMContentLoaded', function() {
    verificarSessao();
    carregarAnimais('gradeAnimaisDestaque', 4);
    iniciarCarrossel();
    
    const banner = document.querySelector('.banner-principal');
    if (banner) {
        banner.addEventListener('mouseenter', pararCarrossel);
        banner.addEventListener('mouseleave', iniciarCarrossel);
    }
    
    const botaoBusca = document.getElementById('botao-busca');
    if (botaoBusca) {
        botaoBusca.addEventListener('click', buscarAnimais);
    }
    
    const campoBusca = document.getElementById('campo-busca');
    if (campoBusca) {
        campoBusca.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') buscarAnimais();
        });
    }
    
    // CORREÇÃO LOGOUT / LOGIN DINÂMICO: Trata o clique no container conforme estado da sessão
    const botaoUsuario = document.getElementById('botaoUsuario');
    if (botaoUsuario) {
        botaoUsuario.addEventListener('click', function(e) {
            if (usuarioLogado) {
                // Se clicou especificamente no gatilho interno ou se o clique veio do botão Sair
                if (e.target.id === 'btnSairEfetivo' || e.target.closest('#btnSairEfetivo')) {
                    if (confirm('Deseja realmente sair da sua conta?')) {
                        fazerLogout();
                    }
                }
            } else {
                abrirLogin();
            }
        });
    }
    
    const btnFavoritos = document.getElementById('btnFavoritos');
    if (btnFavoritos) {
        btnFavoritos.addEventListener('click', verFavoritos);
    }
    
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
            document.body.style.overflow = '';
        }
        if (event.target.classList.contains('modal-animal-box') && event.target === document.getElementById('modalAnimal')) {
            fecharModalAnimal();
        }
    };
    
    // Eventos de formulário
    const cadastroForm = document.getElementById('cadastroForm');
    if (cadastroForm) {
        cadastroForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('.btn');
            btn.disabled = true;
            btn.innerHTML = 'Cadastrando...';
            
            // Lendo TODOS os campos do formulário atualizado
            const dadosCadastro = {
                nome_usuario: document.getElementById('nome')?.value,
                nome_login: document.getElementById('nome_login')?.value,
                idade: document.getElementById('idade')?.value,
                email: document.getElementById('email')?.value,
                senha: document.getElementById('senha')?.value,
                telefone: document.getElementById('telefone')?.value,
                cpf: document.getElementById('cpf')?.value,
                data_nascimento: document.getElementById('data_nascimento')?.value,
                endereco: document.getElementById('endereco')?.value,
                cidade: document.getElementById('cidade')?.value,
                estado: document.getElementById('estado')?.value
            };
            
            const result = await fetchAPI('api.php?acao=cadastrar_usuario', {
                method: 'POST',
                body: JSON.stringify(dadosCadastro)
            });
            
            if (result.success) {
                const successMsg = document.getElementById('cadastroSuccess');
                if (successMsg) {
                    successMsg.textContent = 'Cadastro realizado com sucesso!';
                    successMsg.style.display = 'block';
                }
                setTimeout(() => {
                    alternarFormulario('login');
                    if (successMsg) successMsg.style.display = 'none';
                    cadastroForm.reset();
                }, 2000);
            } else {
                alert(result.error || 'Erro ao realizar cadastro.');
            }
            
            btn.disabled = false;
            btn.innerHTML = 'Cadastrar';
        });
    }
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('.btn');
            btn.disabled = true;
            btn.innerHTML = 'Entrando...';
            
            const result = await fetchAPI('api.php?acao=login', {
                method: 'POST',
                body: JSON.stringify({
                    email: document.getElementById('loginEmail')?.value,
                    senha: document.getElementById('loginSenha')?.value
                })
            });
            
            if (result.success) {
                usuarioLogado = result.usuario;
                atualizarInterfaceUsuario();
                fecharModal('modalLogin');
                if (document.getElementById('paginaFavoritos').style.display === 'block') {
                    carregarFavoritos();
                }
                alert(`Bem-vindo(a), ${usuarioLogado.nome}!`);
            } else {
                const errorMsg = document.getElementById('loginSenhaError');
                if (errorMsg) errorMsg.textContent = result.error || 'E-mail ou senha inválidos';
            }
            
            btn.disabled = false;
            btn.innerHTML = 'Entrar';
        });
    }
});