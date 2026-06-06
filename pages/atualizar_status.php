<?php
require_once '../config/database.php';
require_once '../config/auth.php';

exigirLogin();
$usuario = sessaoUsuario();

$dados = json_decode(file_get_contents('php://input'), true);
$id     = intval($dados['id'] ?? 0);
$status = $dados['status'] ?? '';

$permitidos = ['pendente', 'em_andamento', 'concluida'];

if (!$id || !in_array($status, $permitidos)) {
    echo json_encode(['sucesso' => false]);
    exit;
}

$conn = conectar();
$stmt = $conn->prepare('UPDATE tarefas SET status = ? WHERE id = ? AND usuario_id = ?');
$stmt->bind_param('sii', $status, $id, $usuario['id']);
$ok = $stmt->execute();
$conn->close();

echo json_encode(['sucesso' => $ok]);