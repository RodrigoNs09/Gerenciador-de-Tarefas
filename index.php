<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

exigirLogin();
$usuario = sessaoUsuario();
$conn = conectar();

// Filtros
$status    = $_GET['status'] ?? '';
$prioridade = $_GET['prioridade'] ?? '';
$categoria_id = intval($_GET['categoria'] ?? 0);

// Buscar categorias do usuário
$cats = $conn->prepare('SELECT * FROM categorias WHERE usuario_id = ? ORDER BY nome');
$cats->bind_param('i', $usuario['id']);
$cats->execute();
$categorias = $cats->get_result()->fetch_all(MYSQLI_ASSOC);

// Montar query com filtros
$sql = 'SELECT t.*, c.nome AS categoria_nome, c.cor AS categoria_cor
        FROM tarefas t
        LEFT JOIN categorias c ON t.categoria_id = c.id
        WHERE t.usuario_id = ?';
$params = [$usuario['id']];
$types  = 'i';

if ($status) {
    $sql .= ' AND t.status = ?';
    $params[] = $status;
    $types .= 's';
}
if ($prioridade) {
    $sql .= ' AND t.prioridade = ?';
    $params[] = $prioridade;
    $types .= 's';
}
if ($categoria_id) {
    $sql .= ' AND t.categoria_id = ?';
    $params[] = $categoria_id;
    $types .= 'i';
}

$sql .= ' ORDER BY FIELD(t.prioridade,"alta","media","baixa"), t.prazo ASC';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$tarefas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Estatísticas rápidas
$stats = $conn->prepare('SELECT
    COUNT(*) AS total,
    SUM(status = "pendente") AS pendentes,
    SUM(status = "em_andamento") AS andamento,
    SUM(status = "concluida") AS concluidas
    FROM tarefas WHERE usuario_id = ?');
$stats->bind_param('i', $usuario['id']);
$stats->execute();
$totais = $stats->get_result()->fetch_assoc();

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — TarefasFlow</title>
    <link rel="stylesheet" href="/gerenciador-tarefas/assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <span class="logo-icon">✓</span> TarefasFlow
        </div>
        <div class="nav-user">
            <span>Olá, <?= htmlspecialchars($usuario['nome']) ?></span>
            <button class="btn-dark-toggle" id="toggleDark" onclick="alternarTema()">☾</button>
<a href="/gerenciador-tarefas/logout.php" class="btn btn-outline btn-sm">Sair</a>
        </div>
    </nav>

    <main class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-number"><?= $totais['total'] ?></span>
                <span class="stat-label">Total</span>
            </div>
            <div class="stat-card stat-pending">
                <span class="stat-number"><?= $totais['pendentes'] ?></span>
                <span class="stat-label">Pendentes</span>
            </div>
            <div class="stat-card stat-progress">
                <span class="stat-number"><?= $totais['andamento'] ?></span>
                <span class="stat-label">Em andamento</span>
            </div>
            <div class="stat-card stat-done">
                <span class="stat-number"><?= $totais['concluidas'] ?></span>
                <span class="stat-label">Concluídas</span>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <form method="GET" class="filters">
                <select name="status" onchange="this.form.submit()">
                    <option value="">Todos os status</option>
                    <option value="pendente"     <?= $status === 'pendente'     ? 'selected' : '' ?>>Pendente</option>
                    <option value="em_andamento" <?= $status === 'em_andamento' ? 'selected' : '' ?>>Em andamento</option>
                    <option value="concluida"    <?= $status === 'concluida'    ? 'selected' : '' ?>>Concluída</option>
                </select>
                <select name="prioridade" onchange="this.form.submit()">
                    <option value="">Todas as prioridades</option>
                    <option value="alta"  <?= $prioridade === 'alta'  ? 'selected' : '' ?>>Alta</option>
                    <option value="media" <?= $prioridade === 'media' ? 'selected' : '' ?>>Média</option>
                    <option value="baixa" <?= $prioridade === 'baixa' ? 'selected' : '' ?>>Baixa</option>
                </select>
                <select name="categoria" onchange="this.form.submit()">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $categoria_id === $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($status || $prioridade || $categoria_id): ?>
                    <a href="/gerenciador-tarefas/index.php" class="btn btn-outline btn-sm">Limpar</a>
                <?php endif; ?>
            </form>
            <a href="/gerenciador-tarefas/pages/tarefa_form.php" class="btn btn-primary">+ Nova tarefa</a>
        </div>

        <!-- Lista de tarefas -->
        <?php if (empty($tarefas)): ?>
            <div class="empty-state">
                <p>Nenhuma tarefa encontrada.</p>
                <a href="/gerenciador-tarefas/pages/tarefa_form.php" class="btn btn-primary">Criar primeira tarefa</a>
            </div>
        <?php else: ?>
            <div class="task-list">
                <?php foreach ($tarefas as $tarefa): ?>
                    <div class="task-card priority-<?= $tarefa['prioridade'] ?> <?= $tarefa['status'] === 'concluida' ? 'task-done' : '' ?>">
                        <div class="task-main">
                            <div class="task-header">
                                <span class="task-title"><?= htmlspecialchars($tarefa['titulo']) ?></span>
                                <div class="task-badges">
                                    <span class="badge badge-<?= $tarefa['prioridade'] ?>"><?= ucfirst($tarefa['prioridade']) ?></span>
                                    <span class="badge badge-status-<?= $tarefa['status'] ?>">
                                        <?= ['pendente'=>'Pendente','em_andamento'=>'Em andamento','concluida'=>'Concluída'][$tarefa['status']] ?>
                                    </span>
                                    <?php if ($tarefa['categoria_nome']): ?>
                                        <span class="badge" style="background:<?= htmlspecialchars($tarefa['categoria_cor']) ?>22;color:<?= htmlspecialchars($tarefa['categoria_cor']) ?>">
                                            <?= htmlspecialchars($tarefa['categoria_nome']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($tarefa['descricao']): ?>
                                <p class="task-desc"><?= htmlspecialchars($tarefa['descricao']) ?></p>
                            <?php endif; ?>
                            <?php if ($tarefa['prazo']): ?>
                                <span class="task-prazo">📅 <?= date('d/m/Y', strtotime($tarefa['prazo'])) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="task-actions">
                            <a href="/gerenciador-tarefas/pages/tarefa_form.php?id=<?= $tarefa['id'] ?>" class="btn btn-outline btn-sm">Editar</a>
                            <a href="/gerenciador-tarefas/pages/tarefa_delete.php?id=<?= $tarefa['id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Excluir esta tarefa?')">Excluir</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script src="/gerenciador-tarefas/assets/js/main.js"></script>
</body>
</html>
