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

/**
 * Lê a senha preferencialmente de um Docker secret e mantém compatibilidade
 * com DB_PASSWORD para execuções fora do Docker.
 */
function obterSenhaBanco(): string
{
    $arquivo = getenv('DB_PASSWORD_FILE');

    if ($arquivo !== false && $arquivo !== '') {
        if (!is_readable($arquivo)) {
            error_log('O arquivo configurado em DB_PASSWORD_FILE não pode ser lido.');
            http_response_code(500);
            exit('Configuração do banco de dados indisponível.');
        }

        $senha = file_get_contents($arquivo);
        if ($senha === false) {
            error_log('Não foi possível ler o secret do banco de dados.');
            http_response_code(500);
            exit('Configuração do banco de dados indisponível.');
        }

        return rtrim($senha, "\r\n");
    }

    return obterVariavelBancoObrigatoria('DB_PASSWORD');
}

$host = obterVariavelBancoObrigatoria('DB_HOST');
$porta = getenv('DB_PORT') ?: '3306';
$dbname = obterVariavelBancoObrigatoria('DB_NAME');
$usuario = obterVariavelBancoObrigatoria('DB_USER');
$senha = obterSenhaBanco();

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$porta;dbname=$dbname;charset=utf8mb4",
        $usuario,
        $senha,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log('Falha na conexão com o banco de dados: ' . $e->getMessage());
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados.');
}
