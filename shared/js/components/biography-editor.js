document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('biographyInput');
    const actions = document.getElementById('biographyActions');
    const saveButton = document.getElementById('saveBiographyBtn');
    const cancelButton = document.getElementById('cancelBiographyBtn');
    const counter = document.getElementById('biographyCounter');
    if (!input || !actions || !saveButton || !cancelButton || !counter) return;

    let savedValue = input.value;
    const endpoint = input.dataset.endpoint;

    function updateEditor() {
        counter.textContent = input.value.length + ' / 500';
        actions.hidden = input.value === savedValue;
    }

    input.addEventListener('input', updateEditor);
    cancelButton.addEventListener('click', function () {
        input.value = savedValue;
        updateEditor();
    });

    saveButton.addEventListener('click', async function () {
        const formData = new FormData();
        formData.append('accion', 'update_biography');
        formData.append('biografia', input.value.trim());
        formData.append('csrf_token', window.getCSRFToken
            ? window.getCSRFToken()
            : (document.querySelector('meta[name="csrf-token"]')?.content || ''));
        saveButton.disabled = true;
        try {
            const response = await fetch(endpoint, { method: 'POST', body: formData });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'No se pudo guardar la biografía.');
            savedValue = input.value.trim();
            input.value = savedValue;
            updateEditor();
            Swal.fire({
                icon: 'success',
                title: 'Biografía guardada',
                text: data.message,
                background: '#121212',
                color: '#e2e2e2',
                confirmButtonColor: '#f2ca50'
            });
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'No se pudo guardar',
                text: error.message || 'Error de conexión.',
                background: '#121212',
                color: '#e2e2e2',
                confirmButtonColor: '#f2ca50'
            });
        } finally {
            saveButton.disabled = false;
        }
    });

    updateEditor();
});
