<?php
require_once "conexao.php";
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
        <img src="img/Petquiz.png" alt="Banner Pet quiz">
        <div class="conteudo-banner">
            <h1>Adote um amigo para a vida</h1>
            <p>Milhares de animais esperando por um lar cheio de amor.</p>
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
        <img src="img/Banner Cores neutras.png" alt="Banner de adocao">
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
            <h2><i class="fas fa-paw"></i> Animais para Adocao</h2>
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
                    $idadeTexto = $idade !== "" ? $idade . " anos" : "Nao informado";
                    $pesoTexto = $peso !== "" ? $peso . " kg" : "Nao informado";
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
                <p style="grid-column: 1 / -1; text-align: center; color: #718096;">Nenhum animal disponivel para adocao no momento.</p>
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
            <label for="filtroEspecie">Especie</label>
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
                <option value="Fêmea">Femea</option>
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
            <p>Conectando coracoes e lares ha mais de 10 anos.</p>
        </div>

        <div class="sobre-conteudo">
            <div class="texto-resumido">
                <h3><i class="fas fa-paw"></i> Nossa Missao</h3>
                <p>Nossa missao e resgatar animais abandonados, oferecer cuidados veterinarios e encontrar lares amorosos e definitivos para eles.</p>

                <h3><i class="fas fa-star"></i> Nossos Valores</h3>
                <p>Trabalhamos com transparencia, bem-estar animal e educacao como pilares da organizacao.</p>

                <h3><i class="fas fa-heart"></i> Adocao Responsavel</h3>
                <p>Adotar e um ato de amor e compromisso. Nosso processo busca o pet ideal para cada lar.</p>

                <h3><i class="fas fa-hand-holding-heart"></i> Sua Contribuicao</h3>
                <p>Cada ajuda viabiliza novos resgates, cobre custos medicos e mantem o abrigo funcionando.</p>
            </div>

            <div class="texto-completo">
                <h3><i class="fas fa-paw"></i> Nossa Missao de Resgate e Adocao</h3>
                <p>Nossa missao e resgatar animais abandonados, fornecer cuidados veterinarios e encontrar lares amorosos e definitivos para eles, promovendo bem-estar e combatendo o abandono.</p>
                <p>Acreditamos que cada animal merece dignidade, seguranca e uma segunda chance com uma familia responsavel.</p>

                <h3><i class="fas fa-star"></i> Nossos Valores</h3>
                <ul>
                    <li><strong>Transparencia:</strong> processos e custos claros e auditaveis.</li>
                    <li><strong>Bem-estar Animal:</strong> saude fisica e emocional em primeiro lugar.</li>
                    <li><strong>Educacao:</strong> conscientizacao sobre posse responsavel e castracao.</li>
                    <li><strong>Compromisso:</strong> acompanhamento das adocoes para garantir boa adaptacao.</li>
                </ul>

                <h3><i class="fas fa-heart"></i> Adocao Responsavel</h3>
                <p>Adotar e um ato de amor, mas tambem de responsabilidade. Nosso processo busca o pet certo para cada familia e reforca o compromisso de longo prazo com o novo lar.</p>

                <h3><i class="fas fa-hand-holding-heart"></i> Sua Contribuicao Faz a Diferenca</h3>
                <p>Sua ajuda viabiliza mais resgates, cobre custos medicos e mantem o abrigo funcionando. Cada adocao abre espaco para um novo animal ser acolhido.</p>
                <div class="doacoes-info">
                    <p><strong><i class="fas fa-qrcode"></i> Doacoes via PIX:</strong> contato@petvida.org.br</p>
                    <p><strong><i class="fas fa-university"></i> Transferencia Bancaria:</strong> Banco do Brasil | Agencia: 0001 | Conta: 12345-6</p>
                    <p><strong><i class="fas fa-dog"></i> Doacao de itens:</strong> Rua dos Animais, 123 - Centro, Petropolis/RJ</p>
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
                <div class="foto-bolinha"><img src="img/mari.jpeg" alt="Mariana"></div>
                <div class="nome">Mariana R. Patricio</div>
                <div class="cargo">Desenvolvedora<br>Front-end</div>
            </div>
            <div class="membro">
                <div class="foto-bolinha"><img src="img/gabriel.jpeg" alt="Gabriel"></div>
                <div class="nome">Gabriel F. Freitas</div>
                <div class="cargo">Desenvolvedor<br>Back-end</div>
            </div>
            <div class="membro">
                <div class="foto-bolinha"><img src="img/lais.jpeg" alt="Lais"></div>
                <div class="nome">Lais V. Meris</div>
                <div class="cargo">Desenvolvedora<br>Back-end</div>
            </div>
            <div class="membro">
                <div class="foto-bolinha"><img src="img/welli.jpeg" alt="Wellingtom"></div>
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
