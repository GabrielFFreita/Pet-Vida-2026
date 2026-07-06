<?php
require_once __DIR__ . "/config/conexao.php";
session_start();

$pageTitle = "Pet Vida - Adote um Amigo";

try {
    $sql = "SELECT
                a.id_animal,
                a.nome,
                a.idade,
                a.raca,
                a.especie,
                a.sexo,
                a.porte,
                a.peso,
                a.vacinado,
                a.descricao,
                a.status_adocao,
                fotos.ds_img
            FROM animais_adocao a
            LEFT JOIN (
                SELECT
                    id_animal,
                    MIN(ds_img) AS ds_img
                FROM foto_animal
                GROUP BY id_animal
            ) fotos ON a.id_animal = fotos.id_animal
            ORDER BY RAND()
            LIMIT 5";
    $stmt = $pdo->query($sql);
    $animais_destaque = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $animais_destaque = [];
}

require __DIR__ . "/includes/header.php";
?>

<section class="banner-principal">
    <div class="slide-banner ativo">
        <img src="assets/img/banners/Petquiz.png" alt="Banner Pet quiz">
        <div class="conteudo-banner">
            <h1>Adote um amigo para a vida</h1>
            <p>Milhares de animais esperando por um lar cheio de amor.</p>
        </div>
    </div>
    <div class="slide-banner">
        <div class="video-wrapper">
            <video autoplay muted loop playsinline>
                <source src="assets/video/Video-Adote.mp4" type="video/mp4">
            </video>
        </div>
    </div>
    <div class="slide-banner">
        <img src="assets/img/banners/Banner Cores neutras.png" alt="Banner de ado&ccedil;&atilde;o">
    </div>

    <button class="controle-banner controle-anterior" onclick="mudarSlide(-1)">&#10094;</button>
    <button class="controle-banner controle-proximo" onclick="mudarSlide(1)">&#10095;</button>

    <div class="indicadores-banner">
        <span class="indicador ativo" onclick="irParaSlide(0)"></span>
        <span class="indicador" onclick="irParaSlide(1)"></span>
        <span class="indicador" onclick="irParaSlide(2)"></span>
    </div>
</section>

