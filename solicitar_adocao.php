<?php
// Buffer de saída: previne que qualquer aviso/notice quebre o formato do JSON
ob_start();

require_once "conexao.php";
require_once "config_sessao.php";

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
            'mensagem' => 'Erro interno no servidor: ' . $erro['message'] . ' (linha ' . $erro['line'] . ')'
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
    // 2. Confirma que o animal existe e se está disponível ('Em processo' ou 'Adotado' barra o pedido)
    $stmtAnimal = $pdo->prepare("SELECT id_animal, nome, status_adocao FROM animais_adocao WHERE id_animal = :id_animal");
    $stmtAnimal->execute([':id_animal' => $idAnimal]);
    $animal = $stmtAnimal->fetch(PDO::FETCH_ASSOC);

    if (!$animal) {
        ob_end_clean();
        http_response_code(404);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Pet não encontrado no sistema.']);
        exit;
    }

    if ($animal['status_adocao'] !== 'Disponível') {
        ob_end_clean();
        echo json_encode([
            'sucesso' => false,
            'motivo'  => 'ja_adotado',
            'mensagem' => 'Este pet já recebeu uma solicitação ou já foi adotado por outra pessoa.'
        ]);
        exit;
    }

    // 3. Evita pedido duplicado do mesmo usuário para o mesmo animal
    $stmtDuplicado = $pdo->prepare("
        SELECT id_adocao FROM adocao
        WHERE id_usuario = :id_usuario AND id_animal = :id_animal AND status = 'Pendente'
    ");
    $stmtDuplicado->execute([':id_usuario' => $idUsuario, ':id_animal' => $idAnimal]);

    if ($stmtDuplicado->fetch()) {
        ob_end_clean();
        echo json_encode([
            'sucesso' => false,
            'motivo'  => 'ja_solicitado',
            'mensagem' => 'Você já enviou um pedido para este pet. Aguarde o retorno do abrigo.'
        ]);
        exit;
    }

    // Iniciamos uma transação para garantir atomicidade das operações seguintes
    $pdo->beginTransaction();

    // 4. Registra o pedido de adoção
    $stmtInsert = $pdo->prepare("
        INSERT INTO adocao (id_usuario, id_animal, data_solicitacao, status)
        VALUES (:id_usuario, :id_animal, CURDATE(), 'Pendente')
    ");
    $stmtInsert->execute([
        ':id_usuario' => $idUsuario,
        ':id_animal'  => $idAnimal,
    ]);

    // 5. CRUCIAL: Atualiza o status do pet na tabela principal para 'Em processo'
    $stmtUpdatePet = $pdo->prepare("
        UPDATE animais_adocao 
        SET status_adocao = 'Em processo' 
        WHERE id_animal = :id_animal
    ");
    $stmtUpdatePet->execute([':id_animal' => $idAnimal]);

    // Confirma as duas mudanças de forma simultânea no banco
    $pdo->commit();

    ob_end_clean();
    echo json_encode([
        'sucesso'  => true,
        'mensagem' => 'Sua solicitação de adoção foi enviada com sucesso!'
    ]);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'sucesso'  => false,
        'mensagem' => 'Erro ao processar requisição no banco: ' . $e->getMessage()
    ]);
    exit;
}