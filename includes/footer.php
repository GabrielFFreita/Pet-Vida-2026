<?php
require_once __DIR__ . '/helpers.php';

$extraScripts = $extraScripts ?? [];
?>
    <footer class="rodape-principal">
        <div class="conteudo-rodape">
            <div class="coluna-rodape">
                <h3>Institucional</h3>
                <ul class="links-rodape">
                    <li><a onclick="abrirSobreNos()">Sobre nós</a></li>
                    <li><a href="<?php echo htmlspecialchars(rootPath('adocao.php'), ENT_QUOTES, 'UTF-8'); ?>">Como adotar</a></li>
                    <li><a href="#">Política de privacidade</a></li>
                </ul>
            </div>
            <div class="coluna-rodape">
                <h3>Ajuda</h3>
                <ul class="links-rodape">
                    <li><a onclick="abrirCentralAjuda()">Dúvidas frequentes</a></li>
                    <li><a href="#">Fale conosco</a></li>
                </ul>
            </div>
            <div class="coluna-rodape">
                <h3>Contato</h3>
                <ul class="links-rodape">
                    <li><a href="mailto:contato@petvida.org.br" class="rodape-contato-link">
                        <i class="fas fa-envelope"></i> contato@petvida.org.br
                    </a></li>
                    <li><a href="tel:+554799756519" class="rodape-contato-link">
                        <i class="fab fa-whatsapp"></i> (47) 99756-5199
                    </a></li>
                    <li><a href="#" class="rodape-contato-link">
                        <i class="fab fa-instagram"></i> @petvida.oficial
                    </a></li>
                </ul>
                <div class="links-sociais">
                    <a href="mailto:contato@petvida.org.br" class="link-social" aria-label="E-mail"><i class="fas fa-envelope"></i></a>
                    <a href="#" class="link-social" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="tel:+554799756519" class="link-social" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
        <div class="rodape-inferior">
            <p>&copy; 2025 Adote com Amor · Todos os direitos reservados</p>
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
                               5. Após a aprovação, você poderá buscar seu novo amigo
                            </p>
                        </div>
                    </div>
                    <div class="duvida-item">
                        <button class="duvida-titulo" onclick="alternarDuvida(this)">
                            Quais são as formas de doação?
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="duvida-conteudo">
                            <p>Aceitamos PIX, rações, dinheiro em espécie, brinquedos, roupas e outros itens para pets.</p>
                        </div>
                    </div>
                    <div class="duvida-item">
                        <button class="duvida-titulo" onclick="alternarDuvida(this)">
                            Política de Privacidade
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="duvida-conteudo">
                            <p>Quando você usa nossos serviços, está confiando a nós suas informações. Trabalhamos para proteger esses dados e manter você no controle.</p>
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
                    <p>Adotar é um ato de amor, mas também de responsabilidade. Estamos aqui para garantir que você e seu novo amigo tenham uma vida feliz juntos.</p>
                    <p><strong>Lembre-se: um animal de estimação é um membro da família para a vida toda. Adote com o coração, mas também com consciência.</strong></p>
                </div>
                <div class="valores-sobre">
                    <h3><i class="fas fa-hand-holding-heart"></i> Sua Contribuição Faz a Diferença</h3>
                    <p>Sua ajuda viabiliza o resgate de mais animais, cobre custos médicos e mantém o nosso abrigo funcionando. Cada adoção abre espaço para um novo resgate.</p>
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
                <div style="text-align: center; margin-bottom: 15px;">
                    <img src="<?php echo htmlspecialchars(assetPath('img/qrcode/qrcode_pix.png'), ENT_QUOTES, 'UTF-8'); ?>" alt="QR Code Pix" style="width: 180px; height: 180px; border: 1px solid #e2e8f0; border-radius: var(--raio); padding: 6px; background: #fff;">
                    <p style="margin-top: 10px; font-size: 0.9rem; color: #666;">Escaneie o QR Code com o app do seu banco</p>
                </div>
                <div class="form-group">
                    <label>Ou copie a chave Pix</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" class="form-control" id="chavePix" value="contato@petvida.org.br" readonly>
                        <button type="button" class="btn" style="background: var(--primaria); padding: 10px 16px; white-space: nowrap;" onclick="copiarChavePix()">Copiar</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Valor doado (opcional, apenas para nosso controle)</label>
                    <input type="number" class="form-control" id="valorDoacao" step="0.01" min="1" placeholder="Ex: 50.00">
                </div>
            </div>
            <div id="outroDoacao" style="display: none;">
                <div class="form-group">
                    <label>Descreva o que deseja doar</label>
                    <textarea class="form-control" id="descricaoOutro" rows="3" placeholder="Ex: 3 cobertores, 2 coleiras, 1 caixa de transporte..."></textarea>
                </div>
            </div>
            <div id="infoDoacaoDiv" style="display: none; background: #f4f8f6; border-radius: var(--raio); padding: 15px 18px; margin-bottom: 15px;">
                <p style="font-weight: 600; margin-bottom: 8px; color: var(--primaria);">
                    <i class="fas fa-lightbulb"></i> Itens sugeridos:
                </p>
                <ul id="listaSugestoesDoacao" style="padding-left: 20px; line-height: 1.8; margin: 0;"></ul>
                <div class="form-group" style="margin-top: 12px; margin-bottom: 0;">
                    <label>O que você vai enviar?</label>
                    <textarea class="form-control" id="detalheDoacao" rows="2" placeholder="Ex: 2 pacotes de ração, 1 caixa de vermífugo..."></textarea>
                </div>
            </div>
            <button class="btn" onclick="enviarDoacao()" style="background: var(--primaria);">Confirmar Doação</button>
            <p style="text-align: center; margin-top: 15px; font-size: 0.8rem; color: #666;">
                <i class="fas fa-heart" style="color: #ff4444;"></i> Sua doação faz a diferença!
            </p>
        </div>
    </div>

    <script src="<?php echo htmlspecialchars(assetPath('js/site.js?v=13'), ENT_QUOTES, 'UTF-8'); ?>"></script>
<?php foreach ($extraScripts as $scriptPath): ?>
    <script src="<?php echo htmlspecialchars($scriptPath, ENT_QUOTES, 'UTF-8'); ?>"></script>
<?php endforeach; ?>
</body>
</html>
