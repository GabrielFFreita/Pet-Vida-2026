<?php
// Buffer de saída: previne que qualquer aviso/notice quebre o formato do JSON
ob_start();

require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../config/sessao.php";
require_once __DIR__ . "/adocao-service.php";

header('Content-Type: application/json; charset=utf-8');

// Garante que mesmo em um erro inesperado o retorno seja um JSON válido
register_shutdown_function(function () {
    $erro = error_get_last();
    if ($erro && in_array($erro['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level() > 0) { ob_end_clean(); }
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'sucesso'  => false,
            'mensagem' => 'Ocorreu um erro interno. Tente novamente mais tarde.'
        ]);
    }
});

// 1. Usuário precisa estar logado
if (!isset($_SESSION['nome_usuario']) || !isset($_SESSION['id_usuario'])) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode([
        'sucesso' => false,
        'motivo'  => 'nao_logado',
        'mensagem' => 'Você precisa estar logado para solicitar uma adoção.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

$idUsuario = (int)$_SESSION['id_usuario'];
$idAnimal  = filter_input(INPUT_POST, 'id_animal', FILTER_VALIDATE_INT);

if (!$idAnimal) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Pet inválido.']);
    exit;
}

try {
    $resultado = criarSolicitacaoAdocao($pdo, $idUsuario, $idAnimal);

    ob_end_clean();
    http_response_code($resultado['http_status']);
    unset($resultado['http_status']);
    echo json_encode($resultado);
    exit;
} catch (Throwable $e) {
    error_log('Falha ao criar solicitação de adoção: ' . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não foi possível registrar sua solicitação. Tente novamente mais tarde.'
    ]);
    exit;
}
