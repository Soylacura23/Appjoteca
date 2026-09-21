(() => {
    const PDF_URL = '../../shared/docs/manual-appjoteca.pdf';

    // Elementos
    const canvas = document.getElementById('pdfCanvas');
    const ctx = canvas.getContext('2d');
    const loading = document.getElementById('pdfLoading');
    const currentPageEl = document.getElementById('currentPage');
    const totalPagesEl = document.getElementById('totalPages');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const downloadLink = document.getElementById('downloadPdf');

    let pdfDoc = null;
    let pageNum = 1;
    let rendering = false;
    let pendingPage = null;

    pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    downloadLink.href = PDF_URL;

    // Renderizar página
    const renderPage = async (num) => {
        if (rendering) {
            pendingPage = num;
            return;
        }
        rendering = true;

        try {
            const page = await pdfDoc.getPage(num);

            // Escalar al ancho del contenedor
            const containerWidth = canvas.parentElement.clientWidth - 48;
            const baseViewport = page.getViewport({ scale: 1 });
            const scale = containerWidth / baseViewport.width;
            const viewport = page.getViewport({ scale });

            // Ajustar tamaño del canvas (con DPR para nitidez)
            const dpr = window.devicePixelRatio || 1;
            canvas.width = viewport.width * dpr;
            canvas.height = viewport.height * dpr;
            canvas.style.width = `${viewport.width}px`;
            canvas.style.height = `${viewport.height}px`;

            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

            await page.render({
                canvasContext: ctx,
                viewport
            }).promise;

            currentPageEl.textContent = num;
            prevBtn.disabled = num <= 1;
            nextBtn.disabled = num >= pdfDoc.numPages;
            loading.classList.add('hidden');
        } catch (err) {
            console.error('Error al renderizar la página:', err);
        } finally {
            rendering = false;
            if (pendingPage !== null) {
                const next = pendingPage;
                pendingPage = null;
                renderPage(next);
            }
        }
    };

    // Cargar PDF
    const loadPdf = async () => {
        try {
            pdfDoc = await pdfjsLib.getDocument(PDF_URL).promise;
            totalPagesEl.textContent = pdfDoc.numPages;
            await renderPage(pageNum);
        } catch (err) {
            console.error('Error al cargar el PDF:', err);
            loading.innerHTML = `
                <span class="material-symbols-outlined">error</span>
                <p>No se pudo cargar el manual.</p>
            `;
        }
    };

    // Navegación
    prevBtn.addEventListener('click', () => {
        if (pageNum > 1) {
            pageNum--;
            renderPage(pageNum);
        }
    });

    nextBtn.addEventListener('click', () => {
        if (pageNum < pdfDoc.numPages) {
            pageNum++;
            renderPage(pageNum);
        }
    });

    // Re-render al cambiar tamaño (con debounce)
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (pdfDoc) renderPage(pageNum);
        }, 250);
    });

    // Inicializar
    loadPdf();
})();