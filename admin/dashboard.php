<?php
require_once __DIR__ . "/../config/sessao.php";
require_once __DIR__ . "/../config/conexao.php";

verificarAdmin();

function fetchSingleValue(PDO $pdo, string $sql, array $params = []): int
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function fetchChartData(PDO $pdo, string $sql): array
{
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        "labels" => array_map(static fn ($row) => (string) array_values($row)[0], $rows),
        "values" => array_map(static fn ($row) => (int) array_values($row)[1], $rows),
    ];
}

$metricas = [
    "animais_disponiveis" => fetchSingleValue(
        $pdo,
        "SELECT COUNT(*) FROM animais_adocao WHERE status_adocao = 'Disponível'"
    ),
    "animais_em_processo" => fetchSingleValue(
        $pdo,
        "SELECT COUNT(*) FROM animais_adocao WHERE status_adocao = 'Em processo'"
    ),
    "abrigos_cadastrados" => fetchSingleValue($pdo, "SELECT COUNT(*) FROM abrigos"),
    "pedidos_pendentes" => fetchSingleValue(
        $pdo,
        "SELECT COUNT(*) FROM adocao WHERE status = 'Pendente'"
    ),
];

$animaisPorStatus = fetchChartData(
    $pdo,
    "SELECT status_adocao, COUNT(*) AS total
     FROM animais_adocao
     GROUP BY status_adocao
     ORDER BY total DESC"
);

$animaisPorAbrigo = fetchChartData(
    $pdo,
    "SELECT ab.nome, COUNT(*) AS total
     FROM animais_adocao a
     INNER JOIN abrigos ab ON ab.id = a.id_abrigo
     GROUP BY ab.id, ab.nome
     ORDER BY total DESC, ab.nome ASC"
);

$resumoAbrigo = $pdo->query(
    "SELECT ab.nome, COUNT(*) AS total
     FROM animais_adocao a
     INNER JOIN abrigos ab ON ab.id = a.id_abrigo
     GROUP BY ab.id, ab.nome
     ORDER BY total DESC, ab.nome ASC
     LIMIT 1"
)->fetch(PDO::FETCH_ASSOC);

$ultimoCadastro = $pdo->query(
    "SELECT nome, data_cadastro
     FROM animais_adocao
     WHERE data_cadastro IS NOT NULL
     ORDER BY data_cadastro DESC, id_animal DESC
     LIMIT 1"
)->fetch(PDO::FETCH_ASSOC);

$statusColors = [
    "Disponível" => "#52B788",
    "Em processo" => "#E07B39",
    "Adotado" => "#2D6A4F",
];

$chartStatusColors = [];
foreach ($animaisPorStatus["labels"] as $label) {
    $chartStatusColors[] = $statusColors[$label] ?? "#6B7280";
}

$chartBarColors = ["#2D6A4F", "#52B788", "#95D5B2", "#E07B39", "#D9C7AE", "#6B7280"];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo | Pet Vida</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@400;600&family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .card-metrica h3 {
            font-size: 1.15rem;
            margin-bottom: 10px;
            color: var(--primaria-escura);
        }

        .card-metrica .valor {
            font-size: 2rem;
            font-weight: 700;
            color: var(--texto);
        }

        .card-metrica .legenda {
            display: block;
            margin-top: 8px;
            font-size: 0.92rem;
            color: var(--texto-leve);
        }

        .dashboard-analitico {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .painel-card {
            background: var(--fundo-card);
            border: 1px solid var(--borda);
            border-radius: var(--raio);
            box-shadow: var(--sombra);
            padding: 24px;
        }

        .painel-card h2 {
            font-family: 'Fraunces', 'Playfair Display', serif;
            color: var(--primaria-escura);
            font-size: 1.4rem;
            margin-bottom: 6px;
        }

        .painel-card p {
            color: var(--texto-leve);
            margin-bottom: 18px;
            font-size: 0.95rem;
        }

        .chart-box {
            position: relative;
            min-height: 280px;
        }

        .insights-lista {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }

        .insight-item {
            background: #FFFDFB;
            border: 1px solid var(--borda);
            border-radius: 10px;
            padding: 18px;
        }

        .insight-item strong {
            display: block;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--texto-leve);
            margin-bottom: 8px;
        }

        .insight-item span {
            display: block;
            font-size: 1.05rem;
            color: var(--texto);
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .content {
                padding: 24px;
            }

            .chart-box {
                min-height: 240px;
            }
        }
    </style>
