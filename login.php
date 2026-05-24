<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

iniciarSessao();

if (usuarioLogado()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = 'Preencha todos os campos.';
    } else {
        $conn = conectar();
        $stmt = $conn->prepare('SELECT id, nome, senha FROM usuarios WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id']   = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        } else {
            $erro = 'E-mail ou senha incorretos.';
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Gerenciador de Tarefas</title>
    <link rel="stylesheet" href="/gerenciador-tarefas/assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <span class="logo-icon">✓</span>
            <h1>TarefasFlow</h1>
        </div>
        <p class="auth-subtitle">Entre na sua conta</p>

        <?php if ($erro): ?>
            <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="POST" action="/gerenciador-tarefas/login.php" novalidate>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="dev@teste.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" placeholder="••••••" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Entrar</button>
        </form>

        <p class="auth-link">Não tem conta? <a href="/gerenciador-tarefas/register.php">Cadastre-se</a></p>
        <p class="auth-hint">Teste: dev@teste.com / 123456</p>
    </div>
</body>
</html>
