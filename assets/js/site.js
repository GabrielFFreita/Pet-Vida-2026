let usuarioLogado = null;
let animalSelecionado = null;
let slideAtual = 0;
let intervaloCarrossel;
let tipoDoacaoSelecionado = null;
let menuUsuarioAberto = false;

const sugestoesDoacao = {
    'Ração': [
        'Ração seca para cães e gatos (filhotes, adultos e idosos)',
        'Ração úmida (saches/latas)',
        'Petiscos saudáveis'
    ],
    Roupa: [
        'Cobertores e mantas',
        'Casaquinhos para dias frios',
        'Toalhas de banho'
    ],
    Brinquedo: [
        'Bolinhas e mordedores',
        'Brinquedos de corda',
        'Arranhadores para gatos'
    ],
    Medicamento: [
        'Vermífugos',
        'Antipulgas e carrapatos',
        'Vacinas (V8/V10, antirrábica)',
        'Soro fisiológico, gaze e curativos'
    ]
};

function obterUrlAtual() {
    return `${window.location.pathname.split('/').pop() || 'index.php'}${window.location.search}`;
}

function montarUrlLogin(redirect = null) {
    const destino = redirect || obterUrlAtual();
    return `login.php?redirect=${encodeURIComponent(destino)}`;
}

function redirecionarParaLogin(redirect = null) {
    window.location.href = montarUrlLogin(redirect);
}

