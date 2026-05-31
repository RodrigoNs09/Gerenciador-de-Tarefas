<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

exigirLogin();
$usuario = sessaoUsuario();
$conn    = conectar();

$id     = intval($_GET['id'] ?? 0);
$tarefa = null;
$erro   = '';

// Buscar categorias
$cats = $conn->prepare('SELECT * FROM categorias WHERE usuario_id = ? ORDER BY nome');
$cats->bind_param('i', $usuario['id']);
$cats->execute();
$categorias = $cats->get_result()->fetch_all(MYSQLI_ASSOC);

// Se edição, carregar tarefa existente
if ($id) {
    $stmt = $conn->prepare('SELECT * FROM tarefas WHERE id = ? AND usuario_id = ?');
    $stmt->bind_param('ii', $id, $usuario['id']);
    $stmt->execute();
    $tarefa = $stmt->get_result()->fetch_assoc();

    if (!$tarefa) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo      = trim($_POST['titulo'] ?? '');
    $descricao   = trim($_POST['descricao'] ?? '');
    $prioridade  = $_POST['prioridade'] ?? 'media';
    $status      = $_POST['status'] ?? 'pendente';
    $prazo       = $_POST['prazo'] ?: null;
    $cat_id      = intval($_POST['categoria_id'] ?? 0) ?: null;

    if (empty($titulo)) {
        $erro = 'O título é obrigatório.';
    } else {
        if ($id) {
            $stmt = $conn->prepare('UPDATE tarefas SET titulo=?, descricao=?, prioridade=?, status=?, prazo=?, categoria_id=? WHERE id=? AND usuario_id=?');
            $stmt->bind_param('ssssssii', $titulo, $descricao, $prioridade, $status, $prazo, $cat_id, $id, $usuario['id']);
        } else {
            $stmt = $conn->prepare('INSERT INTO tarefas (usuario_id, titulo, descricao, prioridade, status, prazo, categoria_id) VALUES (?,?,?,?,?,?,?)');
            $stmt->bind_param('isssssi', $usuario['id'], $titulo, $descricao, $prioridade, $status, $prazo, $cat_id);
        }

        if ($stmt->execute()) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        } else {
            $erro = 'Erro ao salvar. Tente novamente.';
        }
    }
}

$conn->close();
$isEdicao = (bool) $tarefa;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdicao ? 'Editar' : 'Nova' ?> Tarefa — TarefasFlow</title>
    <link rel="stylesheet" href="/gerenciador-tarefas/assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><span class="logo-icon">✓</span> TarefasFlow</div>
       <button class="btn-dark-toggle" id="toggleDark" onclick="alternarTema()">Escuro</button>
<a href="/gerenciador-tarefas/index.php" class="btn btn-outline btn-sm">← Voltar</a>
    </nav>

    <main class="container container-sm">
        <h2><?= $isEdicao ? 'Editar tarefa' : 'Nova tarefa' ?></h2>

        <?php if ($erro): ?>
            <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-card">
            <div class="form-group">
                <label for="titulo">Título *</label>
                <input type="text" id="titulo" name="titulo" required
                       value="<?= htmlspecialchars($tarefa['titulo'] ?? $_POST['titulo'] ?? '') ?>"
                       placeholder="Ex: Estudar React.js">
            </div>

            <div class="form-group">
                <label for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao" rows="3"
                          placeholder="Detalhes opcionais..."><?= htmlspecialchars($tarefa['descricao'] ?? $_POST['descricao'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="prioridade">Prioridade</label>
                    <select id="prioridade" name="prioridade">
                        <?php foreach (['baixa','media','alta'] as $p): ?>
                            <option value="<?= $p ?>" <?= ($tarefa['prioridade'] ?? 'media') === $p ? 'selected' : '' ?>>
                                <?= ucfirst($p) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="pendente"     <?= ($tarefa['status'] ?? '') === 'pendente'     ? 'selected' : '' ?>>Pendente</option>
                        <option value="em_andamento" <?= ($tarefa['status'] ?? '') === 'em_andamento' ? 'selected' : '' ?>>Em andamento</option>
                        <option value="concluida"    <?= ($tarefa['status'] ?? '') === 'concluida'    ? 'selected' : '' ?>>Concluída</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="prazo">Prazo</label>
                    <input type="date" id="prazo" name="prazo"
                           value="<?= htmlspecialchars($tarefa['prazo'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="categoria_id">Categoria</label>
                    <select id="categoria_id" name="categoria_id">
                        <option value="">Sem categoria</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                <?= ($tarefa['categoria_id'] ?? null) == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <a href="/gerenciador-tarefas/index.php" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <?= $isEdicao ? 'Salvar alterações' : 'Criar tarefa' ?>
                </button>
            </div>
        </form>
    </main>
    <script src="/gerenciador-tarefas/assets/js/main.js"></script>
</body>
</html>
