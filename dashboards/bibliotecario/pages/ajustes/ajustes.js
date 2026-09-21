document.addEventListener('DOMContentLoaded', () => {
    const inputEmail = document.getElementById('emailblocked');
    const inputPassword = document.getElementById('passwordblocked');
    const input = document.querySelectorAll('.infoinput');
    const editableGroups = document.querySelectorAll('.editable-input');
    const levers = document.querySelectorAll('.lever');

    editableGroups.forEach(group => {
        const input = group.querySelector('.infoinput');
        const btn = group.querySelector('.edit');
        const originalText = btn.innerText;

        btn.addEventListener('click', () => {
            if (input.hasAttribute('readonly')) {
                
                input.removeAttribute('readonly');
                input.focus();

                btn.innerText = btn.getAttribute('data-active-text') || "Guardar";

            } else {

                input.setAttribute('readonly', true);
                btn.innerText = originalText;
                
                

                console.log(`Guardado: ${input.value}`);
            }
        });
    
    
    
    });
    levers.forEach(lever =>{
        lever.addEventListener('click', () =>{
            lever.classList.toggle('on');
            boton.classList.toggle('active')
        });
    });

    const profileImg = document.querySelector('#profile-button-topbar img');
    const actualProfileContainer = document.querySelector('#image img');

    if (profileImg && actualProfileContainer) {
        actualProfileContainer.src = profileImg.src;
        actualProfileContainer.alt = profileImg.alt;

    }




});

