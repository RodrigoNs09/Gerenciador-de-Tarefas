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