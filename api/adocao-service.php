<?php

/**
 * Cria uma solicitação de adoção e reserva o animal de forma atômica.
 * A linha do animal é bloqueada antes de qualquer decisão de negócio.
 */
function criarSolicitacaoAdocao(PDO $pdo, int $idUsuario, int $idAnimal): array
{
    if ($idUsuario < 1 || $idAnimal < 1) {
        return [
            'sucesso' => false,
            'http_status' => 400,
            'motivo' => 'dados_invalidos',
            'mensagem' => 'Dados da solicitação inválidos.',
        ];
    }

    $pdo->beginTransaction();

    try {
        $stmtAnimal = $pdo->prepare(
            "SELECT id_animal, status_adocao
             FROM animais_adocao
             WHERE id_animal = :id_animal
             FOR UPDATE"
        );
        $stmtAnimal->execute([':id_animal' => $idAnimal]);
        $animal = $stmtAnimal->fetch(PDO::FETCH_ASSOC);

        if (!$animal) {
            $pdo->rollBack();
            return [
                'sucesso' => false,
                'http_status' => 404,
                'motivo' => 'pet_nao_encontrado',
                'mensagem' => 'Pet não encontrado no sistema.',
            ];
        }

        if ($animal['status_adocao'] !== 'Disponível') {
            $pdo->rollBack();
            return [
                'sucesso' => false,
                'http_status' => 409,
                'motivo' => 'pet_indisponivel',
                'mensagem' => 'Este pet já recebeu uma solicitação ou já foi adotado.',
            ];
        }

        $stmtDuplicado = $pdo->prepare(
            "SELECT id_adocao
             FROM adocao
             WHERE id_usuario = :id_usuario
               AND id_animal = :id_animal
               AND status = 'Pendente'
             LIMIT 1"
        );
        $stmtDuplicado->execute([
            ':id_usuario' => $idUsuario,
            ':id_animal' => $idAnimal,
        ]);

        if ($stmtDuplicado->fetch()) {
            $pdo->rollBack();
            return [
                'sucesso' => false,
                'http_status' => 409,
                'motivo' => 'ja_solicitado',
                'mensagem' => 'Você já enviou um pedido para este pet. Aguarde o retorno do abrigo.',
            ];
        }

        $stmtInsert = $pdo->prepare(
            "INSERT INTO adocao (id_usuario, id_animal, data_solicitacao, status)
             VALUES (:id_usuario, :id_animal, CURDATE(), 'Pendente')"
        );
        $stmtInsert->execute([
            ':id_usuario' => $idUsuario,
            ':id_animal' => $idAnimal,
        ]);

        $stmtAtualizarPet = $pdo->prepare(
            "UPDATE animais_adocao
             SET status_adocao = 'Em processo'
             WHERE id_animal = :id_animal
               AND status_adocao = 'Disponível'"
        );
        $stmtAtualizarPet->execute([':id_animal' => $idAnimal]);

        if ($stmtAtualizarPet->rowCount() !== 1) {
            throw new RuntimeException('A reserva do pet não foi confirmada.');
        }

        $pdo->commit();

        return [
            'sucesso' => true,
            'http_status' => 200,
            'mensagem' => 'Sua solicitação de adoção foi enviada com sucesso!',
        ];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}
