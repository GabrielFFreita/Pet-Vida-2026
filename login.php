<?php
$pageTitle = 'Pet Vida - Login';
$bodyClass = 'pagina-auth';
$extraScripts = ['assets/js/auth.js?v=1'];
require __DIR__ . '/includes/header.php';
?>

<main class="auth-container">
    <section class="auth-card">
        <span class="auth-eyebrow">Acesse sua conta</span>
        <h1 class="auth-title">Faca login para continuar</h1>
        <p class="auth-subtitle">Entre para adotar, favoritar animais e registrar doacoes sem perder sua sessao.</p>

        <form id="loginForm" class="auth-form">
            <div class="form-group">
                <label for="loginEmail">E-mail</label>
                <input type="email" class="form-control" id="loginEmail" required>
                <span class="error-msg" id="loginEmailError"></span>
            </div>

            <div class="form-group">
                <label for="loginSenha">Senha</label>
                <input type="password" class="form-control" id="loginSenha" required>
                <span class="error-msg" id="loginSenhaError"></span>
            </div>

            <p class="success" id="loginSuccess" style="display: none;"></p>
            <button type="submit" class="btn auth-submit">Entrar</button>
        </form>

        <div class="auth-footer">
            Nao tem conta? <a href="cadastro.php" class="link">Crie sua Conta</a>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
