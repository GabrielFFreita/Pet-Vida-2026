(function () {
    var DURACAO_PADRAO = 5000;
    var container = null;

    function obterContainer() {
        if (container && document.body.contains(container)) return container;
        container = document.createElement('div');
        container.id = 'pv-toast-container';
        container.className = 'pv-toast-container';
        container.setAttribute('aria-live', 'polite');
        document.body.appendChild(container);
        return container;
    }

    function mostrarToast(mensagem, tipo, duracaoMs) {
        tipo = tipo === 'erro' ? 'erro' : 'sucesso';
        duracaoMs = typeof duracaoMs === 'number' ? duracaoMs : DURACAO_PADRAO;

        var cont = obterContainer();
        var toast = document.createElement('div');
        toast.className = 'pv-toast pv-toast--' + tipo;
        toast.setAttribute('role', tipo === 'erro' ? 'alert' : 'status');

        var icone = document.createElement('span');
        icone.className = 'pv-toast-icone';
        icone.setAttribute('aria-hidden', 'true');

        var texto = document.createElement('span');
        texto.className = 'pv-toast-texto';
        texto.textContent = mensagem;

        var fechar = document.createElement('button');
        fechar.type = 'button';
        fechar.className = 'pv-toast-fechar';
        fechar.setAttribute('aria-label', 'Fechar notificação');
        fechar.innerHTML = '&times;';

        toast.appendChild(icone);
        toast.appendChild(texto);
        toast.appendChild(fechar);
        cont.appendChild(toast);

        requestAnimationFrame(function () { toast.classList.add('pv-toast--visivel'); });

        var timer = null;
        function remover() {
            toast.classList.remove('pv-toast--visivel');
            toast.classList.add('pv-toast--saindo');
            window.setTimeout(function () { toast.remove(); }, 300);
        }
        function agendar() {
            if (duracaoMs > 0) timer = window.setTimeout(remover, duracaoMs);
        }
        fechar.addEventListener('click', function () {
            if (timer) clearTimeout(timer);
            remover();
        });
        toast.addEventListener('mouseenter', function () { if (timer) clearTimeout(timer); });
        toast.addEventListener('mouseleave', agendar);
        agendar();

        return toast;
    }

    window.mostrarToast = mostrarToast;
})();
