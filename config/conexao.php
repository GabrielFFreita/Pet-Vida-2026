<?php

ini_set('display_errors', getenv('APP_DEBUG') === '1' ? '1' : '0');
error_reporting(E_ALL);

/**
 * Obtém uma variável obrigatória de conexão sem expor seu valor em mensagens.
 */
function obterVariavelBancoObrigatoria(string $nome): string
{
    $valor = getenv($nome);

    if ($valor === false || $valor === '') {
        error_log('Configuração obrigatória do banco de dados ausente: ' . $nome);
        http_response_code(500);
        exit('Configuração do banco de dados indisponível.');
    }

    return $valor;
}

$host = obterVariavelBancoObrigatoria('DB_HOST');
$porta = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'petvida_db';
$usuario = getenv('DB_USER') ?: 'petvida_db';
$senha = getenv('DB_PASSWORD')?: ''; 

if ($senha === false) {
    $senha = '';
}

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$porta;dbname=$dbname;charset=utf8mb4",
        $usuario,
        $senha
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

?>
