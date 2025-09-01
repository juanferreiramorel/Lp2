function openGlobalDeleteModal(actionUrl, message = '¿Estás seguro de que deseas eliminar este elemento?') {
    document.getElementById('globalConfirmDeleteForm').setAttribute('action', actionUrl);
    document.getElementById('confirmDeleteMessage').textContent = message;

    const modalEl = document.getElementById('globalConfirmDeleteModal');

    if (typeof bootstrap !== 'undefined') {
        // Bootstrap 5
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    } else {
        // Bootstrap 4 sin jQuery
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        modalEl.setAttribute('aria-modal', 'true');
        modalEl.removeAttribute('aria-hidden');

        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'manualBackdrop';
        document.body.appendChild(backdrop);

        const closeBtns = modalEl.querySelectorAll('[data-dismiss="modal"]');
        closeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                modalEl.classList.remove('show');
                modalEl.style.display = 'none';
                modalEl.removeAttribute('aria-modal');
                modalEl.setAttribute('aria-hidden', 'true');

                const backdrop = document.getElementById('manualBackdrop');
                if (backdrop) backdrop.remove();
            });
        });
    }
}

function openGlobalConfirmModal(actionUrl, titulo, color, message = '¿Estás seguro de que deseas realizar la acción?', metodo='DELETE') {
    document.getElementById('globalConfirmModal').setAttribute('action', actionUrl);
    document.getElementById('confirmMessage').textContent = message;
    document.getElementById('confirmModalHeader').className = `modal-header bg-${color} text-white`;
    document.getElementById('confirmModalLabel').textContent = titulo;
    document.getElementById('globalConfirmForm').setAttribute('method', metodo);

    const modalEl = document.getElementById('globalConfirmModal');

    if (typeof bootstrap !== 'undefined') {
        // Bootstrap 5
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    } else {
        // Bootstrap 4 sin jQuery
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        modalEl.setAttribute('aria-modal', 'true');
        modalEl.removeAttribute('aria-hidden');

        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'manualBackdrop';
        document.body.appendChild(backdrop);

        const closeBtns = modalEl.querySelectorAll('[data-dismiss="modal"]');
        closeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                modalEl.classList.remove('show');
                modalEl.style.display = 'none';
                modalEl.removeAttribute('aria-modal');
                modalEl.setAttribute('aria-hidden', 'true');

                const backdrop = document.getElementById('manualBackdrop');
                if (backdrop) backdrop.remove();
            });
        });
    }
}
