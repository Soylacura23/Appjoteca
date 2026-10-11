window.alerta = function(icon, title, text) {
    return Swal.fire({
        icon: icon,
        title: title,
        text: text,
        background: '#121212',
        color: '#e2e2e2',
        confirmButtonColor: '#f2ca50'
    });
};
