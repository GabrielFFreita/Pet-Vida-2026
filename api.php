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
        echo json_encode(['error' => 'Ação inválida']);
}
function listarAnimais() {
    global $pdo;
    try {
        $especie = isset($_GET['especie']) ? $_GET['especie'] : '';
        $sexo = isset($_GET['sexo']) ? $_GET['sexo'] : '';
        $porte = isset($_GET['porte']) ? $_GET['porte'] : '';
        
        // CORREÇÃO FINAL: Tiramos o "animais_adocao." de dentro do WHERE e ORDER BY
        $sql = "SELECT animais_adocao.*, foto_animal.ds_img 
                FROM animais_adocao 
                LEFT JOIN foto_animal ON animais_adocao.id_animal = foto_animal.id_animal 
                WHERE status_adocao = 'Disponível'";
        $params = [];
        
        if (!empty($especie)) {
            $sql .= " AND especie = :especie";
            $params[':especie'] = $especie;
        }
        if (!empty($sexo)) {
            $sql .= " AND sexo = :sexo";
            $params[':sexo'] = $sexo;
        }
        if (!empty($porte)) {
            $sql .= " AND porte = :porte";
            $params[':porte'] = $porte;
        }
        
        $sql .= " ORDER BY animais_adocao.id_animal DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $animais = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($animais);
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
        
        // CORREÇÃO FINAL: Tiramos o prefixo do status_adocao aqui também
        $sql = "SELECT animais_adocao.*, foto_animal.ds_img 
                FROM animais_adocao 
                INNER JOIN favoritos ON animais_adocao.id_animal = favoritos.id_animal 
                LEFT JOIN foto_animal ON animais_adocao.id_animal = foto_animal.id_animal
                WHERE favoritos.id_usuario = :id_usuario AND status_adocao = 'Disponível'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
        $animais = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($animais);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
function buscarAnimal() {
    global $pdo;
    try {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        // CORREÇÃO: Adicionado LEFT JOIN para que o modal de detalhes também encontre a foto
        $sql = "SELECT a.*, f.ds_img FROM animais_adocao a 
                LEFT JOIN foto_animal f ON a.id_animal = f.id_animal 
                WHERE a.id_animal = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $animal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($animal) {
            echo json_encode($animal);
        } else {
            echo json_encode(['error' => 'Animal não encontrado']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function solicitarAdocao() {
    global $pdo;
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id_usuario']) || !isset($data['id_animal'])) {
            echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
            return;
        }
        
        // Iniciar transação
        $pdo->beginTransaction();
        
        // Inserir solicitação
        $sql = "INSERT INTO solicitacoes_adocao (id_usuario, id_animal, data_solicitacao, status_solicitacao) 
                VALUES (:id_usuario, :id_animal, CURDATE(), 'Pendente')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $data['id_usuario'],
            ':id_animal' => $data['id_animal']
        ]);
        
        // Atualizar status do animal
        $sql_update = "UPDATE animais_adocao SET status_adocao = 'Em Análise' WHERE id_animal = :id_animal";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([':id_animal' => $data['id_animal']]);
        
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function cadastrarUsuario() {
    global $pdo;
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['nome']) || empty($data['email']) || empty($data['senha'])) {
            echo json_encode(['success' => false, 'error' => 'Campos obrigatórios vazios']);
            return;
        }
        
        // Verificar se e-mail já existe
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $data['email']]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'E-mail já cadastrado']);
            return;
        }
        
        $sql = "INSERT INTO usuarios (nome, email, senha, telefone, cpf, cidade, estado, tipo_usuario, data_cadastro) 
                VALUES (:nome, :email, :senha, :telefone, :cpf, :cidade, :estado, 'Cliente', CURDATE())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $data['nome'],
            ':email' => $data['email'],
            ':senha' => password_hash($data['senha'], PASSWORD_DEFAULT),
            ':telefone' => $data['telefone'] ?? null,
            ':cpf' => $data['cpf'] ?? null,
            ':cidade' => $data['cidade'] ?? null,
            ':estado' => $data['estado'] ?? null
        ]);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function login() {
    global $pdo;
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['email']) || empty($data['senha'])) {
            echo json_encode(['success' => false, 'error' => 'Preencha todos os campos']);
            return;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $data['email']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario && password_verify($data['senha'], $usuario['senha'])) {
            unset($usuario['senha']); // Remove por segurança
            $_SESSION['usuario'] = $usuario;
            echo json_encode(['success' => true, 'usuario' => $usuario]);
        } else {
            echo json_encode(['success' => false, 'error' => 'E-mail ou senha incorretos']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function logout() {
    session_destroy();
    echo json_encode(['success' => true]);
}

function verificarSessao() {
    if (isset($_SESSION['usuario'])) {
        echo json_encode(['success' => true, 'usuario' => $_SESSION['usuario']]);
    } else {
        echo json_encode(['success' => false]);
    }
}

function registrarDoacao() {
    global $pdo;
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $sql = "INSERT INTO doacoes (id_usuario, tipo_doacao, descricao, valor, data_doacao) 
                VALUES (:id_usuario, :tipo_doacao, :descricao, :valor, CURDATE())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $data['id_usuario'],
            ':tipo_doacao' => $data['tipo_doacao'],
            ':descricao' => $data['descricao'],
            ':valor' => $data['valor']
        ]);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function toggleFavorito() {
    global $pdo;
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $id_usuario = $data['id_usuario'];
        $id_animal = $data['id_animal'];
        
        $stmt = $pdo->prepare("SELECT id_favorito FROM favoritos WHERE id_usuario = :id_usuario AND id_animal = :id_animal");
        $stmt->execute([':id_usuario' => $id_usuario, ':id_animal' => $id_animal]);
        $favorito = $stmt->fetch();
        
        if ($favorito) {
            $deleteStmt = $pdo->prepare("DELETE FROM favoritos WHERE id_favorito = :id");
            $deleteStmt->execute([':id' => $favorito['id_favorito']]);
            echo json_encode(['success' => true, 'favoritado' => false]);
        } else {
            $insertStmt = $pdo->prepare("INSERT INTO favoritos (id_usuario, id_animal, data_favorito) VALUES (:id_usuario, :id_animal, CURDATE())");
            $insertStmt->execute([':id_usuario' => $id_usuario, ':id_animal' => $id_animal]);
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
        $stmt->execute([':id_usuario' => $id_usuario, ':id_animal' => $id_animal]);
        
        echo json_encode(['success' => true, 'favoritado' => $stmt->fetch() ? true : false]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

