<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once "conexao.php";
session_start();

$acao = isset($_GET['acao']) ? $_GET['acao'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

switch($acao) {
    case 'listar_animais':
        listarAnimais();
        break;
    case 'buscar_animal':
        buscarAnimal();
        break;
    case 'solicitar_adocao':
        solicitarAdocao();
        break;
    case 'cadastrar_usuario':
        cadastrarUsuario();
        break;
    case 'login':
        login();
        break;
    case 'logout':
        logout();
        break;
    case 'verificar_sessao':
        verificarSessao();
        break;
    case 'doar':
        registrarDoacao();
        break;
    case 'toggle_favorito':
        toggleFavorito();
        break;
    case 'verificar_favorito':
        verificarFavorito();
        break;
    case 'listar_favoritos':
        listarFavoritos();
        break;
    default:
        echo json_encode(['error' => 'Ação não encontrada']);
}

function listarAnimais() {
    global $pdo;
    try {
        $sql = "SELECT * FROM animais_adocao WHERE status_adocao = 'Disponível'";
        $params = [];
        
        if (isset($_GET['especie']) && !empty($_GET['especie'])) {
            $sql .= " AND especie = :especie";
            $params[':especie'] = $_GET['especie'];
        }
        if (isset($_GET['sexo']) && !empty($_GET['sexo'])) {
            $sql .= " AND sexo = :sexo";
            $params[':sexo'] = $_GET['sexo'];
        }
        if (isset($_GET['porte']) && !empty($_GET['porte'])) {
            $sql .= " AND porte = :porte";
            $params[':porte'] = $_GET['porte'];
        }
        
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        $animais = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($animais as &$animal) {
            $fotoStmt = $pdo->prepare("SELECT caminho_foto FROM foto_animal WHERE id_animal = :id LIMIT 1");
            $fotoStmt->execute([':id' => $animal['id_animal']]);
            $foto = $fotoStmt->fetch(PDO::FETCH_ASSOC);
            $animal['foto'] = $foto ? $foto['caminho_foto'] : null;
        }
        
        echo json_encode($animais);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function buscarAnimal() {
    global $pdo;
    try {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if (!$id) {
            echo json_encode(['error' => 'ID não informado']);
            return;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM animais_adocao WHERE id_animal = :id");
        $stmt->execute([':id' => $id]);
        $animal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($animal) {
            $fotoStmt = $pdo->prepare("SELECT caminho_foto FROM foto_animal WHERE id_animal = :id LIMIT 1");
            $fotoStmt->execute([':id' => $id]);
            $foto = $fotoStmt->fetch(PDO::FETCH_ASSOC);
            $animal['foto'] = $foto ? $foto['caminho_foto'] : null;
        }
        
        echo json_encode($animal);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function solicitarAdocao() {
    global $pdo;
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id_usuario']) || !isset($data['id_animal'])) {
            echo json_encode(['error' => 'Dados incompletos']);
            return;
        }
        
        $checkStmt = $pdo->prepare("SELECT id_adocao FROM adocao WHERE id_usuario = :id_usuario AND id_animal = :id_animal AND status = 'Pendente'");
        $checkStmt->execute([
            ':id_usuario' => $data['id_usuario'],
            ':id_animal' => $data['id_animal']
        ]);
        
        if ($checkStmt->fetch()) {
            echo json_encode(['error' => 'Você já possui uma solicitação pendente para este animal']);
            return;
        }
        
        $stmt = $pdo->prepare("INSERT INTO adocao (id_usuario, id_animal, data_solicitacao, status) VALUES (:id_usuario, :id_animal, CURDATE(), 'Pendente')");
        $stmt->execute([
            ':id_usuario' => $data['id_usuario'],
            ':id_animal' => $data['id_animal']
        ]);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function cadastrarUsuario() {
    global $pdo;
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $nome = trim($data['nome'] ?? '');
        $email = trim($data['email'] ?? '');
        $senha = trim($data['senha'] ?? '');
        $telefone = trim($data['telefone'] ?? '');
        $cpf = trim($data['cpf'] ?? '');
        $cidade = trim($data['cidade'] ?? '');
        $estado = trim($data['estado'] ?? '');
        
        if (empty($nome) || empty($email) || empty($senha)) {
            echo json_encode(['error' => 'Preencha os campos obrigatórios']);
            return;
        }
        
        $checkStmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email = :email");
        $checkStmt->execute([':email' => $email]);
        if ($checkStmt->fetch()) {
            echo json_encode(['error' => 'E-mail já cadastrado']);
            return;
        }
        
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO usuario (nome, email, senha, telefone, cpf, cidade, estado) VALUES (:nome, :email, :senha, :telefone, :cpf, :cidade, :estado)");
        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':senha' => $senhaHash,
            ':telefone' => $telefone,
            ':cpf' => $cpf,
            ':cidade' => $cidade,
            ':estado' => $estado
        ]);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function login() {
    global $pdo;
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $email = trim($data['email'] ?? '');
        $senha = trim($data['senha'] ?? '');
        
        if (empty($email) || empty($senha)) {
            echo json_encode(['error' => 'Preencha todos os campos']);
            return;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            unset($usuario['senha']);
            $_SESSION['nome_usuario'] = $usuario['nome'];
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['ultima_atividade'] = time();
            echo json_encode(['success' => true, 'usuario' => $usuario]);
        } else {
            echo json_encode(['error' => 'E-mail ou senha inválidos']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function logout() {
    session_destroy();
    echo json_encode(['success' => true]);
}

function verificarSessao() {
    if (isset($_SESSION['id_usuario']) && isset($_SESSION['nome_usuario'])) {
        echo json_encode([
            'success' => true,
            'usuario' => [
                'id_usuario' => $_SESSION['id_usuario'],
                'nome' => $_SESSION['nome_usuario']
            ]
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
}

function registrarDoacao() {
    global $pdo;
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id_usuario']) || !isset($data['tipo_doacao'])) {
            echo json_encode(['error' => 'Dados incompletos']);
            return;
        }
        
        $valor = isset($data['valor']) ? $data['valor'] : null;
        $descricao = isset($data['descricao']) ? $data['descricao'] : '';
        
        $stmt = $pdo->prepare("INSERT INTO doacao (id_usuario, tipo_doacao, descricao, valor, status, data_doacao) VALUES (:id_usuario, :tipo_doacao, :descricao, :valor, 'Pendente', CURDATE())");
        $stmt->execute([
            ':id_usuario' => $data['id_usuario'],
            ':tipo_doacao' => $data['tipo_doacao'],
            ':descricao' => $descricao,
            ':valor' => $valor
        ]);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function toggleFavorito() {
    global $pdo;
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id_usuario']) || !isset($data['id_animal'])) {
            echo json_encode(['error' => 'Dados incompletos']);
            return;
        }
        
        $checkStmt = $pdo->prepare("SELECT id_favorito FROM favoritos WHERE id_usuario = :id_usuario AND id_animal = :id_animal");
        $checkStmt->execute([
            ':id_usuario' => $data['id_usuario'],
            ':id_animal' => $data['id_animal']
        ]);
        
        if ($checkStmt->fetch()) {
            $deleteStmt = $pdo->prepare("DELETE FROM favoritos WHERE id_usuario = :id_usuario AND id_animal = :id_animal");
            $deleteStmt->execute([
                ':id_usuario' => $data['id_usuario'],
                ':id_animal' => $data['id_animal']
            ]);
            echo json_encode(['success' => true, 'favoritado' => false]);
        } else {
            $insertStmt = $pdo->prepare("INSERT INTO favoritos (id_usuario, id_animal, data_favorito) VALUES (:id_usuario, :id_animal, CURDATE())");
            $insertStmt->execute([
                ':id_usuario' => $data['id_usuario'],
                ':id_animal' => $data['id_animal']
            ]);
            echo json_encode(['success' => true, 'favoritado' => true]);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function verificarFavorito() {
    global $pdo;
    try {
        $id_usuario = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : null;
        $id_animal = isset($_GET['id_animal']) ? $_GET['id_animal'] : null;
        
        if (!$id_usuario || !$id_animal) {
            echo json_encode(['success' => false, 'favoritado' => false]);
            return;
        }
        
        $stmt = $pdo->prepare("SELECT id_favorito FROM favoritos WHERE id_usuario = :id_usuario AND id_animal = :id_animal");
        $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':id_animal' => $id_animal
        ]);
        
        echo json_encode([
            'success' => true,
            'favoritado' => $stmt->fetch() ? true : false
        ]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function listarFavoritos() {
    global $pdo;
    try {
        $id_usuario = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : null;
        
        if (!$id_usuario) {
            echo json_encode(['error' => 'Usuário não informado']);
            return;
        }
        
        $sql = "SELECT a.* FROM animais_adocao a 
                INNER JOIN favoritos f ON a.id_animal = f.id_animal 
                WHERE f.id_usuario = :id_usuario AND a.status_adocao = 'Disponível'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
        $animais = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($animais as &$animal) {
            $fotoStmt = $pdo->prepare("SELECT caminho_foto FROM foto_animal WHERE id_animal = :id LIMIT 1");
            $fotoStmt->execute([':id' => $animal['id_animal']]);
            $foto = $fotoStmt->fetch(PDO::FETCH_ASSOC);
            $animal['foto'] = $foto ? $foto['caminho_foto'] : null;
        }
        
        echo json_encode($animais);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>