<section class="animais-destaque" id="secaoAnimais">
    <div class="container-animais">
        <div class="cabecalho-animais">
            <h2><i class="fas fa-paw"></i> Animais para Ado&ccedil;&atilde;o</h2>
            <a href="adocao.php" class="btn-ver-mais" onclick="return irParaAdocao(event)">
                Ver mais animais <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="grade-animais" id="gradeAnimaisDestaque">
            <?php if (!empty($animais_destaque)): ?>
                <?php foreach ($animais_destaque as $animal):
                    $sexo = trim((string) ($animal["sexo"] ?? ""));
                    $sexoNormalizado = function_exists("mb_strtolower") ? mb_strtolower($sexo, "UTF-8") : strtolower($sexo);
                    $sexoClasse = $sexoNormalizado === "macho" ? "macho" : "femea";
                    $imagem = !empty($animal["ds_img"]) ? "uploads/" . ltrim((string) $animal["ds_img"], "/\\") : "";
                    $idade = trim((string) ($animal["idade"] ?? ""));
                    $peso = trim((string) ($animal["peso"] ?? ""));
                    $idadeTexto = $idade !== "" ? $idade . " anos" : "N&atilde;o informado";
                    $pesoTexto = $peso !== "" ? $peso . " kg" : "N&atilde;o informado";
                ?>
                    <div
                        class="cartao-animal"
                        data-id="<?php echo (int) ($animal["id_animal"] ?? 0); ?>"
                        data-img="<?php echo htmlspecialchars($imagem, ENT_QUOTES, "UTF-8"); ?>"
                        data-nome="<?php echo htmlspecialchars((string) ($animal["nome"] ?? ""), ENT_QUOTES, "UTF-8"); ?>"
                        data-raca="<?php echo htmlspecialchars((string) ($animal["raca"] ?? ""), ENT_QUOTES, "UTF-8"); ?>"
                        data-sexo="<?php echo htmlspecialchars($sexo, ENT_QUOTES, "UTF-8"); ?>"
                        data-idade="<?php echo htmlspecialchars((string) ($animal["idade"] ?? ""), ENT_QUOTES, "UTF-8"); ?>"
                        data-especie="<?php echo htmlspecialchars((string) ($animal["especie"] ?? ""), ENT_QUOTES, "UTF-8"); ?>"
                        data-peso="<?php echo htmlspecialchars((string) ($animal["peso"] ?? ""), ENT_QUOTES, "UTF-8"); ?>"
                        data-porte="<?php echo htmlspecialchars((string) ($animal["porte"] ?? ""), ENT_QUOTES, "UTF-8"); ?>"
                        data-vacinado="<?php echo htmlspecialchars((string) ($animal["vacinado"] ?? ""), ENT_QUOTES, "UTF-8"); ?>"
                        data-descricao="<?php echo htmlspecialchars((string) ($animal["descricao"] ?? ""), ENT_QUOTES, "UTF-8"); ?>"
                        onclick="abrirPreviewAnimal(this)">
                        <div class="imagem-animal">
                            <?php if ($imagem !== ""): ?>
                                <img src="<?php echo htmlspecialchars($imagem, ENT_QUOTES, "UTF-8"); ?>" alt="<?php echo htmlspecialchars((string) ($animal["nome"] ?? "Animal"), ENT_QUOTES, "UTF-8"); ?>" onerror="this.style.display='none'">
                            <?php endif; ?>
                            <span class="etiqueta-sexo <?php echo $sexoClasse; ?>"><?php echo htmlspecialchars($sexo, ENT_QUOTES, "UTF-8"); ?></span>
                            <button class="btn-favorito" onclick="event.stopPropagation(); toggleFavorito(<?php echo (int) ($animal["id_animal"] ?? 0); ?>, this)" aria-label="Favoritar">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                        <div class="info-animal">
                            <h3 class="nome-animal"><?php echo htmlspecialchars((string) ($animal["nome"] ?? ""), ENT_QUOTES, "UTF-8"); ?></h3>
                            <p class="raca-animal"><i class="fas fa-dna"></i> <?php echo htmlspecialchars((string) ($animal["raca"] ?? ""), ENT_QUOTES, "UTF-8"); ?></p>
                            <div class="detalhes-animal">
                                <div class="detalhe-item"><i class="fas fa-birthday-cake"></i> <?php echo htmlspecialchars($idadeTexto, ENT_QUOTES, "UTF-8"); ?></div>
                                <div class="detalhe-item"><i class="fas fa-weight-hanging"></i> <?php echo htmlspecialchars($pesoTexto, ENT_QUOTES, "UTF-8"); ?></div>
                            </div>
                            <button class="btn-adotar" onclick="event.stopPropagation(); abrirPreviewAnimal(this.closest('.cartao-animal'))">
                                <i class="fas fa-paw"></i> Ver Detalhes
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1 / -1; text-align: center; color: #718096;">Nenhum animal dispon&iacute;vel para ado&ccedil;&atilde;o no momento.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="pagina-todos-animais" id="paginaTodosAnimais">
    <button class="botao-voltar" type="button" onclick="voltarParaHome()">
        <i class="fas fa-arrow-left"></i> Voltar para a home
    </button>

    <div class="filtros-animais">
        <div class="grupo-filtro">
            <label for="filtroEspecie">Esp&eacute;cie</label>
            <select id="filtroEspecie">
                <option value="">Todas</option>
                <option value="Cachorro">Cachorro</option>
                <option value="Gato">Gato</option>
            </select>
        </div>
        <div class="grupo-filtro">
            <label for="filtroSexo">Sexo</label>
            <select id="filtroSexo">
                <option value="">Todos</option>
                <option value="Macho">Macho</option>
                <option value="F&ecirc;mea">F&ecirc;mea</option>
            </select>
        </div>
        <div class="grupo-filtro">
            <label for="filtroPorte">Porte</label>
            <select id="filtroPorte">
                <option value="">Todos</option>
                <option value="Pequeno">Pequeno</option>
                <option value="Medio">Medio</option>
                <option value="Grande">Grande</option>
            </select>
        </div>
        <button class="btn-filtrar" type="button" onclick="filtrarAnimais()">Filtrar</button>
    </div>

    <div class="grade-animais" id="gradeTodosAnimais"></div>
</section>

<section class="pagina-todos-animais" id="paginaFavoritos">
    <button class="botao-voltar" type="button" onclick="voltarParaHome()">
        <i class="fas fa-arrow-left"></i> Voltar para a home
    </button>

    <div class="cabecalho-animais">
        <h2><i class="fas fa-heart"></i> Seus Favoritos</h2>
    </div>

    <div class="grade-animais" id="gradeFavoritos"></div>
</section>

