<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

$body  = json_decode(file_get_contents('php://input'), true);
$email = trim($body['email'] ?? '');
$senha = $body['senha'] ?? '';

if (empty($email) || empty($senha)) {
    http_response_code(400);
    echo json_encode(['erro' => 'E-mail e senha obrigatórios']);
    exit;
}

$conn = conectar();
$stmt = $conn->prepare('SELECT id, nome, senha, email FROM usuarios WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$conn->close();

if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Credenciais inválidas']);
    exit;
}

$token = md5($usuario['id'] . $usuario['email']);

echo json_encode([
    'sucesso' => true,
    'token'   => $token,
    'usuario' => [
        'id'   => $usuario['id'],
        'nome' => $usuario['nome'],
    ]
]);