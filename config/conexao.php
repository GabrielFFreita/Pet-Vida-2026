<?php
// Essa página é dedicada a fazer a conexão com o banco de dados.
// (Comentário movido pra dentro das tags PHP — texto fora do <?php 
// é impresso literalmente na tela, o que quebra endpoints que precisam
// devolver JSON puro, como o solicitar_adocao.php e o dashboard.

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Isso aqui em cima liga os "erros", pq senão o php quando der erro aparece apenas uma tela branca.

$host = "tini.click";
$dbname = "petvida_db";
$usuario = "petvida_db";
$senha = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('[Pet-Vida] Falha de conexão com o banco de dados: ' . $e->getMessage());

    if (defined('PV_JSON_CONTEXT')) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode([
            'success' => false,
            'message' => 'Erro interno no servidor. Tente novamente em instantes.',
        ]);
        exit;
    }

    require_once __DIR__ . '/../includes/helpers.php';
    if (!headers_sent()) {
        http_response_code(500);
    }
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Erro temporário | Pet Vida</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(assetPath('css/toast.css'), ENT_QUOTES, 'UTF-8'); ?>">
    <style>
        body{font-family:Arial,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#fcfbf5;margin:0;text-align:center;padding:24px;}
        a{color:#1B4332;}
    </style>
</head>
<body>
    <div>
        <h1>Ops! Algo deu errado.</h1>
        <p>Não foi possível conectar ao banco de dados agora. Tente novamente em instantes.</p>
        <p><a href="<?php echo htmlspecialchars(rootPath('index.php'), ENT_QUOTES, 'UTF-8'); ?>">Voltar para a página inicial</a></p>
    </div>
    <script src="<?php echo htmlspecialchars(assetPath('js/toast.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.mostrarToast) {
                mostrarToast('Não foi possível conectar ao banco de dados. Tente novamente em instantes.', 'erro', 0);
            }
        });
    </script>
</body>
</html>
    <?php
    exit;
}

?>