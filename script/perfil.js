function preencherResumoPerfil(usuario) {
    const resumo = document.getElementById('perfilResumo');
    const lista = document.getElementById('perfilInfoLista');
    if (!resumo || !lista || !usuario) return;

    const nome = escaparHtml(usuario.nome || 'Usuario Pet Vida');
    const email = escaparHtml(usuario.email || 'E-mail nao informado');
    const perfil = usuario.perfil === 'admin' ? 'Administrador' : 'Usuario';
    const iniciais = escaparHtml(obterIniciaisUsuario(usuario.nome));

    resumo.innerHTML = `
        <div class="perfil-hero-avatar" aria-hidden="true">${iniciais}</div>
        <span class="perfil-badge"><i class="fas fa-user-check"></i> Conta ativa</span>
        <h1>${nome}</h1>
        <p>${email}</p>
    `;

    lista.innerHTML = `
        <div class="perfil-info-item">
            <strong>Perfil</strong>
            <span>${escaparHtml(perfil)}</span>
        </div>
        <div class="perfil-info-item">
            <strong>Telefone</strong>
            <span>${escaparHtml(usuario.telefone || 'Nao informado')}</span>
        </div>
        <div class="perfil-info-item">
            <strong>Cidade</strong>
            <span>${escaparHtml(usuario.cidade || 'Nao informada')}</span>
        </div>
    `;
}

function preencherFormularioPerfil(usuario) {
    document.getElementById('perfilNome').value = usuario.nome || '';
    document.getElementById('perfilIdade').value = usuario.idade ?? '';
    document.getElementById('perfilEmail').value = usuario.email || '';
    document.getElementById('perfilTelefone').value = usuario.telefone || '';
    document.getElementById('perfilCpf').value = usuario.cpf || '';
    document.getElementById('perfilDataNascimento').value = usuario.data_nascimento || '';
    document.getElementById('perfilEndereco').value = usuario.endereco || '';
    document.getElementById('perfilCidade').value = usuario.cidade || '';
    document.getElementById('perfilEstado').value = usuario.estado || '';
}

function mostrarFeedbackPerfil(mensagem, tipo) {
    const feedback = document.getElementById('perfilFeedback');
    if (!feedback) return;

    feedback.className = `perfil-feedback ${tipo}`;
    feedback.textContent = mensagem;
}

async function carregarPerfilUsuario() {
    const resultado = await fetchAPI('api.php?acao=meus_dados');
    if (!resultado.success || !resultado.usuario) {
        mostrarFeedbackPerfil(resultado.error || 'Nao foi possivel carregar seus dados.', 'erro');
        return;
    }

    usuarioLogado = {
        id: resultado.usuario.id_usuario,
        id_usuario: resultado.usuario.id_usuario,
        nome: resultado.usuario.nome,
        email: resultado.usuario.email,
        perfil: resultado.usuario.perfil
    };

    atualizarInterfaceUsuario();
    preencherResumoPerfil(resultado.usuario);
    preencherFormularioPerfil(resultado.usuario);
}

async function salvarPerfilUsuario(event) {
    event.preventDefault();

    const botaoSalvar = document.getElementById('perfilSalvarBtn');
    if (!botaoSalvar) return;

    botaoSalvar.disabled = true;
    botaoSalvar.textContent = 'Salvando...';
    mostrarFeedbackPerfil('', '');

    const payload = {
        nome: document.getElementById('perfilNome')?.value?.trim() || '',
        idade: document.getElementById('perfilIdade')?.value || '',
        email: document.getElementById('perfilEmail')?.value?.trim() || '',
        telefone: document.getElementById('perfilTelefone')?.value?.trim() || '',
        cpf: document.getElementById('perfilCpf')?.value?.trim() || '',
        data_nascimento: document.getElementById('perfilDataNascimento')?.value || '',
        endereco: document.getElementById('perfilEndereco')?.value?.trim() || '',
        cidade: document.getElementById('perfilCidade')?.value?.trim() || '',
        estado: document.getElementById('perfilEstado')?.value?.trim() || '',
        nova_senha: document.getElementById('perfilNovaSenha')?.value || '',
        confirmar_senha: document.getElementById('perfilConfirmarSenha')?.value || ''
    };

    const resultado = await fetchAPI('api.php?acao=atualizar_usuario', {
        method: 'POST',
        body: JSON.stringify(payload)
    });

    if (resultado.success && resultado.usuario) {
        usuarioLogado = {
            id: resultado.usuario.id_usuario,
            id_usuario: resultado.usuario.id_usuario,
            nome: resultado.usuario.nome,
            email: resultado.usuario.email,
            perfil: resultado.usuario.perfil
        };

        atualizarInterfaceUsuario();
        preencherResumoPerfil(resultado.usuario);
        preencherFormularioPerfil(resultado.usuario);
        document.getElementById('perfilNovaSenha').value = '';
        document.getElementById('perfilConfirmarSenha').value = '';
        mostrarFeedbackPerfil('Dados atualizados com sucesso.', 'sucesso');
    } else {
        mostrarFeedbackPerfil(resultado.error || 'Nao foi possivel salvar suas alteracoes.', 'erro');
    }

    botaoSalvar.disabled = false;
    botaoSalvar.textContent = 'Salvar alteracoes';
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('perfilForm');
    if (!form) return;

    carregarPerfilUsuario();
    form.addEventListener('submit', salvarPerfilUsuario);
});
