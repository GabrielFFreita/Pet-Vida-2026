<?php
require_once "conexao.php";
session_start();

try {
    // Faz o JOIN com a tabela foto_animal para trazer a coluna ds_img correspondente
    $sql = "SELECT a.id_animal, a.nome, a.idade, a.raca, a.especie, a.sexo, a.porte, a.peso, a.vacinado, a.descricao, a.status_adocao, f.ds_img 
            FROM animais_adocao a 
            LEFT JOIN foto_animal f ON a.id_animal = f.id_animal 
            WHERE a.status_adocao = 'Disponível' 
            LIMIT 4";
    $stmt = $pdo->query($sql);
    $animais_destaque = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $animais_destaque = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Vida - Adote um Amigo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div vw class="enabled">
        <div vw-access-button class="active"></div>
        <div vw-plugin-wrapper>
            <div class="vw-plugin-top-wrapper"></div>
        </div>
    </div>
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script>new window.VLibras.Widget('https://vlibras.gov.br/app');</script>

    <header class="cabecalho-principal">
        <div class="conteudo-cabecalho">
            <div class="logo">
                <img src="img/Logo Pet Vida.png" alt="Logo Pet Vida">
            </div>
            
            <div class="barra-busca">
                <input type="text" id="campo-busca" placeholder="Buscar animal por nome ou raça...">
                <button id="botao-busca"><i class="fas fa-search"></i></button>
            </div>
            
            <div class="acoes-cabecalho">
                <div class="acao-cabecalho" id="btnFavoritos">
                    <i class="fas fa-heart"></i>
                    <span>Adote um amigo</span>
                </div>
                <div class="acao-cabecalho">
                    <button class="botao-doacao"><i class="fas fa-hand-holding-heart"></i> Doe/ajude</button>
                </div>
                <div class="acao-cabecalho" style="padding:0;background:none;border:none;">
                    <div id="botaoUsuario" style="display:flex;flex-direction:row;align-items:center;gap:12px;cursor:pointer;">
                        <div class="usuario-perfil-bloco" style="display:flex;flex-direction:column;align-items:center;">
                            <i class="far fa-user" id="iconeUsuario" style="font-size:1.4rem;margin-bottom:4px;"></i>
                            <span id="textoUsuario" style="font-size:0.8rem;font-weight:600;">Entrar/Cadastrar</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="banner-principal">
        <div class="slide-banner ativo">
            <img src="img/Petquiz.png" alt="Banner Pet quiz">
            <div class="conteudo-banner">
                <h1>Adote um amigo para a vida</h1>
                <p>Milhares de animais esperando por um lar cheio de amor</p>
            </div>
        </div>
        <div class="slide-banner">
            <div class="video-wrapper">
                <video autoplay muted loop playsinline>
                    <source src="video/Video-Adote.mp4" type="video/mp4">
                </video>
            </div>
        </div>
        <div class="slide-banner">
            <img src="img/Banner Cores neutras.png" alt="Banner de adoção">
        </div>
        
        <button class="controle-banner controle-anterior" onclick="mudarSlide(-1)">❮</button>
        <button class="controle-banner controle-proximo" onclick="mudarSlide(1)">❯</button>
        
        <div class="indicadores-banner">
            <span class="indicador ativo" onclick="irParaSlide(0)"></span>
            <span class="indicador" onclick="irParaSlide(1)"></span>
            <span class="indicador" onclick="irParaSlide(2)"></span>
        </div>
    </section>

    <section class="animais-destaque" id="secaoAnimais">
        <div class="container-animais">
            <div class="cabecalho-animais">
                <h2><i class="fas fa-paw"></i> Animais para Adoção</h2>
                <button class="btn-ver-mais" onclick="verTodosAnimais()">
                    Ver mais animais <i class="fas fa-arrow-right"></i>
                </button>
            </div>
            <div class="grade-animais" id="gradeAnimaisDestaque">
                <?php if (!empty($animais_destaque)): ?>
                    <?php foreach ($animais_destaque as $animal): ?>
                    <?php
                        $foto = !empty($animal['ds_img'])
                            ? 'uploads/' . htmlspecialchars($animal['ds_img'])
                            : 'https://placehold.co/400x400?text=Sem+Imagem';
                        $sexoClass = $animal['sexo'] === 'Macho' ? 'macho' : 'femea';
                        $sexoIcone = $animal['sexo'] === 'Macho' ? '♂' : '♀';
                        $especieIcone = $animal['especie'] === 'Cachorro' ? 'dog' : 'cat';
                        $idadeLabel = ($animal['idade'] == 1) ? '1 ano' : $animal['idade'] . ' anos';
                    ?>
                    <div class="cartao-animal" data-id="<?= $animal['id_animal'] ?>" onclick="abrirDetalhesAnimal(<?= $animal['id_animal'] ?>)">
                        <div class="imagem-animal">
                            <img src="<?= $foto ?>"
                                 alt="<?= htmlspecialchars($animal['nome']) ?>"
                                 onerror="this.src='https://placehold.co/400x400?text=Sem+Imagem'">
                            <span class="etiqueta-sexo <?= $sexoClass ?>"><?= $sexoIcone ?> <?= htmlspecialchars($animal['sexo']) ?></span>
                            <button class="btn-favorito" onclick="event.stopPropagation(); toggleFavorito(<?= $animal['id_animal'] ?>, this)">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                        <div class="info-animal">
                            <h3 class="nome-animal"><?= htmlspecialchars($animal['nome']) ?></h3>
                            <div class="raca-animal"><i class="fas fa-tag"></i> <?= htmlspecialchars($animal['raca'] ?? 'SRD') ?></div>
                            <div class="detalhes-animal">
                                <div class="detalhe-item"><i class="fas fa-birthday-cake"></i> <?= $idadeLabel ?></div>
                                <div class="detalhe-item"><i class="fas fa-<?= $especieIcone ?>"></i> <?= htmlspecialchars($animal['especie']) ?></div>
                            </div>
                            <button class="btn-adotar" onclick="event.stopPropagation(); abrirDetalhesAnimal(<?= $animal['id_animal'] ?>)">
                                <i class="fas fa-heart"></i> Quero adotar
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align:center; color:#666; padding: 40px 0;">
                        Nenhum animal disponível para adoção no momento.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <div id="paginaTodosAnimais" class="pagina-todos-animais">
        <button class="botao-voltar" onclick="voltarParaHome()">
            <i class="fas fa-arrow-left"></i> Voltar
        </button>
        
        <h1 style="margin-bottom: 20px; color: var(--primaria);">Todos os Animais para Adoção</h1>
        
        <div class="filtros-animais">
            <div class="grupo-filtro">
                <label>Espécie</label>
                <select id="filtroEspecie">
                    <option value="">Todas</option>
                    <option value="Cachorro">Cachorro</option>
                    <option value="Gato">Gato</option>
                </select>
            </div>
            <div class="grupo-filtro">
                <label>Sexo</label>
                <select id="filtroSexo">
                    <option value="">Todos</option>
                    <option value="Macho">Macho</option>
                    <option value="Fêmea">Fêmea</option>
                </select>
            </div>
            <div class="grupo-filtro">
                <label>Porte</label>
                <select id="filtroPorte">
                    <option value="">Todos</option>
                    <option value="Pequeno">Pequeno</option>
                    <option value="Médio">Médio</option>
                    <option value="Grande">Grande</option>
                </select>
            </div>
            <button class="btn-filtrar" onclick="filtrarAnimais()">Filtrar</button>
        </div>
        
        <div class="grade-animais" id="gradeTodosAnimais">
            <div class="loading"><div class="spinner"></div> Carregando...</div>
        </div>
    </div>

    <div id="paginaFavoritos" class="pagina-todos-animais">
        <button class="botao-voltar" onclick="voltarParaHome()">
            <i class="fas fa-arrow-left"></i> Voltar
        </button>
        
        <h1 style="margin-bottom: 20px; color: var(--primaria);">Meus Favoritos</h1>
        
        <div class="grade-animais" id="gradeFavoritos">
            <div class="loading"><div class="spinner"></div> Carregando...</div>
        </div>
    </div>

    <section class="secao-sobre">
        <div class="container-sobre">
            <div class="cabecalho-sobre">
                <h2 class="titulo-sobre">Sobre o Pet Vida</h2>
                <p>Conectando corações e lares há mais de 10 anos</p>
            </div>
            
            <div class="sobre-conteudo">
                <div class="texto-resumido">
                    <h3><i class="fas fa-paw"></i> Nossa Missão</h3>
                    <p>Nossa missão é resgatar animais abandonados, fornecer cuidados veterinários e encontrar lares amorosos e definitivos para eles, promovendo o bem-estar animal e combatendo o abandono.</p>
                    
                    <h3><i class="fas fa-star"></i> Nossos Valores</h3>
                    <p>Trabalhamos com transparência, bem-estar animal e educação como pilares fundamentais da nossa organização. Todos os processos de adoção são claros e auditáveis.</p>
                    
                    <h3><i class="fas fa-heart"></i> Adoção Responsável</h3>
                    <p>Adotar é um ato de amor, mas também de responsabilidade. Nosso processo de adoção foca em encontrar o pet perfeito para cada lar, realizamos entrevistas e visitas domiciliares.</p>
                    
                    <h3><i class="fas fa-hand-holding-heart"></i> Sua Contribuição</h3>
                    <p>Sua ajuda viabiliza o resgate de mais animais, cobre custos médicos e mantém o nosso abrigo funcionando. Cada adoção abre espaço para um novo resgate.</p>
                </div>
                
                <div class="texto-completo">
                    <h3><i class="fas fa-paw"></i> Nossa Missão de Resgate e Adoção</h3>
                    <p>Nossa missão é resgatar animais abandonados, fornecer cuidados veterinários e encontrar lares amorosos e definitivos para eles, promovendo o bem-estar animal e combatendo o abandono. Trabalhamos incansavelmente para garantir que cada animal tenha uma segunda chance e encontre uma família que o ame para sempre. Acreditamos que todo animal merece dignidade, respeito and amor.</p>
                    
                    <h3><i class="fas fa-star"></i> Nossos Valores</h3>
                    <ul>
                        <li><strong>Transparência:</strong> Todos os processos e custos de adoção são claros e auditáveis. Publicamos relatórios mensais de todas as despesas e arrecadações.</li>
                        <li><strong>Bem-estar Animal:</strong> A saúde física e emocional dos nossos animais é prioridade máxima. Todos os resgatados passam por avaliação veterinária completa, vacinação e vermifugação.</li>
                        <li><strong>Educação:</strong> Conscientização sobre a posse responsável e a importância da castração. Realizamos palestras em escolas e comunidades sobre bem-estar animal.</li>
                        <li><strong>Compromisso:</strong> Acompanhamos cada adoção por pelo menos 3 meses para garantir a adaptação do animal ao novo lar.</li>
                    </ul>
                    
                    <h3><i class="fas fa-heart"></i> Adoção Responsável</h3>
                    <p>Adotar é um ato de amor, mas também de responsabilidade. Estamos aqui para garantir que você e seu novo amigo tenham uma vida feliz juntos. Nosso processo de adoção foca em encontrar o pet perfeito para cada lar, realizamos entrevistas e visitas domiciliares para garantir o bem-estar do animal.</p>
                    <p><strong>Lembre-se: um animal de estimação é um membro da família para a vida toda. Adote com o coração, mas também com consciência.</strong> Eles dependem de você para alimentação, cuidados veterinários, amor e atenção. Um pet pode viver de 10 a 20 anos, então é um compromisso de longo prazo.</p>
                    
                    <h3><i class="fas fa-hand-holding-heart"></i> Sua Contribuição Faz a Diferença</h3>
                    <p>Sua ajuda viabiliza o resgate de mais animais, cobre custos médicos e mantém o nosso abrigo funcionando. Cada adoção abre espaço para um novo resgate. Com sua doação, podemos salvar ainda mais vidas e proporcionar dignidade a esses animais que tanto precisam.</p>
                    <div class="doacoes-info">
                        <p><strong><i class="fas fa-qrcode"></i> Doações via PIX:</strong> contato@petvida.org.br</p>
                        <p><strong><i class="fas fa-university"></i> Transferência Bancária:</strong> Banco do Brasil | Agência: 0001 | Conta: 12345-6</p>
                        <p><strong><i class="fas fa-dog"></i> Doação de itens:</strong> Rua dos Animais, 123 - Centro, Petrópolis/RJ</p>
                    </div>
                </div>
                
                <button class="btn-ler-mais-sobre" onclick="toggleLerMaisGeral()">
                    <i class="fas fa-chevron-down"></i> Ler mais
                </button>
            </div>
        </div>
    </section>

    <section class="secao-equipe">
        <div class="container">
            <h2 class="secao-titulo">Nossa Equipe</h2>
            <div class="equipe-grid">
                <div class="membro">
                    <div class="foto-bolinha"><img src="static/Mariana.jpeg" alt="Mariana"></div>
                    <div class="nome">Mariana R. Patricio</div>
                    <div class="cargo">Desenvolvedora Front-end</div>
                </div>
                <div class="membro">
                    <div class="foto-bolinha"><img src="static/Gabriel.jpeg" alt="Gabriel"></div>
                    <div class="nome">Gabriel F. Fortunato</div>
                    <div class="cargo">Desenvolvedor Back-end</div>
                </div>
                <div class="membro">
                    <div class="foto-bolinha"><img src="static/Lais.jpeg" alt="Lais"></div>
                    <div class="nome">Lais V. Meris</div>
                    <div class="cargo">Desenvolvedora Back-end</div>
                </div>
                <div class="membro">
                    <div class="foto-bolinha"><img src="static/Wellingtom.jpeg" alt="Wellingtom"></div>
                    <div class="nome">Wellingtom</div>
                    <div class="cargo">Desenvolvedor de Modelagem</div>
                </div>
            </div>
        </div>
    </section>

    <footer class="rodape-principal">
        <div class="conteudo-rodape">
            <div class="coluna-rodape">
                <h3>Institucional</h3>
                <ul class="links-rodape">
                    <li><a onclick="abrirSobreNos()">Sobre nós</a></li>
                    <li><a onclick="verTodosAnimais()">Animais para Adoção</a></li>
                </ul>
            </div>
            <div class="coluna-rodape">
                <h3>Atendimento</h3>
                <ul class="links-rodape">
                    <li><a onclick="abrirCentralAjuda()">Central de Ajuda</a></li>
                </ul>
            </div>
            <div class="coluna-rodape">
                <h3>Fale Conosco</h3>
                <ul class="links-rodape">
                    <li><i class="fas fa-phone"></i> (47) 99756-5199</li>
                    <li><i class="fas fa-envelope"></i> contato@petvida.org.br</li>
                </ul>
                <div class="links-sociais">
                    <a href="#" class="link-social"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="link-social"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
        <div class="rodape-inferior">
            <p>&copy; 2025 Pet Vida - Adote com amor</p>
        </div>
    </footer>

    <div class="botao-ajuda-flutuante" onclick="abrirCentralAjuda()">
        <i class="fas fa-question-circle"></i>
    </div>

    <div id="modal-Ajuda" class="modal">
        <div class="modal-box" style="max-width: 600px;">
            <button class="close" onclick="fecharModal('modal-Ajuda')">&times;</button>
            <h2 class="modal-title">Central de Ajuda</h2>
            
            <div class="conteudo-ajuda">
                <div class="secao-duvidas">
                    <h3><i class="fas fa-question-circle"></i> Dúvidas Frequentes</h3>
                    
                    <div class="duvida-item">
                        <button class="duvida-titulo" onclick="alternarDuvida(this)">
                            Como faço para adotar?
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="duvida-conteudo">
                            <p>1. Escolha o animal que deseja adotar<br>
                               2. Clique em "Quero adotar"<br>
                               3. Preencha o formulário de solicitação<br>
                               4. Nossa equipe entrará em contato para agendar uma entrevista<br>
                               5. Após aprovação, você poderá buscar seu novo amigo
                            </p>
                        </div>
                    </div>
                    
                    <div class="duvida-item">
                        <button class="duvida-titulo" onclick="alternarDuvida(this)">
                            Quais são as formas de doação?
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="duvida-conteudo">
                            <p>Aceitamos PIX, rações, dinheiro em espécie, brinquedos, roupas e outros itens para pets!</p>
                        </div>
                    </div>

                    <div class="duvida-item">
                        <button class="duvida-titulo" onclick="alternarDuvida(this)">
                            Política de Privacidade
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="duvida-conteudo">
                            <p>Quando você usa nossos serviços, está confiando a nós suas informações. Entendemos que isso é uma grande responsabilidade e trabalhamos duro para proteger essas informações e colocar você no controle.</p>
                        </div>
                    </div>
                </div>
                
                <div class="secao-contato" style="margin-top: 30px;">
                    <h3><i class="fas fa-envelope"></i> Entre em Contato</h3>
                    
                    <div class="opcoes-contato">
                        <div class="opcao-contato" onclick="abrirWhatsApp()">
                            <i class="fab fa-whatsapp"></i>
                            <div>
                                <strong>WhatsApp (SAC)</strong>
                                <p>(47) 99756-5199</p>
                                <small style="color: #666;">Atendimento rápido</small>
                            </div>
                        </div>
                        
                        <div class="opcao-contato" onclick="abrirEmail()">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>E-mail</strong>
                                <p>sac@petvida.org.br</p>
                                <small style="color: #666;">Respondemos em até 24h</small>
                            </div>
                        </div>
                        
                        <div class="opcao-contato" onclick="abrirHorarioAtendimento()">
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Horário de Atendimento</strong>
                                <p>Segunda a Sexta: 8h às 18h</p>
                                <p>Sábado: 9h às 13h</p>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px; padding: 15px; background: var(--clara); border-radius: var(--raio);">
                        <h4 style="color: var(--primaria); margin-bottom: 10px;"><i class="fas fa-info-circle"></i> Informações Importantes</h4>
                        <p style="font-size: 0.9rem; color: #666;">
                            <strong>Endereço:</strong> Rua dos Animais, 123 - Centro, Petrópolis/RJ<br>
                            <strong>CNPJ:</strong> 12.345.678/0001-90<br>
                            <strong>Atendimento presencial:</strong> Segunda a Sexta, 9h às 17h
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modalSobre" class="modal">
        <div class="modal-box">
            <button class="close" onclick="fecharModal('modalSobre')">&times;</button>
            <h2 class="modal-title">Sobre a Pet Vida</h2>
            <div class="conteudo-modal">
                <div class="missao-sobre">
                    <h3><i class="fas fa-heart"></i> Nossa História</h3>
                    <p>Fundada em 2015, a Pet Vida nasceu do sonho de transformar a realidade de animais abandonados. Desde então, já realizamos mais de 500 adoções e ajudamos centenas de animais a encontrarem um lar amoroso.</p>
                </div>
                <div class="valores-sobre">
                    <h3><i class="fas fa-star"></i> Nossos Valores</h3>
                    <ul>
                        <li><strong>Bem-estar Animal:</strong> A saúde física e emocional dos nossos animais é prioridade máxima.</li>
                        <li><strong>Amor pelos animais:</strong> Cada ação é feita com carinho e dedicação.</li>
                        <li><strong>Educação:</strong> Conscientização sobre a posse responsável e a importância da castração.</li>
                    </ul>
                </div>
                <div class="valores-sobre">
                    <h3><i class="fas fa-gift"></i> Adoção Responsável</h3>
                    <p>Adotar é um ato de amor, mas também de responsabilidade. Estamos aqui para garantir que você e seu novo amigo tenham uma vida feliz juntos. Nosso processo de adoção foca em encontrar o pet perfeito para cada lar.</p>
                    <p><strong>Lembre-se: um animal de estimação é um membro da família para a vida toda. Adote com o coração, mas também com consciência.</strong></p>
                </div>
                <div class="valores-sobre">
                    <h3><i class="fas fa-hand-holding-heart"></i> Sua Contribuição Faz a Diferença</h3>
                    <p>Sua ajuda viabiliza o resgate de mais animais, cobre custos médicos e mantém o nosso abrigo funcionando. Cada adoção abre espaço para um novo resgate.</p>
                </div>
            </div>
        </div>
    </div>

   <div id="modalLogin" class="modal">
        <div class="modal-box" style="max-height: 85vh; overflow-y: auto; padding-right: 10px;">
            <button class="close" onclick="fecharModal('modalLogin')">&times;</button>
            <h2 class="modal-title" id="modalTitle" style="color: var(--primaria); text-align: center;">Crie sua Conta</h2>
            
            <div id="formCadastro" style="display: block;">
                <form id="cadastroForm">
                    <div class="form-group">
                        <label>Nome Completo</label>
                        <input type="text" class="form-control" id="nome" required>
                        <span class="error-msg" id="nomeError"></span>
                    </div>
                    

                    <div class="form-group">
                        <label>Idade</label>
                        <input type="number" class="form-control" id="idade" min="0" max="120">
                    </div>

                    <div class="form-group">
                        <label>E-mail</label>
                        <input type="email" class="form-control" id="email" required>
                        <span class="error-msg" id="emailError"></span>
                    </div>

                    <div class="form-group">
                        <label>Senha</label>
                        <input type="password" class="form-control" id="senha" required>
                        <span class="error-msg" id="senhaError"></span>
                    </div>

                    <div class="form-group">
                        <label>Telefone</label>
                        <input type="text" class="form-control" id="telefone" required placeholder="(00) 00000-0000">
                    </div>

                    <div class="form-group">
                        <label>CPF</label>
                        <input type="text" class="form-control" id="cpf" required placeholder="000.000.000-00">
                    </div>

                    <div class="form-group">
                        <label>Data de Nascimento</label>
                        <input type="date" class="form-control" id="data_nascimento" required>
                    </div>

                    <div class="form-group">
                        <label>Endereço</label>
                        <input type="text" class="form-control" id="endereco" required placeholder="Rua, número e bairro">
                    </div>

                    <div class="form-group">
                        <label>Cidade</label>
                        <input type="text" class="form-control" id="cidade" required>
                    </div>

                    <div class="form-group">
                        <label>Estado</label>
                        <select class="form-control" id="estado" required>
                            <option value="" disabled selected>Selecione seu estado</option>
                            <option value="AC">Acre</option>
                            <option value="AL">Alagoas</option>
                            <option value="AP">Amapá</option>
                            <option value="AM">Amazonas</option>
                            <option value="BA">Bahia</option>
                            <option value="CE">Ceará</option>
                            <option value="DF">Distrito Federal</option>
                            <option value="ES">Espírito Santo</option>
                            <option value="GO">Goiás</option>
                            <option value="MA">Maranhão</option>
                            <option value="MT">Mato Grosso</option>
                            <option value="MS">Mato Grosso do Sul</option>
                            <option value="MG">Minas Gerais</option>
                            <option value="PA">Pará</option>
                            <option value="PB">Paraíba</option>
                            <option value="PR">Paraná</option>
                            <option value="PE">Pernambuco</option>
                            <option value="PI">Piauí</option>
                            <option value="RJ">Rio de Janeiro</option>
                            <option value="RN">Rio Grande do Norte</option>
                            <option value="RS">Rio Grande do Sul</option>
                            <option value="RO">Rondônia</option>
                            <option value="RR">Roraima</option>
                            <option value="SC">Santa Catarina</option>
                            <option value="SP">São Paulo</option>
                            <option value="SE">Sergipe</option>
                            <option value="TO">Tocantins</option>
                        </select>
                    </div>

                    <p class="success" id="cadastroSuccess" style="display: none;"></p>
                    <button type="submit" class="btn">Cadastrar</button>
                </form>
                <div class="footer-modal">
                    Já tem conta? <a href="#" class="link" onclick="alternarFormulario('login')">Faça Login</a>
                </div>
            </div>

            <div id="formLogin" style="display: none;">
                <form id="loginForm">
                    <div class="form-group">
                        <label>E-mail</label>
                        <input type="email" class="form-control" id="loginEmail" required>
                        <span class="error-msg" id="loginEmailError"></span>
                    </div>
                    <div class="form-group">
                        <label>Senha</label>
                        <input type="password" class="form-control" id="loginSenha" required>
                        <span class="error-msg" id="loginSenhaError"></span>
                    </div>
                    <p class="success" id="loginSuccess" style="display: none;"></p>
                    <button type="submit" class="btn">Entrar</button>
                </form>
                <div class="footer-modal">
                    Não tem conta? <a href="#" class="link" onclick="alternarFormulario('cadastro')">Crie sua Conta</a>
                </div>
            </div>
        </div>
    </div>

    <div id="modalAnimal" class="modal">
        <div class="modal-animal-box">
            <button class="close" onclick="fecharModalAnimal()">&times;</button>
            <div class="modal-animal-conteudo">
                <div class="modal-animal-imagem">
                    <img id="modalAnimalImg" src="" alt="Animal">
                </div>
                <div class="modal-animal-info">
                    <h2 id="modalAnimalNome"></h2>
                    <div class="raca-animal" id="modalAnimalRaca"></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 20px 0; padding: 15px 0; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                        <div><i class="fas fa-venus-mars"></i> <span id="modalAnimalSexo"></span></div>
                        <div><i class="fas fa-birthday-cake"></i> <span id="modalAnimalIdade"></span> anos</div>
                        <div><i class="fas fa-paw"></i> <span id="modalAnimalEspecie"></span></div>
                        <div><i class="fas fa-weight-hanging"></i> <span id="modalAnimalPeso"></span> kg</div>
                        <div><i class="fas fa-arrows-alt"></i> Porte: <span id="modalAnimalPorte"></span></div>
                        <div><i class="fas fa-syringe"></i> Vacinado: <span id="modalAnimalVacinado"></span></div>
                    </div>
                    <div class="modal-descricao" id="modalAnimalDescricao" style="margin: 20px 0; line-height: 1.6;"></div>
                    <button class="btn-adotar" onclick="solicitarAdocao()">
                        <i class="fas fa-heart"></i> Quero adotar este animal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="modalDoacao" class="modal">
        <div class="modal-box" style="max-width: 600px;">
            <button class="close" onclick="fecharModalDoacao()">&times;</button>
            <h2 class="modal-title"><i class="fas fa-hand-holding-heart"></i> Faça uma Doação</h2>
            
            <div class="grid-doacoes">
                <div class="opcao-doacao" onclick="selecionarTipoDoacao('Dinheiro', this)">
                    <i class="fas fa-money-bill-wave"></i>
                    <h4>Dinheiro</h4>
                    <p>Qualquer valor ajuda</p>
                </div>
                <div class="opcao-doacao" onclick="selecionarTipoDoacao('Ração', this)">
                    <i class="fas fa-dog"></i>
                    <h4>Ração</h4>
                    <p>Para cães e gatos</p>
                </div>
                <div class="opcao-doacao" onclick="selecionarTipoDoacao('Roupa', this)">
                    <i class="fas fa-tshirt"></i>
                    <h4>Roupas</h4>
                    <p>Para dias frios</p>
                </div>
                <div class="opcao-doacao" onclick="selecionarTipoDoacao('Brinquedo', this)">
                    <i class="fas fa-baseball-ball"></i>
                    <h4>Brinquedos</h4>
                    <p>Para entreter os pets</p>
                </div>
                <div class="opcao-doacao" onclick="selecionarTipoDoacao('Medicamento', this)">
                    <i class="fas fa-capsules"></i>
                    <h4>Medicamentos</h4>
                    <p>Vermífugos e vacinas</p>
                </div>
                <div class="opcao-doacao" onclick="selecionarTipoDoacao('Outro', this)">
                    <i class="fas fa-gift"></i>
                    <h4>Outros</h4>
                    <p>Camas, coleiras, etc</p>
                </div>
            </div>
            
            <div id="valorDoacaoDiv" style="display: none;">
                <div class="form-group">
                    <label>Valor (R$)</label>
                    <input type="number" class="form-control" id="valorDoacao" step="0.01" min="1" placeholder="Ex: 50.00">
                </div>
            </div>
            
            <div id="outroDoacao" style="display: none;">
                <div class="form-group">
                    <label>Descreva o que deseja doar</label>
                    <textarea class="form-control" id="descricaoOutro" rows="3" placeholder="Ex: 3 cobertores, 2 coleiras, 1 caixa de transporte..."></textarea>
                </div>
            </div>
            
            <button class="btn" onclick="enviarDoacao()" style="background: var(--primaria);">Confirmar Doação</button>
            <p style="text-align: center; margin-top: 15px; font-size: 0.8rem; color: #666;">
                <i class="fas fa-heart" style="color: #ff4444;"></i> Sua doação faz a diferença!
            </p>
        </div>
    </div>

    <script src="script/script.js"></script>
    <script>
        // Corrigir o evento do botão de doação
        document.querySelector('.botao-doacao').addEventListener('click', function(e) {
            e.preventDefault();
            abrirModalDoacao();
        });
        
        // Mostrar/esconder campo de valor quando selecionar Dinheiro
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
    </script>
</body>
</html>