</head>
<body>

    <?php $adminActivePage = 'dashboard'; require __DIR__ . '/../includes/menu-admin.php'; ?>

    <main class="content">
        <h1>Visão Geral do Sistema</h1>
        <p class="subtitulo">Gerencie as adoções, abrigos parceiros e métricas operacionais com base nos dados atuais da plataforma.</p>

        <section class="dashboard-grid">
            <div class="card-metrica">
                <h3>Animais Disponíveis</h3>
                <div class="valor"><?= $metricas["animais_disponiveis"]; ?></div>
                <span class="legenda">Prontos para nova solicitação</span>
            </div>
            <div class="card-metrica">
                <h3>Animais Em Processo</h3>
                <div class="valor"><?= $metricas["animais_em_processo"]; ?></div>
                <span class="legenda">Em análise de adoção</span>
            </div>
            <div class="card-metrica">
                <h3>Abrigos Cadastrados</h3>
                <div class="valor"><?= $metricas["abrigos_cadastrados"]; ?></div>
                <span class="legenda">Parceiros ativos na base</span>
            </div>
            <div class="card-metrica">
                <h3>Pedidos Pendentes</h3>
                <div class="valor"><?= $metricas["pedidos_pendentes"]; ?></div>
                <span class="legenda">Aguardando decisão</span>
            </div>
        </section>

        <section class="dashboard-analitico">
            <article class="painel-card">
                <h2>Status dos animais</h2>
                <p>Distribuição atual entre disponibilidade e andamento do processo.</p>
                <div class="chart-box">
                    <canvas id="graficoStatus"></canvas>
                </div>
            </article>

            <article class="painel-card">
                <h2>Animais por abrigo</h2>
                <p>Visão rápida da concentração de animais cadastrados por parceiro.</p>
                <div class="chart-box">
                    <canvas id="graficoAbrigos"></canvas>
                </div>
            </article>
        </section>

        <section class="painel-card">
            <h2>Leituras rápidas</h2>
            <p>Resumo textual para facilitar a leitura operacional do painel.</p>

            <div class="insights-lista">
                <div class="insight-item">
                    <strong>Abrigo com mais animais</strong>
                    <span>
                        <?= htmlspecialchars($resumoAbrigo["nome"] ?? "Sem dados", ENT_QUOTES, "UTF-8"); ?>
                        <?php if (!empty($resumoAbrigo["total"])): ?>
                            (<?= (int) $resumoAbrigo["total"]; ?>)
                        <?php endif; ?>
                    </span>
                </div>

                <div class="insight-item">
                    <strong>Último cadastro</strong>
                    <span>
                        <?php if (!empty($ultimoCadastro)): ?>
                            <?= htmlspecialchars($ultimoCadastro["nome"], ENT_QUOTES, "UTF-8"); ?> em <?= date("d/m/Y", strtotime($ultimoCadastro["data_cadastro"])); ?>
                        <?php else: ?>
                            Sem dados recentes
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const statusLabels = <?= json_encode($animaisPorStatus["labels"], JSON_UNESCAPED_UNICODE); ?>;
        const statusValues = <?= json_encode($animaisPorStatus["values"]); ?>;
        const statusColors = <?= json_encode($chartStatusColors); ?>;

        const abrigoLabels = <?= json_encode($animaisPorAbrigo["labels"], JSON_UNESCAPED_UNICODE); ?>;
        const abrigoValues = <?= json_encode($animaisPorAbrigo["values"]); ?>;
        const abrigoColors = <?= json_encode(array_slice(array_merge($chartBarColors, $chartBarColors), 0, max(count($animaisPorAbrigo["labels"]), 1))); ?>;

        const tooltipTitleColor = "#1B4332";
        const tooltipBodyColor = "#3F3F46";
        const tooltipBackgroundColor = "rgba(255, 253, 251, 0.96)";
        const tooltipBorderColor = "#D9C7AE";

        new Chart(document.getElementById("graficoStatus"), {
            type: "doughnut",
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: statusColors,
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: {
                            usePointStyle: true,
                            padding: 18
                        }
                    },
                    tooltip: {
                        backgroundColor: tooltipBackgroundColor,
                        borderColor: tooltipBorderColor,
                        borderWidth: 1,
                        titleColor: tooltipTitleColor,
                        bodyColor: tooltipBodyColor,
                        displayColors: true,
                        boxPadding: 4
                    }
                },
                cutout: "62%"
            }
        });

        new Chart(document.getElementById("graficoAbrigos"), {
            type: "bar",
            data: {
                labels: abrigoLabels,
                datasets: [{
                    label: "Animais cadastrados",
                    data: abrigoValues,
                    backgroundColor: abrigoColors,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: tooltipBackgroundColor,
                        borderColor: tooltipBorderColor,
                        borderWidth: 1,
                        titleColor: tooltipTitleColor,
                        bodyColor: tooltipBodyColor,
                        displayColors: true,
                        boxPadding: 4
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: "#6B7280"
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: "#6B7280"
                        },
                        grid: {
                            color: "#EDE8DF"
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
