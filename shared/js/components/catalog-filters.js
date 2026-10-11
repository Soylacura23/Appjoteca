(() => {
    const filterBar = document.querySelector('[data-catalog-filters]');
    if (!filterBar) return;

    const categorySelect = filterBar.querySelector('#filterCategory');
    const availabilitySelect = filterBar.querySelector('#filterAvailability');
    const orderSelect = filterBar.querySelector('#filterOrder');
    const applyButton = filterBar.querySelector('[data-apply-catalog-filters]');

    function getValues() {
        return {
            materia: categorySelect.value,
            availability: availabilitySelect.value,
            sort: orderSelect.value
        };
    }

    function setMaterias(materias) {
        const selected = categorySelect.value;
        categorySelect.replaceChildren(new Option('Todas las categorías', ''));

        materias.forEach((materia) => {
            categorySelect.add(new Option(materia.nombre, materia.id));
        });

        if ([...categorySelect.options].some((option) => option.value === selected)) {
            categorySelect.value = selected;
        }
    }

    applyButton.addEventListener('click', () => {
        document.dispatchEvent(new CustomEvent('catalog:applyFilters', {
            detail: getValues()
        }));
    });

    window.catalogFilters = { getValues, setMaterias };
})();
