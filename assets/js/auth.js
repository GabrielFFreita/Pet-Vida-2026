function limparMensagem(id) {
    const elemento = document.getElementById(id);
    if (elemento) {
        elemento.textContent = '';
        elemento.style.display = 'none';
    }
}

function preencherNomeLogin() {
    const nomeLogin = document.getElementById('nome_login');
    const email = document.getElementById('email');

    if (nomeLogin && email) {
        nomeLogin.value = email.value.trim();
    }
}

function obterRedirectPosLogin() {
    const params = new URLSearchParams(window.location.search);
    return params.get('redirect') || 'index.php';
}

async function inicializarCadastro() {
    const cadastroForm = document.getElementById('cadastroForm');
    if (!cadastroForm) return;

    const email = document.getElementById('email');
    if (email) {
        email.addEventListener('input', preencherNomeLogin);
    }

    cadastroForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        limparMensagem('cadastroSuccess');
        preencherNomeLogin();

        const btn = cadastroForm.querySelector('.btn');
        if (!btn) return;

        btn.disabled = true;
        btn.textContent = 'Cadastrando...';

        const dadosCadastro = {
            nome_usuario: document.getElementById('nome')?.value?.trim() || '',
            nome_login: document.getElementById('nome_login')?.value?.trim() || document.getElementById('email')?.value?.trim() || '',
            idade: document.getElementById('idade')?.value || '',
            email: document.getElementById('email')?.value?.trim() || '',
            senha: document.getElementById('senha')?.value || '',
            telefone: document.getElementById('telefone')?.value?.trim() || '',
            cpf: document.getElementById('cpf')?.value?.trim() || '',
            data_nascimento: document.getElementById('data_nascimento')?.value || '',
            endereco: document.getElementById('endereco')?.value?.trim() || '',
            cidade: document.getElementById('cidade')?.value?.trim() || '',
            estado: document.getElementById('estado')?.value || ''
        };

        const result = await fetchAPI('api.php?acao=cadastrar_usuario', {
            method: 'POST',
            body: JSON.stringify(dadosCadastro)
        });

        if (result.success) {
            const successMsg = document.getElementById('cadastroSuccess');
            if (successMsg) {
                successMsg.textContent = 'Cadastro realizado com sucesso! Redirecionando para o login...';
                successMsg.style.display = 'block';
            }

            await fetchAPI('api.php?acao=logout');
            cadastroForm.reset();

            window.setTimeout(() => {
                window.location.href = 'login.php';
            }, 1800);
        } else {
            alert(result.error || 'Erro ao realizar cadastro.');
        }

        btn.disabled = false;
        btn.textContent = 'Cadastrar';
    });
}

async function inicializarLogin() {
    const loginForm = document.getElementById('loginForm');
    if (!loginForm) return;

    loginForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        limparMensagem('loginSuccess');

        const emailError = document.getElementById('loginEmailError');
        const senhaError = document.getElementById('loginSenhaError');
        if (emailError) emailError.textContent = '';
        if (senhaError) senhaError.textContent = '';

        const btn = loginForm.querySelector('.btn');
        if (!btn) return;

        btn.disabled = true;
        btn.textContent = 'Entrando...';

        const result = await fetchAPI('api.php?acao=login', {
            method: 'POST',
            body: JSON.stringify({
                email: document.getElementById('loginEmail')?.value?.trim() || '',
                senha: document.getElementById('loginSenha')?.value || ''
            })
        });

        if (result.success && result.usuario) {
            usuarioLogado = result.usuario;
            atualizarInterfaceUsuario();

            const successMsg = document.getElementById('loginSuccess');
            if (successMsg) {
                successMsg.textContent = 'Login realizado com sucesso!';
                successMsg.style.display = 'block';
            }

            window.setTimeout(() => {
                window.location.href = obterRedirectPosLogin();
            }, 400);
        } else if (senhaError) {
            senhaError.textContent = result.error || 'E-mail ou senha invalidos';
        }

        btn.disabled = false;
        btn.textContent = 'Entrar';
    });
}

document.addEventListener('DOMContentLoaded', function () {
    inicializarCadastro();
    inicializarLogin();
});
