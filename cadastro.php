<?php
$pageTitle = 'Pet Vida - Cadastro';
$bodyClass = 'pagina-auth';
$extraScripts = ['assets/js/auth.js?v=1'];
require __DIR__ . '/includes/header.php';
?>

<main class="auth-container">
    <section class="auth-card auth-card-cadastro">
        <h1 class="auth-title">Crie sua conta</h1>

        <form id="cadastroForm" class="auth-form">
            <input type="hidden" id="nome_login" value="">

            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" class="form-control" id="nome" required>
                <span class="error-msg" id="nomeError"></span>
            </div>

            <div class="form-group">
                <label for="idade">Idade</label>
                <input type="number" class="form-control" id="idade" min="0" max="120">
            </div>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" class="form-control" id="email" required>
                <span class="error-msg" id="emailError"></span>
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" class="form-control" id="senha" required>
                <span class="error-msg" id="senhaError"></span>
            </div>

            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="text" class="form-control" id="telefone" required placeholder="(00) 00000-0000">
            </div>

            <div class="form-group">
                <label for="cpf">CPF</label>
                <input type="text" class="form-control" id="cpf" required placeholder="000.000.000-00">
            </div>

            <div class="form-group">
                <label for="data_nascimento">Data de Nascimento</label>
                <input type="date" class="form-control" id="data_nascimento" required>
            </div>

            <div class="form-group">
                <label for="endereco">Endereco</label>
                <input type="text" class="form-control" id="endereco" required placeholder="Rua, numero e bairro">
            </div>

            <div class="form-group">
                <label for="cidade">Cidade</label>
                <input type="text" class="form-control" id="cidade" required>
            </div>

            <div class="form-group">
                <label for="estado">Estado</label>
                <select class="form-control" id="estado" required>
                    <option value="" disabled selected>Selecione seu estado</option>
                    <option value="AC">Acre</option>
                    <option value="AL">Alagoas</option>
                    <option value="AP">Amapa</option>
                    <option value="AM">Amazonas</option>
                    <option value="BA">Bahia</option>
                    <option value="CE">Ceara</option>
                    <option value="DF">Distrito Federal</option>
                    <option value="ES">Espirito Santo</option>
                    <option value="GO">Goias</option>
                    <option value="MA">Maranhao</option>
                    <option value="MT">Mato Grosso</option>
                    <option value="MS">Mato Grosso do Sul</option>
                    <option value="MG">Minas Gerais</option>
                    <option value="PA">Para</option>
                    <option value="PB">Paraiba</option>
                    <option value="PR">Parana</option>
                    <option value="PE">Pernambuco</option>
                    <option value="PI">Piaui</option>
                    <option value="RJ">Rio de Janeiro</option>
                    <option value="RN">Rio Grande do Norte</option>
                    <option value="RS">Rio Grande do Sul</option>
                    <option value="RO">Rondonia</option>
                    <option value="RR">Roraima</option>
                    <option value="SC">Santa Catarina</option>
                    <option value="SP">Sao Paulo</option>
                    <option value="SE">Sergipe</option>
                    <option value="TO">Tocantins</option>
                </select>
            </div>

            <p class="success" id="cadastroSuccess" style="display: none;"></p>
            <button type="submit" class="btn auth-submit">Cadastrar</button>
        </form>

        <div class="auth-footer">
            Ja tem conta? <a href="login.php" class="link">Faca Login</a>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