<section class="secao-sobre">
    <div class="container-sobre">
        <div class="cabecalho-sobre">
            <h2 class="titulo-sobre">Sobre o Pet Vida</h2>
            <p>Conectando cora&ccedil;&otilde;es e lares h&aacute; mais de 10 anos.</p>
        </div>

        <div class="sobre-conteudo">
            <div class="texto-resumido">
                <h3><i class="fas fa-paw"></i> Nossa Miss&atilde;o</h3>
                <p>Nossa miss&atilde;o &eacute; resgatar animais abandonados, oferecer cuidados veterin&aacute;rios e encontrar lares amorosos e definitivos para eles.</p>

                <h3><i class="fas fa-star"></i> Nossos Valores</h3>
                <p>Trabalhamos com transpar&ecirc;ncia, bem-estar animal e educa&ccedil;&atilde;o como pilares da organiza&ccedil;&atilde;o.</p>

                <h3><i class="fas fa-heart"></i> Ado&ccedil;&atilde;o Respons&aacute;vel</h3>
                <p>Adotar &eacute; um ato de amor e compromisso. Nosso processo busca o pet ideal para cada lar.</p>

                <h3><i class="fas fa-hand-holding-heart"></i> Sua Contribui&ccedil;&atilde;o</h3>
                <p>Cada ajuda viabiliza novos resgates, cobre custos m&eacute;dicos e mant&eacute;m o abrigo funcionando.</p>
            </div>

            <div class="texto-completo">
                <h3><i class="fas fa-paw"></i> Nossa Miss&atilde;o de Resgate e Ado&ccedil;&atilde;o</h3>
                <p>Nossa miss&atilde;o &eacute; resgatar animais abandonados, fornecer cuidados veterin&aacute;rios e encontrar lares amorosos e definitivos para eles, promovendo bem-estar e combatendo o abandono.</p>
                <p>Acreditamos que cada animal merece dignidade, seguran&ccedil;a e uma segunda chance com uma fam&iacute;lia respons&aacute;vel.</p>

                <h3><i class="fas fa-star"></i> Nossos Valores</h3>
                <ul>
                    <li><strong>Transpar&ecirc;ncia:</strong> processos e custos claros e audit&aacute;veis.</li>
                    <li><strong>Bem-estar Animal:</strong> sa&uacute;de f&iacute;sica e emocional em primeiro lugar.</li>
                    <li><strong>Educa&ccedil;&atilde;o:</strong> conscientiza&ccedil;&atilde;o sobre posse respons&aacute;vel e castra&ccedil;&atilde;o.</li>
                    <li><strong>Compromisso:</strong> acompanhamento das ado&ccedil;&otilde;es para garantir boa adapta&ccedil;&atilde;o.</li>
                </ul>

                <h3><i class="fas fa-heart"></i> Ado&ccedil;&atilde;o Respons&aacute;vel</h3>
                <p>Adotar &eacute; um ato de amor, mas tamb&eacute;m de responsabilidade. Nosso processo busca o pet certo para cada fam&iacute;lia e refor&ccedil;a o compromisso de longo prazo com o novo lar.</p>

                <h3><i class="fas fa-hand-holding-heart"></i> Sua Contribui&ccedil;&atilde;o Faz a Diferen&ccedil;a</h3>
                <p>Sua ajuda viabiliza mais resgates, cobre custos m&eacute;dicos e mant&eacute;m o abrigo funcionando. Cada ado&ccedil;&atilde;o abre espa&ccedil;o para um novo animal ser acolhido.</p>
                <div class="doacoes-info">
                    <p><strong><i class="fas fa-qrcode"></i> Doa&ccedil;&otilde;es via PIX:</strong> contato@petvida.org.br</p>
                    <p><strong><i class="fas fa-university"></i> Transfer&ecirc;ncia Banc&aacute;ria:</strong> Banco do Brasil | Ag&ecirc;ncia: 0001 | Conta: 12345-6</p>
                    <p><strong><i class="fas fa-dog"></i> Doa&ccedil;&atilde;o de itens:</strong> Rua dos Animais, 123 - Centro, Petr&oacute;polis/RJ</p>
                </div>
            </div>

            <button class="btn-ler-mais-sobre" type="button" onclick="toggleLerMaisGeral()">
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
                <div class="foto-bolinha"><img src="assets/img/equipe/mari.jpeg" alt="Mariana"></div>
                <div class="nome">Mariana R. Patricio</div>
                <div class="cargo">Desenvolvedora<br>Front-end</div>
            </div>
            <div class="membro">
                <div class="foto-bolinha"><img src="assets/img/equipe/gabriel.jpeg" alt="Gabriel"></div>
                <div class="nome">Gabriel F. Freitas</div>
                <div class="cargo">Desenvolvedor<br>Back-end</div>
            </div>
            <div class="membro">
                <div class="foto-bolinha"><img src="assets/img/equipe/lais.jpeg" alt="Lais"></div>
                <div class="nome">Lais V. Meris</div>
                <div class="cargo">Desenvolvedora<br>Back-end</div>
            </div>
            <div class="membro">
                <div class="foto-bolinha"><img src="assets/img/equipe/welli.jpeg" alt="Wellingtom"></div>
                <div class="nome">Wellingtom</div>
                <div class="cargo">Desenvolvedor<br>de Modelagem</div>
            </div>
        </div>
    </div>
</section>

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

<?php require __DIR__ . "/includes/footer.php"; ?>

