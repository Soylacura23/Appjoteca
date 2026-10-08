document.addEventListener("DOMContentLoaded", () => {
    // Referencias del DOM
    const text404 = document.querySelector('.huge-404');
    const book = document.querySelector('.book-wrapper');
    const particles = document.querySelectorAll('.particle');
    const message = document.querySelector('.message');
    const buttons = document.querySelectorAll('.btn');
   
    // 1. Animación de Entrada (Timeline)
    const tl = gsap.timeline();

    tl.fromTo(text404, 
        { opacity: 0, scale: 0.95 },
        { opacity: 1, scale: 1, duration: 1.8, ease: "power2.out" }
    )
    .fromTo(book,
        { opacity: 0, y: 40 },
        { opacity: 1, y: 0, duration: 1.2, ease: "power3.out" },
        "-=1.2" // Empieza un poco antes de que acabe el anterior
    )
    .fromTo(particles,
        { opacity: 0, scale: 0 },
        { opacity: 0.4, scale: 1, duration: 1, stagger: 0.1, ease: "back.out(1.5)" },
        "-=0.8"
    )
    .fromTo([message, buttons],
        { opacity: 0, y: 15 },
        { opacity: 1, y: 0, duration: 0.8, stagger: 0.15, ease: "power2.out" },
        "-=0.5"
    );

    // 2. Interacción de Mouse (Parallax) vs Animación Táctil
    const isTouchDevice = window.matchMedia("(hover: none)").matches;

    if (isTouchDevice) {

        gsap.to(book, { y: "-=15", rotation: 2, duration: 3, yoyo: true, repeat: -1, ease: "sine.inOut" });
        gsap.to(text404, { scale: 1.02, duration: 4, yoyo: true, repeat: -1, ease: "sine.inOut" });
    } else {

        window.addEventListener("mousemove", (e) => {

            const x = (e.clientX / window.innerWidth - 0.5) * 2;
            const y = (e.clientY / window.innerHeight - 0.5) * 2;

            gsap.to(text404, { x: x * -25, y: y * -25, duration: 1, ease: "power2.out" });
            
            gsap.to(book, { x: x * 35, y: y * 35, rotation: x * 6, duration: 1, ease: "power2.out" });

            particles.forEach((particle, index) => {
                const speed = (index % 3 + 1) * 12;
                gsap.to(particle, { x: x * speed, y: y * speed, duration: 1, ease: "power2.out" });
            });
        });
    }

    buttons.forEach(btn => {
        btn.addEventListener('mouseenter', () => {
            gsap.to(btn, { scale: 1.04, y: -2, duration: 0.3, ease: "power2.out" });
        });
        btn.addEventListener('mouseleave', () => {
            gsap.to(btn, { scale: 1, y: 0, duration: 0.3, ease: "power2.out" });
        });
    });
});