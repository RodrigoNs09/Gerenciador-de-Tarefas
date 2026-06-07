document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
        if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
});

document.querySelectorAll('.alert').forEach(alert => {
    alert.style.cursor = 'pointer';
    alert.title = 'Clique para fechar';
    alert.addEventListener('click', () => alert.remove());
});

function alternarTema() {
    const body = document.body;
    const btn = document.getElementById('toggleDark');
    body.classList.toggle('dark');

    if (body.classList.contains('dark')) {
        btn.textContent = 'Claro';
        localStorage.setItem('tema', 'dark');
    } else {
        btn.textContent = 'Escuro';
        localStorage.setItem('tema', 'light');
    }
}

const temaSalvo = localStorage.getItem('tema');
if (temaSalvo === 'dark') {
    document.body.classList.add('dark');
    const btn = document.getElementById('toggleDark');
    if (btn) btn.textContent = 'Claro';
}

// --- Kanban ---
let dragId = null;

function drag(event, id) {
    dragId = id;
}

function drop(event, novoStatus) {
    if (!dragId) return;

    fetch('/gerenciador-tarefas/pages/atualizar_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: dragId, status: novoStatus })
    })
    .then(r => r.json())
    .then(data => {
        if (data.sucesso) location.reload();
    });
}

function alternarVisao(visao) {
    const lista = document.querySelector('.task-list') || document.querySelector('.empty-state');
    const kanban = document.getElementById('kanban-view');
    const btnLista = document.getElementById('btnLista');
    const btnKanban = document.getElementById('btnKanban');

    if (visao === 'kanban') {
        if (lista) lista.style.display = 'none';
        kanban.style.display = 'block';
        btnKanban.classList.add('active');
        btnLista.classList.remove('active');
        localStorage.setItem('visao', 'kanban');
    } else {
        if (lista) lista.style.display = 'flex';
        kanban.style.display = 'none';
    }
}

// --- Badge no título da aba ---
const prazoBanner = document.getElementById('prazoBanner');
if (prazoBanner) {
    const qtd = document.querySelectorAll('.prazo-lista li').length;
    if (qtd > 0) {
        document.title = '(' + qtd + ') ' + document.title;
    }
}