const hamburguer = document.querySelector('#menu-activar');
const overlay = document.querySelector('#overlay')
const body = document.body;
const sidebar = document.querySelector('.sidebar');
const topbar = document.querySelector('.topbar');

if (hamburguer) {

hamburguer.addEventListener('click', () =>  {
    overlay.classList.toggle('show');
    sidebar.classList.toggle('open');
    topbar.classList.toggle('above');
    if (sidebar.classList.contains('open') && overlay.classList.contains('show')) {
        document.body.style.overflow = 'hidden';
    }
    else {
        document.body.style.overflow =''
    }
}
);
}

const form = document.querySelector('#search-form')
if (form) {
form.addEventListener('submit', (evt) => {
    evt.preventDefault();

    const search = evt.target.query.value;
    console.log("Pronto podrás buscar", search);
}
)
}

const profileImage = '../images/silueta.png';
function createProfileButton(containerId, sizeImage = 'btn-md'){
    const container = document.querySelector('#' + containerId);
    if (container) {
        container.innerHTML = `
            <button class="profile-button ${sizeImage}" aria-label="Perfil" type="button">
                <img src="${profileImage}" alt="Foto de perfil" class="img-profile">
            </button>
        `;
    }
} 
createProfileButton('profile-button-topbar', 'btn-md');
createProfileButton('profile-button-menu', 'btn-lg');

const menu_off_canva = document.querySelector('.menu-off-canva');
const profileButton = document.querySelector('.profile-button')
if (menu_off_canva){
    profileButton.addEventListener('click', () => {
        overlay.classList.add('show');
        menu_off_canva.classList.add('open');
        if (menu_off_canva.classList.contains('open')){
            document.body.style.overflow = 'hidden';
        }
        else {
            document.body.style.overflow = '';
        }

    }

)
}
const goBackButton = document.querySelector('.arrow-back');
goBackButton.addEventListener('click', ()=>{
    menu_off_canva.classList.remove('open');
    overlay.classList.remove('show');
    document.body.style.overflow = '';
})

const configBtn = document.querySelector('.config');
configBtn.addEventListener('click', () =>{
    window.location.href = "../pages/ajustes/ajustes.php";

});

const signOut = document.querySelector('.signout');
signOut.addEventListener('click', () => {
    window.location.href = "../../../backend/auth/logout.php";
});







