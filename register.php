<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

iniciarSessao();

if (usuarioLogado()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $conf  = $_POST['confirmar_senha'] ?? '';

    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = 'Preencha todos os campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter ao menos 6 caracteres.';
    } elseif ($senha !== $conf) {
        $erro = 'As senhas não coincidem.';
    } else {
        $conn = conectar();
        $check = $conn->prepare('SELECT id FROM usuarios WHERE email = ?');
        $check->bind_param('s', $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $erro = 'E-mail já cadastrado.';
        } else {
            $hash = password_hash($senha, PASSWORD_BCRYPT);
            $stmt = $conn->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $nome, $email, $hash);

            if ($stmt->execute()) {
                $sucesso = 'Conta criada! Redirecionando...';
                header('Refresh: 2; url=/gerenciador-tarefas/login.php');
            } else {
                $erro = 'Erro ao criar conta. Tente novamente.';
            }
            $stmt->close();
        }

        $check->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro — TarefasFlow</title>
    <link rel="stylesheet" href="/gerenciador-tarefas/assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <span class="logo-icon">✓</span>
            <h1>TarefasFlow</h1>
        </div>
        <p class="auth-subtitle">Crie sua conta</p>

        <?php if ($erro): ?>
            <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>

        <form method="POST" action="/gerenciador-tarefas/register.php" novalidate>
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" placeholder="Seu nome"
                       value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="seu@email.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" placeholder="Mínimo 6 caracteres" required>
            </div>
            <div class="form-group">
                <label for="confirmar_senha">Confirmar senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="••••••" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Criar conta</button>
        </form>

        <p class="auth-link">Já tem conta? <a href="/gerenciador-tarefas/login.php">Entrar</a></p>
    </div>
</body>
</html>
