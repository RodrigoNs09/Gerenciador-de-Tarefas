<?php
define('BASE_URL', '/gerenciador-tarefas');

function iniciarSessao() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function usuarioLogado() {
    iniciarSessao();
    return isset($_SESSION['usuario_id']);
}

function exigirLogin() {
    if (!usuarioLogado()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function sessaoUsuario() {
    iniciarSessao();
    return [
        'id'   => $_SESSION['usuario_id'] ?? null,
        'nome' => $_SESSION['usuario_nome'] ?? null,
    ];
}

function logout() {
    iniciarSessao();
    session_destroy();
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}
