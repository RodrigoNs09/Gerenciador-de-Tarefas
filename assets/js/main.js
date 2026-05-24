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