function escaparHtml(valor) {
    return String(valor ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function obterPrimeiroNome(nome) {
    const nomeLimpo = String(nome ?? '').trim();
    if (!nomeLimpo) return 'Conta';

    return nomeLimpo.split(/\s+/)[0];
}

function obterIniciaisUsuario(nome) {
    const partes = String(nome ?? '').trim().split(/\s+/).filter(Boolean);
    if (!partes.length) return 'PV';

    return partes.slice(0, 2).map((parte) => parte[0].toUpperCase()).join('');
}

function obterUrlPainelControle() {
    return 'admin/dashboard.php';
}

function obterEstadoVisualUsuario() {
    if (!usuarioLogado) {
        return {
            nomeCompleto: '',
            primeiroNome: 'Entrar',
            email: '',
            iniciais: 'PV',
            perfil: 'visitante',
            ehAdmin: false
        };
    }

    return {
        nomeCompleto: usuarioLogado.nome || 'Usuario Pet Vida',
        primeiroNome: obterPrimeiroNome(usuarioLogado.nome),
        email: usuarioLogado.email || '',
        iniciais: obterIniciaisUsuario(usuarioLogado.nome),
        perfil: usuarioLogado.perfil || 'user',
        ehAdmin: usuarioLogado.perfil === 'admin'
    };
}

function mostrarSlide(indice) {
    const slides = document.querySelectorAll('.slide-banner');
    const indicadores = document.querySelectorAll('.indicador');
    if (!slides.length) return;

    if (indice >= slides.length) slideAtual = 0;
    else if (indice < 0) slideAtual = slides.length - 1;
    else slideAtual = indice;

    slides.forEach((slide) => slide.classList.remove('ativo'));
    indicadores.forEach((indicador) => indicador.classList.remove('ativo'));
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
    const slides = document.querySelectorAll('.slide-banner');
    if (!slides.length) return;

    if (intervaloCarrossel) clearInterval(intervaloCarrossel);
    intervaloCarrossel = setInterval(() => mudarSlide(1), 5000);
}

function pararCarrossel() {
    clearInterval(intervaloCarrossel);
}

async function fetchAPI(endpoint, options = {}) {
    try {
        const response = await fetch(endpoint, {
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

    const temCardsPhp = container.querySelectorAll('.cartao-animal').length > 0;
    if (temCardsPhp && Object.keys(filtros).length === 0) {
        container.querySelectorAll('.cartao-animal').forEach((card) => {
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
        container.innerHTML = '<p style="text-align:center;">Nenhum animal disponivel para adocao no momento.</p>';
        return;
    }

    container.innerHTML = animais.map((animal) => criarCardAnimal(animal)).join('');

    animais.forEach((animal) => {
        atualizarIconeFavorito(animal.id_animal);
    });
}

function criarCardAnimal(animal) {
    const sexoClass = animal.sexo === 'Macho' ? 'macho' : 'femea';
    const idadeFormatada = animal.idade ? `${animal.idade} ${animal.idade == 1 ? 'ano' : 'anos'}` : 'Idade nao informada';
    const foto = animal.ds_img ? `uploads/${animal.ds_img}` : 'https://placehold.co/400x400?text=Sem+Foto';

    return `
        <div class="cartao-animal" data-id="${animal.id_animal}" onclick="abrirDetalhesAnimal(${animal.id_animal})">
            <div class="imagem-animal">
                <img src="${foto}" alt="${animal.nome}" onerror="this.src='https://placehold.co/400x400?text=Sem+Imagem'">
                <span class="etiqueta-sexo ${sexoClass}">${animal.sexo}</span>
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

async function toggleFavorito(idAnimal, elemento) {
    if (!usuarioLogado) {
        alert('Voce precisa fazer login para favoritar animais.');
        redirecionarParaLogin();
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
        elemento.classList.toggle('favoritado', !!result.favoritado);
    } else {
        alert('Erro ao favoritar. Tente novamente.');
    }
}

async function atualizarIconeFavorito(idAnimal) {
    if (!usuarioLogado) return;

    const result = await fetchAPI(`api.php?acao=verificar_favorito&id_usuario=${usuarioLogado.id_usuario}&id_animal=${idAnimal}`);

    if (result.success && result.favoritado) {
        const botoes = document.querySelectorAll(`.cartao-animal[data-id="${idAnimal}"] .btn-favorito`);
        botoes.forEach((btn) => btn.classList.add('favoritado'));
    }
}

async function carregarFavoritos() {
    const container = document.getElementById('gradeFavoritos');
    if (!container) return;

    if (!usuarioLogado) {
        container.innerHTML = '<p style="text-align:center;">Faca login para ver seus animais favoritados.</p>';
        return;
    }

    container.innerHTML = '<div class="loading"><div class="spinner"></div> Carregando...</div>';

    const result = await fetchAPI(`api.php?acao=listar_favoritos&id_usuario=${usuarioLogado.id_usuario}`);

    if (result.error) {
        container.innerHTML = '<p style="text-align:center;">Erro ao carregar favoritos.</p>';
        return;
    }

    if (result.length === 0) {
        container.innerHTML = '<p style="text-align:center;">Voce nao tem animais favoritados.</p>';
        return;
    }

    container.innerHTML = result.map((animal) => criarCardAnimal(animal)).join('');
}

function abrirModalDoacao() {
    const modal = document.getElementById('modalDoacao');
    if (!modal) return;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function fecharModalDoacao() {
    const modal = document.getElementById('modalDoacao');
    if (!modal) return;

    modal.style.display = 'none';
    document.body.style.overflow = '';
    tipoDoacaoSelecionado = null;

    document.querySelectorAll('.opcao-doacao').forEach((opt) => opt.classList.remove('selecionado'));

    const valorDiv = document.getElementById('valorDoacaoDiv');
    const outroDiv = document.getElementById('outroDoacao');
    const infoDiv = document.getElementById('infoDoacaoDiv');
    const valorDoacao = document.getElementById('valorDoacao');
    const descricaoOutro = document.getElementById('descricaoOutro');
    const detalheDoacao = document.getElementById('detalheDoacao');

    if (valorDiv) valorDiv.style.display = 'none';
    if (outroDiv) outroDiv.style.display = 'none';
    if (infoDiv) infoDiv.style.display = 'none';
    if (valorDoacao) valorDoacao.value = '';
    if (descricaoOutro) descricaoOutro.value = '';
    if (detalheDoacao) detalheDoacao.value = '';
}

function copiarChavePix() {
    const campo = document.getElementById('chavePix');
    if (!campo) return;

    navigator.clipboard.writeText(campo.value).then(() => {
        alert('Chave Pix copiada!');
    }).catch(() => {
        campo.select();
        document.execCommand('copy');
        alert('Chave Pix copiada!');
    });
}

function selecionarTipoDoacao(tipo, elemento) {
    const valorDiv = document.getElementById('valorDoacaoDiv');
    const outroDiv = document.getElementById('outroDoacao');
    const infoDiv = document.getElementById('infoDoacaoDiv');
    const listaSugestoes = document.getElementById('listaSugestoesDoacao');
    const detalheDoacao = document.getElementById('detalheDoacao');

    document.querySelectorAll('.opcao-doacao').forEach((opt) => opt.classList.remove('selecionado'));
    elemento.classList.add('selecionado');
    tipoDoacaoSelecionado = tipo;

    if (valorDiv) valorDiv.style.display = 'none';
    if (outroDiv) outroDiv.style.display = 'none';
    if (infoDiv) infoDiv.style.display = 'none';

    if (tipo === 'Dinheiro') {
        if (valorDiv) valorDiv.style.display = 'block';
        return;
    }

    if (tipo === 'Outro') {
        if (outroDiv) outroDiv.style.display = 'block';
        return;
    }

    if (sugestoesDoacao[tipo] && listaSugestoes && infoDiv) {
        listaSugestoes.innerHTML = sugestoesDoacao[tipo].map((item) => `<li>${item}</li>`).join('');
        if (detalheDoacao) detalheDoacao.value = '';
        infoDiv.style.display = 'block';
    }
}

async function enviarDoacao() {
    if (!usuarioLogado) {
        alert('Voce precisa fazer login para realizar uma doacao.');
        fecharModalDoacao();
        redirecionarParaLogin();
        return;
    }

    if (!tipoDoacaoSelecionado) {
        alert('Selecione um tipo de doacao.');
        return;
    }

    let descricao = '';
    let valor = null;

    if (tipoDoacaoSelecionado === 'Dinheiro') {
        const valorInformado = document.getElementById('valorDoacao')?.value || '';
        if (valorInformado) {
            valor = parseFloat(valorInformado);
            if (Number.isNaN(valor) || valor <= 0) {
                alert('Informe um valor valido para doacao.');
                return;
            }
            descricao = `Doacao via Pix de R$ ${valor.toFixed(2)}`;
        } else {
            descricao = 'Doacao via Pix';
        }
    } else if (tipoDoacaoSelecionado === 'Outro') {
        descricao = document.getElementById('descricaoOutro')?.value?.trim() || '';
        if (!descricao) {
            alert('Descreva o que voce deseja doar.');
            return;
        }
    } else {
        const detalhe = document.getElementById('detalheDoacao')?.value?.trim() || '';
        if (!detalhe) {
            alert('Descreva o que voce vai enviar.');
            return;
        }
        descricao = `Doacao de ${tipoDoacaoSelecionado}: ${detalhe}`;
    }

    const result = await fetchAPI('api.php?acao=doar', {
        method: 'POST',
        body: JSON.stringify({
            id_usuario: usuarioLogado.id_usuario,
            tipo_doacao: tipoDoacaoSelecionado,
            descricao,
            valor
        })
    });

    if (result.success) {
        alert('Doacao registrada com sucesso! Muito obrigado!');
        fecharModalDoacao();
    } else {
        alert('Erro ao registrar doacao. Tente novamente.');
    }
}

async function abrirDetalhesAnimal(id) {
    const result = await fetchAPI(`api.php?acao=buscar_animal&id=${id}`);
    if (result.error) {
        alert('Erro ao carregar detalhes do animal.');
        return;
    }

    animalSelecionado = result;

    const foto = animalSelecionado.ds_img ? `uploads/${animalSelecionado.ds_img}` : 'https://placehold.co/400x400?text=Sem+Foto';
    const modal = document.getElementById('modalAnimal');
    if (!modal) return;

    document.getElementById('modalAnimalImg').src = foto;
    document.getElementById('modalAnimalNome').textContent = animalSelecionado.nome;
    document.getElementById('modalAnimalRaca').textContent = animalSelecionado.raca || 'SRD';
    document.getElementById('modalAnimalSexo').innerHTML = `<i class="fas fa-${animalSelecionado.sexo === 'Macho' ? 'mars' : 'venus'}"></i> ${animalSelecionado.sexo}`;
    document.getElementById('modalAnimalIdade').textContent = animalSelecionado.idade || '?';
    document.getElementById('modalAnimalEspecie').innerHTML = `<i class="fas fa-${animalSelecionado.especie === 'Cachorro' ? 'dog' : 'cat'}"></i> ${animalSelecionado.especie}`;
    document.getElementById('modalAnimalPeso').textContent = animalSelecionado.peso || '?';
    document.getElementById('modalAnimalPorte').textContent = animalSelecionado.porte || 'Nao informado';
    document.getElementById('modalAnimalVacinado').innerHTML = animalSelecionado.vacinado ? '<i class="fas fa-check-circle" style="color:#4caf50"></i> Sim' : '<i class="fas fa-times-circle" style="color:#f44336"></i> Nao';
    document.getElementById('modalAnimalDescricao').textContent = animalSelecionado.descricao || 'Sem descricao disponivel.';

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function abrirPreviewAnimal(card) {
    const modal = document.getElementById('modalAnimal');
    if (!card || !modal) return;

    document.getElementById('modalAnimalImg').src = card.dataset.img || '';
    document.getElementById('modalAnimalNome').textContent = card.dataset.nome || '';
    document.getElementById('modalAnimalRaca').textContent = card.dataset.raca || '';
    document.getElementById('modalAnimalSexo').textContent = card.dataset.sexo || '';
    document.getElementById('modalAnimalIdade').textContent = card.dataset.idade || '';
    document.getElementById('modalAnimalEspecie').textContent = card.dataset.especie || '';
    document.getElementById('modalAnimalPeso').textContent = card.dataset.peso || '';
    document.getElementById('modalAnimalPorte').textContent = card.dataset.porte || '';
    document.getElementById('modalAnimalVacinado').textContent = card.dataset.vacinado || '';
    document.getElementById('modalAnimalDescricao').textContent = card.dataset.descricao || '';

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function fecharModalAnimal() {
    const modal = document.getElementById('modalAnimal');
    if (!modal) return;

    modal.style.display = 'none';
    document.body.style.overflow = '';
}

async function solicitarAdocao() {
    if (!usuarioLogado) {
        alert('Voce precisa fazer login para solicitar uma adocao.');
        fecharModalAnimal();
        redirecionarParaLogin();
        return;
    }

    if (!animalSelecionado) return;

    const confirmar = confirm(`Voce realmente deseja adotar ${animalSelecionado.nome}?`);
    if (!confirmar) return;

    const result = await fetchAPI('api.php?acao=solicitar_adocao', {
        method: 'POST',
        body: JSON.stringify({
            id_usuario: usuarioLogado.id_usuario,
            id_animal: animalSelecionado.id_animal
        })
    });

    if (result.success) {
        alert('Solicitacao de adocao enviada com sucesso!');
        fecharModalAnimal();
        carregarAnimais('gradeAnimaisDestaque', 4);
        if (document.getElementById('gradeTodosAnimais')) {
            carregarAnimais('gradeTodosAnimais');
        }
    } else {
        alert('Erro ao enviar solicitacao. Tente novamente.');
    }
}

function toggleLerMaisGeral() {
    const secao = document.querySelector('.secao-sobre');
    const btn = document.querySelector('.btn-ler-mais-sobre');
    if (!secao || !btn) return;

    secao.classList.toggle('expandido');
    btn.innerHTML = secao.classList.contains('expandido')
        ? '<i class="fas fa-chevron-up"></i> Ler menos'
        : '<i class="fas fa-chevron-down"></i> Ler mais';
}

function abrirLogin(redirect = null) {
    redirecionarParaLogin(redirect);
}

function irParaAdocao(e) {
    if (e?.preventDefault) e.preventDefault();

    if (!usuarioLogado) {
        alert('Voce precisa se cadastrar para ver os animais disponiveis para adocao.');
        redirecionarParaLogin('adocao.php');
        return false;
    }

    window.location.href = 'adocao.php';
    return false;
}

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
        alert('Faca login para ver seus favoritos.');
        redirecionarParaLogin();
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
    const menuWrapper = document.getElementById('botaoUsuario');
    const dropdown = document.getElementById('menuUsuarioDropdown');
    if (!menuWrapper) return;

    const dadosUsuario = obterEstadoVisualUsuario();
    const conteudoCabecalho = document.querySelector('.conteudo-cabecalho');
    if (conteudoCabecalho) {
        conteudoCabecalho.classList.toggle('tem-usuario-logado', !!usuarioLogado);
    }

    if (usuarioLogado) {
        menuWrapper.innerHTML = `
            <button
                id="usuarioMenuTrigger"
                class="usuario-trigger"
                type="button"
                aria-haspopup="true"
                aria-expanded="${menuUsuarioAberto ? 'true' : 'false'}"
                aria-label="Abrir menu da conta de ${escaparHtml(dadosUsuario.nomeCompleto)}"
            >
                <span class="usuario-avatar" aria-hidden="true">${escaparHtml(dadosUsuario.iniciais)}</span>
                <span class="usuario-trigger-textos">
                    <span class="usuario-trigger-nome">${escaparHtml(dadosUsuario.primeiroNome)}</span>
                    <span class="usuario-status">
                        <span class="usuario-status-dot" aria-hidden="true"></span>
                        <span>Logado</span>
                    </span>
                </span>
                <i class="fas fa-chevron-down usuario-trigger-seta" aria-hidden="true"></i>
            </button>
        `;

        if (dropdown) {
            dropdown.innerHTML = `
                <div class="usuario-dropdown-header">
                    <div class="usuario-dropdown-avatar" aria-hidden="true">${escaparHtml(dadosUsuario.iniciais)}</div>
                    <p class="usuario-dropdown-conta">Conta Pet Vida</p>
                    <p class="usuario-dropdown-nome">${escaparHtml(dadosUsuario.nomeCompleto)}</p>
                    <p class="usuario-dropdown-email">${escaparHtml(dadosUsuario.email || 'E-mail nao informado')}</p>
                </div>
                <button type="button" class="usuario-dropdown-item" role="menuitem" onclick="irParaPerfil()">
                    <i class="fas fa-user-edit" aria-hidden="true"></i>
                    <span>Alterar meus dados</span>
                </button>
                ${dadosUsuario.ehAdmin ? `
                    <button type="button" class="usuario-dropdown-item" role="menuitem" onclick="irParaPainelControle()">
                        <i class="fas fa-shield-alt" aria-hidden="true"></i>
                        <span>Pet Vida Admin</span>
                    </button>
                ` : ''}
                <button type="button" class="usuario-dropdown-item usuario-dropdown-sair" role="menuitem" onclick="deslogarUsuario(event)">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                    <span>Sair</span>
                </button>
            `;
        }
    } else {
        menuUsuarioAberto = false;
        menuWrapper.innerHTML = `
            <div class="usuario-acoes-visitante">
                <button
                    id="usuarioLoginTrigger"
                    class="usuario-trigger usuario-trigger--visitante"
                    type="button"
                    aria-label="Acessar sua conta"
                >
                    <span class="usuario-avatar usuario-avatar--visitante" aria-hidden="true">
                        <i class="far fa-user"></i>
                    </span>
                    <span class="usuario-trigger-textos">
                        <span class="usuario-trigger-nome">Entrar</span>
                        <span class="usuario-trigger-subtexto">Acesse sua conta</span>
                    </span>
                </button>
                <button
                    id="usuarioCadastroTrigger"
                    class="usuario-trigger usuario-trigger--cadastro"
                    type="button"
                    aria-label="Ir para cadastro"
                >
                    Cadastre-se
                </button>
            </div>
        `;

        if (dropdown) {
            dropdown.classList.remove('aberto');
            dropdown.setAttribute('aria-hidden', 'true');
            dropdown.innerHTML = '';
        }
    }

    const trigger = document.getElementById('usuarioMenuTrigger');
    if (trigger && usuarioLogado) {
        trigger.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            toggleMenuUsuario();
        });
    } else {
        const loginTrigger = document.getElementById('usuarioLoginTrigger');
        const cadastroTrigger = document.getElementById('usuarioCadastroTrigger');

        if (loginTrigger) {
            loginTrigger.addEventListener('click', function () {
                abrirLogin();
            });
        }

        if (cadastroTrigger) {
            cadastroTrigger.addEventListener('click', function () {
                window.location.href = 'cadastro.php';
            });
        }
    }
}

function abrirMenuUsuario() {
    const trigger = document.getElementById('usuarioMenuTrigger');
    const dropdown = document.getElementById('menuUsuarioDropdown');
    if (!trigger || !dropdown || !usuarioLogado) return;

    menuUsuarioAberto = true;
    dropdown.classList.add('aberto');
    dropdown.setAttribute('aria-hidden', 'false');
    trigger.setAttribute('aria-expanded', 'true');
}

function fecharMenuUsuario() {
    const trigger = document.getElementById('usuarioMenuTrigger');
    const dropdown = document.getElementById('menuUsuarioDropdown');
    if (!dropdown) return;

    menuUsuarioAberto = false;
    dropdown.classList.remove('aberto');
    dropdown.setAttribute('aria-hidden', 'true');
    if (trigger) {
        trigger.setAttribute('aria-expanded', 'false');
    }
}

function toggleMenuUsuario() {
    if (menuUsuarioAberto) {
        fecharMenuUsuario();
        return;
    }

    abrirMenuUsuario();
}

function irParaPerfil() {
    fecharMenuUsuario();
    window.location.href = 'perfil.php';
}

function irParaPainelControle() {
    fecharMenuUsuario();
    window.location.href = obterUrlPainelControle();
}

async function fazerLogout() {
    fecharMenuUsuario();

    const result = await fetchAPI('api.php?acao=logout');
    if (!result.success) {
        alert(result.error || 'Nao foi possivel encerrar a sessao.');
        return;
    }

    usuarioLogado = null;
    atualizarInterfaceUsuario();
    alert('Sessao encerrada com sucesso!');
    window.location.href = 'index.php';
}

function deslogarUsuario(event) {
    if (event) event.stopPropagation();
    fazerLogout();
}

function fecharModal(idModal) {
    const modal = document.getElementById(idModal);
    if (!modal) return;

    modal.style.display = 'none';
    document.body.style.overflow = '';
}

function alternarDuvida(elemento) {
    elemento.classList.toggle('ativo');
    const conteudo = elemento.nextElementSibling;
    if (conteudo) conteudo.classList.toggle('aberto');
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
    alert('Horario de Atendimento:\nSegunda a Sexta: 8h as 18h\nSabado: 9h as 13h');
}

function abrirSobreNos() {
    const modal = document.getElementById('modalSobre');
    if (modal) modal.style.display = 'flex';
}

async function filtrarAnimais() {
    const especie = document.getElementById('filtroEspecie')?.value || '';
    const sexo = document.getElementById('filtroSexo')?.value || '';
    const porte = document.getElementById('filtroPorte')?.value || '';
    await carregarAnimais('gradeTodosAnimais', null, { especie, sexo, porte });
}

async function buscarAnimais() {
    const termo = document.getElementById('campo-busca')?.value?.toLowerCase().trim() || '';
    if (!termo) return;

    const container = document.getElementById('gradeTodosAnimais');
    if (!container) {
        window.location.href = `index.php?busca=${encodeURIComponent(termo)}`;
        return;
    }

    verTodosAnimais();

    window.setTimeout(async () => {
        const result = await fetchAPI('api.php?acao=listar_animais');
        if (result.error) return;

        const filtrados = result.filter((animal) =>
            animal.nome.toLowerCase().includes(termo) ||
            (animal.raca && animal.raca.toLowerCase().includes(termo))
        );

        if (filtrados.length > 0) {
            container.innerHTML = filtrados.map((animal) => criarCardAnimal(animal)).join('');
        } else {
            container.innerHTML = '<p style="text-align:center;">Nenhum animal encontrado.</p>';
        }
    }, 100);
}

async function verificarSessao() {
    const result = await fetchAPI('api.php?acao=verificar_sessao');
    if (result.success && result.usuario) {
        usuarioLogado = result.usuario;
    } else {
        usuarioLogado = null;
    }

    atualizarInterfaceUsuario();
    return result;
}

document.addEventListener('DOMContentLoaded', function () {
    verificarSessao();
    carregarAnimais('gradeAnimaisDestaque', 4);

    const banner = document.querySelector('.banner-principal');
    if (banner) {
        iniciarCarrossel();
        banner.addEventListener('mouseenter', pararCarrossel);
        banner.addEventListener('mouseleave', iniciarCarrossel);
    }

    const botaoBusca = document.getElementById('botao-busca');
    if (botaoBusca) {
        botaoBusca.addEventListener('click', buscarAnimais);
    }

    const campoBusca = document.getElementById('campo-busca');
    if (campoBusca) {
        campoBusca.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') buscarAnimais();
        });
    }

    const botaoDoacao = document.querySelector('.botao-doacao');
    if (botaoDoacao) {
        botaoDoacao.addEventListener('click', function (e) {
            e.preventDefault();
            abrirModalDoacao();
        });
    }

    const urlBusca = new URLSearchParams(window.location.search).get('busca');
    if (urlBusca && document.getElementById('gradeTodosAnimais')) {
        const campo = document.getElementById('campo-busca');
        if (campo) campo.value = urlBusca;
        buscarAnimais();
    }

    window.onclick = function (event) {
        const dropdown = document.getElementById('menuUsuarioDropdown');
        const wrapper = document.querySelector('.acao-cabecalho-usuario');
        if (menuUsuarioAberto && dropdown && wrapper && !wrapper.contains(event.target)) {
            fecharMenuUsuario();
        }

        if (event.target.classList?.contains('modal')) {
            event.target.style.display = 'none';
            document.body.style.overflow = '';
        }
    };

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && menuUsuarioAberto) {
            fecharMenuUsuario();
            document.getElementById('usuarioMenuTrigger')?.focus();
        }
    });
});
