// ============================================================
// PET QUIZ — quiz.js
// O envio em si é feito pelo próprio <form> (method="post",
// action="php/buscar_animais.php") — uma navegação real de página,
// o que garante que os caminhos relativos usados dentro do PHP
// (ex.: "../adocao.html", "../css/style_buscar.css") funcionem
// corretamente.
//
// Este script cuida apenas da validação no lado do cliente: impede
// o envio caso alguma pergunta não tenha sido respondida e mostra
// uma mensagem amigável.
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('form-quiz');
  const erro = document.getElementById('quiz-erro');

  const campos = [
    'rotina',
    'frequencia',
    'investimento',
    'motivacao',
    'atividade',
    'moradia',
    'tempoLivre',
    'sexo'
  ];

  form.addEventListener('submit', (e) => {
    const todasRespondidas = campos.every(
      (campo) => form.querySelector(`input[name="${campo}"]:checked`)
    );

    if (!todasRespondidas) {
      e.preventDefault();
      erro.hidden = false;
      erro.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    erro.hidden = true;

    const btn = form.querySelector('.btn-enviar-quiz');
    btn.disabled = true;
    btn.textContent = 'Buscando pets...';
    // O formulário continua o envio normalmente (POST para
    // php/buscar_animais.php) após esta validação.
  });
});
