document.addEventListener('DOMContentLoaded', () => {
    const text = document.querySelector('.huge-404');
    const illustration = document.querySelector('.book-wrapper');
    const particles = document.querySelectorAll('.particle');
    const message = document.querySelector('.message');
    const actions = document.querySelector('.actions');
    const buttons = document.querySelectorAll('.btn');

    if (!window.gsap) return;

    const timeline = gsap.timeline();
    timeline.fromTo(text, { opacity: 0, scale: 0.97 }, {
        opacity: 1, scale: 1, duration: 0.55, ease: 'power2.out'
    });
    timeline.fromTo(illustration, { opacity: 0, y: 18 }, {
        opacity: 1, y: 0, duration: 0.45, ease: 'power2.out'
    }, '-=0.3');
    timeline.fromTo([message, actions], { opacity: 0, y: 10 }, {
        opacity: 1, y: 0, duration: 0.35, stagger: 0.08, ease: 'power2.out'
    }, '-=0.2');
    timeline.fromTo(particles, { opacity: 0, scale: 0.7 }, {
        opacity: 0.35, scale: 1, duration: 0.35, stagger: 0.04, ease: 'power1.out'
    }, 0);

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        gsap.globalTimeline.timeScale(4);
        return;
    }

    const touch = window.matchMedia('(hover: none)').matches;
    if (touch) {
        gsap.to(illustration, {
            y: '-=5', duration: 3, yoyo: true, repeat: -1, ease: 'sine.inOut'
        });
    } else {
        window.addEventListener('mousemove', (event) => {
            const x = (event.clientX / window.innerWidth - 0.5) * 2;
            const y = (event.clientY / window.innerHeight - 0.5) * 2;
            gsap.to(text, { x: x * -12, y: y * -12, duration: 0.6, ease: 'power2.out' });
            gsap.to(illustration, { x: x * 15, y: y * 12, duration: 0.6, ease: 'power2.out' });
        }, { passive: true });
    }

    buttons.forEach((button) => {
        button.addEventListener('mouseenter', () => {
            gsap.to(button, { scale: 1.03, y: -2, duration: 0.18, ease: 'power2.out' });
        });
        button.addEventListener('mouseleave', () => {
            gsap.to(button, { scale: 1, y: 0, duration: 0.18, ease: 'power2.out' });
        });
    });
});
