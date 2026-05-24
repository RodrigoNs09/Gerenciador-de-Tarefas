<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

exigirLogin();
$usuario = sessaoUsuario();

$id = intval($_GET['id'] ?? 0);

if ($id) {
    $conn = conectar();
    $stmt = $conn->prepare('DELETE FROM tarefas WHERE id = ? AND usuario_id = ?');
    $stmt->bind_param('ii', $id, $usuario['id']);
    $stmt->execute();
    $conn->close();
}

header('Location: ' . BASE_URL . '/index.php');
exit;
