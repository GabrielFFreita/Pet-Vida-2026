// ============================================================
// QUIZ.JS - Pet Quiz
// ============================================================

// ── Dados do quiz ────────────────────────────────────────────
const perguntas = [
    {
        pergunta: "Como é sua rotina durante a semana?",
        opcoes: ["Muito corrida", "Moderada", "Tranquila"]
    },
    {
        pergunta: "Com que frequência você sai de casa?",
        opcoes: ["Todos os dias", "Algumas vezes por semana", "Raramente"]
    },
    {
        pergunta: "Quanto pretende investir mensalmente nos cuidados do pet?",
        opcoes: ["Até R$100", "Entre R$100 e R$300", "Mais de R$300"]
    },
    {
        pergunta: "O que te motiva a querer um pet?",
        opcoes: ["Ter companhia no dia a dia", "Compartilhar momentos e atividades", "Cuidar e oferecer um lar"]
    },
    {
        pergunta: "Quais atividades você imagina fazer com seu pet?",
        opcoes: ["Relaxar e aproveitar a companhia em casa", "Um pouco de tudo", "Brincadeiras e atividades ao ar livre"]
    },
    {
        pergunta: "Qual é seu tipo de moradia?",
        opcoes: ["Moro em um espaço pequeno", "Moro em um espaço médio", "Moro em um espaço grande"]
    },
    {
        pergunta: "Quanto tempo livre você possui diariamente para dedicar ao pet?",
        opcoes: ["Menos de 1 hora", "Entre 1 e 3 horas", "Mais de 3 horas"]
    },
    {
        pergunta: "Você possui alguma preferência para o sexo do pet?",
        opcoes: ["Não tenho preferência", "Macho", "Fêmea"]
    }
];

const imagens = {
    "Muito corrida": "fa-solid fa-person-running",
    "Moderada": "fa-solid fa-calendar-days",
    "Tranquila": "fa-solid fa-mug-hot",
    "Todos os dias": "fa-solid fa-car",
    "Algumas vezes por semana": "fa-solid fa-store",
    "Raramente": "fa-solid fa-bed",
    "Até R$100": "fa-solid fa-coins",
    "Entre R$100 e R$300": "fa-solid fa-wallet",
    "Mais de R$300": "fa-solid fa-sack-dollar",
    "Ter companhia no dia a dia": "fa-solid fa-heart",
    "Compartilhar momentos e atividades": "fa-solid fa-dog",
    "Cuidar e oferecer um lar": "fa-solid fa-hand-holding-heart",
    "Relaxar e aproveitar a companhia em casa": "fa-solid fa-couch",
    "Um pouco de tudo": "fa-solid fa-scale-balanced",
    "Brincadeiras e atividades ao ar livre": "fa-solid fa-bone",
    "Moro em um espaço pequeno": "fa-solid fa-building",
    "Moro em um espaço médio": "fa-solid fa-city",
    "Moro em um espaço grande": "fa-solid fa-house",
    "Menos de 1 hora": "fa-regular fa-clock",
    "Entre 1 e 3 horas": "fa-regular fa-clock",
    "Mais de 3 horas": "fa-regular fa-clock",
    "Não tenho preferência": "fa-solid fa-paw",
    "Macho": "fa-solid fa-mars",
    "Fêmea": "fa-solid fa-venus"
};

// ── Estado ────────────────────────────────────────────────────
let perguntaAtual = 0;
let respostas = [];

// ── Elementos DOM ─────────────────────────────────────────────
const contadorEl = document.getElementById("contador");
const perguntaEl = document.getElementById("pergunta");
const opcoesEl = document.getElementById("opcoes");
const progressoEl = document.getElementById("linhaProgresso");
const voltarBtn = document.getElementById("voltar");
const resultadoBtn = document.getElementById("btnResultado");
const loadingScreen = document.getElementById("loadingScreen");

// ── Funções principais ──────────────────────────────────────
function carregarPergunta() {
    const pergunta = perguntas[perguntaAtual];

    contadorEl.innerText = `Pergunta ${perguntaAtual + 1} de ${perguntas.length}`;
    perguntaEl.innerText = pergunta.pergunta;

    atualizarProgresso();

    let html = "";
    pergunta.opcoes.forEach(opcao => {
        const selecionada = (respostas[perguntaAtual] === opcao) ? "selecionada" : "";
        const icone = imagens[opcao] || "fa-solid fa-question";
        html += `
            <div class="opcao-quiz ${selecionada}" data-opcao="${opcao}">
                <i class="${icone}"></i>
                <p>${opcao}</p>
            </div>
        `;
    });

    opcoesEl.innerHTML = html;

    // Event listeners para as opções (delegação)
    opcoesEl.querySelectorAll(".opcao-quiz").forEach(el => {
        el.addEventListener("click", () => selecionarOpcao(el));
    });

    // Configura o botão voltar
    voltarBtn.onclick = (perguntaAtual === 0) ? voltarInicio : voltarPergunta;

    // Mostra/esconde botão "Dar Match"
    if (perguntaAtual === perguntas.length - 1 && respostas[perguntaAtual]) {
        resultadoBtn.style.display = "block";
    } else {
        resultadoBtn.style.display = "none";
    }
}

function atualizarProgresso() {
    let html = "";
    for (let i = 0; i < perguntas.length; i++) {
        html += `<div class="circulo ${i <= perguntaAtual ? 'ativo' : ''}"></div>`;
    }
    progressoEl.innerHTML = html;
}

function selecionarOpcao(elemento) {
    const resposta = elemento.dataset.opcao;

    // Remove seleção de todas as opções
    opcoesEl.querySelectorAll(".opcao-quiz").forEach(el => el.classList.remove("selecionada"));
    elemento.classList.add("selecionada");
    respostas[perguntaAtual] = resposta;

    // Se for a última pergunta, mostra o botão de resultado
    if (perguntaAtual === perguntas.length - 1) {
        resultadoBtn.style.display = "block";
        return;
    }

    // Avança para a próxima pergunta após um breve delay
    setTimeout(() => {
        if (perguntaAtual < perguntas.length - 1) {
            perguntaAtual++;
            carregarPergunta();
        }
    }, 300);
}

function voltarPergunta() {
    if (perguntaAtual > 0) {
        perguntaAtual--;
        carregarPergunta();
    }
}

function voltarInicio() {
    window.location.href = "../html/adocao.html";
}

// ── Finalizar Quiz ───────────────────────────────────────────
function finalizarQuiz() {
    if (!respostas[perguntaAtual]) {
        // Treme as opções se não respondeu a última
        opcoesEl.classList.add("shake");
        setTimeout(() => opcoesEl.classList.remove("shake"), 400);
        return;
    }

    // Mostra tela de carregamento
    loadingScreen.style.display = "flex";
    document.body.style.overflow = "hidden";

    // Envia as respostas via POST para quiz.php
    setTimeout(() => {
        fetch("quiz.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(respostas)
        })
        .then(r => r.text())
        .then(html => {
            document.body.innerHTML = html;
        })
        .catch(err => {
            loadingScreen.style.display = "none";
            document.body.style.overflow = "";
            alert("Ocorreu um erro ao processar o quiz. Tente novamente.");
        });
    }, 3000); // 3 segundos de carregamento (igual ao original)
}

// ── Event listener do botão "Dar Match" ──────────────────────
resultadoBtn.addEventListener("click", finalizarQuiz);

// ── Inicialização ─────────────────────────────────────────────
carregarPergunta();