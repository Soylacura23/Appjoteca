<section class="shared-catalog-filters" data-catalog-filters aria-label="Filtros del catálogo">
    <div class="catalog-filter-field">
        <label for="filterCategory">Categoría</label>
        <select id="filterCategory" class="catalog-filter-select">
            <option value="">Todas las categorías</option>
        </select>
    </div>
    <div class="catalog-filter-field">
        <label for="filterAvailability">Disponibilidad</label>
        <select id="filterAvailability" class="catalog-filter-select">
            <option value="">Cualquier estado</option>
            <option value="available">Disponible</option>
            <option value="borrowed">En préstamo</option>
            <option value="reserved">Reservado</option>
        </select>
    </div>
    <div class="catalog-filter-field">
        <label for="filterOrder">Ordenar por</label>
        <select id="filterOrder" class="catalog-filter-select">
            <option value="recommended">Más recomendados</option>
            <option value="recent">Agregados recientemente</option>
            <option value="borrowed">Más prestados</option>
        </select>
    </div>
    <div class="catalog-filter-actions">
        <button class="btn-filter" type="button" data-apply-catalog-filters>
            <span class="material-symbols-outlined">filter_list</span>
            Aplicar
        </button>
    </div>
</section>
