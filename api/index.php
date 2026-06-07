<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';

// Autenticação simples por token
function autenticar() {
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? '';
    $token = str_replace('Bearer ', '', $token);

    if (empty($token)) {
        http_response_code(401);
        echo json_encode(['erro' => 'Token não informado']);
        exit;
    }

    $conn = conectar();
    $stmt = $conn->prepare('SELECT id, nome FROM usuarios WHERE MD5(CONCAT(id, email)) = ?');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $conn->close();

    if (!$usuario) {
        http_response_code(401);
        echo json_encode(['erro' => 'Token inválido']);
        exit;
    }

    return $usuario;
}

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$partes = explode('/', trim($uri, '/'));
$id = isset($partes[3]) ? intval($partes[3]) : null;

// Roteamento
if ($method === 'GET' && !$id) {
    // GET /api/tarefas
    $usuario = autenticar();
    $conn = conectar();
    $stmt = $conn->prepare('SELECT t.*, c.nome AS categoria_nome FROM tarefas t LEFT JOIN categorias c ON t.categoria_id = c.id WHERE t.usuario_id = ? ORDER BY t.criado_em DESC');
    $stmt->bind_param('i', $usuario['id']);
    $stmt->execute();
    $tarefas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $conn->close();
    echo json_encode(['sucesso' => true, 'dados' => $tarefas]);

} elseif ($method === 'GET' && $id) {
    // GET /api/tarefas/{id}
    $usuario = autenticar();
    $conn = conectar();
    $stmt = $conn->prepare('SELECT * FROM tarefas WHERE id = ? AND usuario_id = ?');
    $stmt->bind_param('ii', $id, $usuario['id']);
    $stmt->execute();
    $tarefa = $stmt->get_result()->fetch_assoc();
    $conn->close();

    if (!$tarefa) {
        http_response_code(404);
        echo json_encode(['erro' => 'Tarefa não encontrada']);
        exit;
    }
    echo json_encode(['sucesso' => true, 'dados' => $tarefa]);

} elseif ($method === 'POST') {
    // POST /api/tarefas
    $usuario = autenticar();
    $body = json_decode(file_get_contents('php://input'), true);

    $titulo     = trim($body['titulo'] ?? '');
    $descricao  = trim($body['descricao'] ?? '');
    $prioridade = $body['prioridade'] ?? 'media';
    $status     = $body['status'] ?? 'pendente';
    $prazo      = $body['prazo'] ?? null;
    $cat_id     = intval($body['categoria_id'] ?? 0) ?: null;

    if (empty($titulo)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Título obrigatório']);
        exit;
    }

    $conn = conectar();
    $stmt = $conn->prepare('INSERT INTO tarefas (usuario_id, titulo, descricao, prioridade, status, prazo, categoria_id) VALUES (?,?,?,?,?,?,?)');
    $stmt->bind_param('isssssi', $usuario['id'], $titulo, $descricao, $prioridade, $status, $prazo, $cat_id);
    $stmt->execute();
    $novo_id = $conn->insert_id;
    $conn->close();

    http_response_code(201);
    echo json_encode(['sucesso' => true, 'id' => $novo_id]);

} elseif ($method === 'PUT' && $id) {
    // PUT /api/tarefas/{id}
    $usuario = autenticar();
    $body = json_decode(file_get_contents('php://input'), true);

    $titulo     = trim($body['titulo'] ?? '');
    $descricao  = trim($body['descricao'] ?? '');
    $prioridade = $body['prioridade'] ?? 'media';
    $status     = $body['status'] ?? 'pendente';
    $prazo      = $body['prazo'] ?? null;

    $conn = conectar();
    $stmt = $conn->prepare('UPDATE tarefas SET titulo=?, descricao=?, prioridade=?, status=?, prazo=? WHERE id=? AND usuario_id=?');
    $stmt->bind_param('sssssii', $titulo, $descricao, $prioridade, $status, $prazo, $id, $usuario['id']);
    $ok = $stmt->execute();
    $conn->close();

    echo json_encode(['sucesso' => $ok]);

} elseif ($method === 'DELETE' && $id) {
    // DELETE /api/tarefas/{id}
    $usuario = autenticar();
    $conn = conectar();
    $stmt = $conn->prepare('DELETE FROM tarefas WHERE id = ? AND usuario_id = ?');
    $stmt->bind_param('ii', $id, $usuario['id']);
    $ok = $stmt->execute();
    $conn->close();

    echo json_encode(['sucesso' => $ok]);

} else {
    http_response_code(404);
    echo json_encode(['erro' => 'Rota não encontrada']);
}