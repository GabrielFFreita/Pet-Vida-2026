<?php
require_once __DIR__ . '/config_sessao.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php?redirect=' . urlencode('perfil.php'));
    exit;
}

$pageTitle = 'Pet Vida - Minha Conta';
$extraScripts = ['script/perfil.js?v=1'];
require __DIR__ . '/includes/header.php';
?>

<main class="perfil-main">
    <div class="perfil-shell">
        <aside class="perfil-card">
            <div id="perfilResumo">
                <div class="perfil-hero-avatar" aria-hidden="true">PV</div>
                <span class="perfil-badge"><i class="fas fa-user-check"></i> Conta ativa</span>
                <h1>Minha conta</h1>
                <p>Revise seus dados cadastrais e mantenha suas informações atualizadas.</p>
            </div>

            <div class="perfil-info-lista" id="perfilInfoLista">
                <div class="perfil-info-item">
                    <strong>Status</strong>
                    <span>Carregando dados...</span>
                </div>
            </div>
        </aside>

        <section class="perfil-form-card">
            <h2>Alterar meus dados</h2>
            <p>Os campos abaixo atualizam apenas a conta da sessão atual. A senha só será enviada se você preencher os campos de troca.</p>

            <div id="perfilFeedback" class="perfil-feedback" role="status" aria-live="polite"></div>

            <form id="perfilForm">
                <div class="perfil-form-grid">
                    <div class="form-group">
                        <label for="perfilNome">Nome Completo</label>
                        <input type="text" class="form-control" id="perfilNome" required>
                    </div>

                    <div class="form-group">
                        <label for="perfilIdade">Idade</label>
                        <input type="number" class="form-control" id="perfilIdade" min="0">
                    </div>

                    <div class="form-group">
                        <label for="perfilEmail">E-mail</label>
                        <input type="email" class="form-control" id="perfilEmail" required>
                    </div>

                    <div class="form-group">
                        <label for="perfilTelefone">Telefone</label>
                        <input type="text" class="form-control" id="perfilTelefone">
                    </div>

                    <div class="form-group">
                        <label for="perfilCpf">CPF</label>
                        <input type="text" class="form-control" id="perfilCpf">
                    </div>

                    <div class="form-group">
                        <label for="perfilDataNascimento">Data de Nascimento</label>
                        <input type="date" class="form-control" id="perfilDataNascimento">
                    </div>

                    <div class="form-group form-group--full">
                        <label for="perfilEndereco">Endereco</label>
                        <input type="text" class="form-control" id="perfilEndereco">
                    </div>

                    <div class="form-group">
                        <label for="perfilCidade">Cidade</label>
                        <input type="text" class="form-control" id="perfilCidade">
                    </div>

                    <div class="form-group">
                        <label for="perfilEstado">Estado</label>
                        <input type="text" class="form-control" id="perfilEstado">
                    </div>

                    <div class="form-group">
                        <label for="perfilNovaSenha">Nova senha</label>
                        <input type="password" class="form-control" id="perfilNovaSenha" autocomplete="new-password">
                    </div>

                    <div class="form-group">
                        <label for="perfilConfirmarSenha">Confirmar nova senha</label>
                        <input type="password" class="form-control" id="perfilConfirmarSenha" autocomplete="new-password">
                    </div>
                </div>

                <div class="perfil-acoes">
                    <button type="submit" class="btn" id="perfilSalvarBtn">Salvar alteracoes</button>
                </div>
            </form>
        </section>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
