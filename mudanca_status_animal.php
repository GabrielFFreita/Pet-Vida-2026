<?php
// ============================================================
// MUDANCA_STATUS_ANIMAL.PHP - ATUALIZAÇÃO VIA POST (PDO)
// ============================================================
require_once "conexao.php";
require_once "config_sessao.php";
verificarLogado(); // Proteção para garantir privilégios de acesso

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Captura o ID do animal enviado pelo botão do formulário de adoção
    $id_animal = isset($_POST['btn-confirmar-adocao']) ? intval($_POST['btn-confirmar-adocao']) : (isset($_POST['id_animal']) ? intval($_POST['id_animal']) : null);
    $novoStatus = $_POST['status'] ?? 'Adotado';

    if (!$id_animal) {
        header("Location: adocao.php?erro=id_invalido");
        exit;
    }

    // Filtro rígido para aceitar apenas os três status válidos do banco
    $statusPermitidos = ['Disponível', 'Em Processo', 'Adotado'];
    if (!in_array($novoStatus, $statusPermitidos)) {
        $novoStatus = 'Adotado';
    }

    try {
        // Query de alteração estruturada em PDO seguro contra SQL Injection
        $sql = "UPDATE animais_adocao SET status_adocao = :status WHERE id_animal = :id";
        $stmt = $pdo->prepare($sql);
        
        $sucesso = $stmt->execute([
            ':status' => $novoStatus,
            ':id'     => $id_animal
        ]);

        if ($sucesso) {
            header("Location: adocao.php?sucesso=status_atualizado");
            exit;
        } else {
            header("Location: adocao.php?erro=falha_execucao");
            exit;
        }

    } catch (PDOException $e) {
        die("Erro crítico no banco de dados ao mudar o status do pet: " . $e->getMessage());
    }
} else {
    header("Location: adocao.php");
    exit;
}
?>