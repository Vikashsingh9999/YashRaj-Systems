document.addEventListener('DOMContentLoaded', () => {

    // --- Header Scrolled Listener (Centering to Single Row Navbar collapse) ---
    const header = document.querySelector('header');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 40) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }, { passive: true });

    // --- Mobile Drawer ---
    const hamburgerBtn  = document.getElementById('hamburgerBtn');
    const mobileDrawer  = document.getElementById('mobileDrawer');
    const mobileBackdrop = document.getElementById('mobileBackdrop');
    const drawerClose   = document.getElementById('drawerClose');

    function openDrawer() {
        mobileDrawer.classList.add('is-open');
        mobileBackdrop.classList.add('is-open');
        hamburgerBtn.classList.add('is-open');
        mobileDrawer.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
        mobileDrawer.classList.remove('is-open');
        mobileBackdrop.classList.remove('is-open');
        hamburgerBtn.classList.remove('is-open');
        mobileDrawer.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    if (hamburgerBtn) hamburgerBtn.addEventListener('click', openDrawer);
    if (drawerClose)  drawerClose.addEventListener('click', closeDrawer);
    if (mobileBackdrop) mobileBackdrop.addEventListener('click', closeDrawer);

    // Close on any drawer link tap
    if (mobileDrawer) {
        mobileDrawer.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', closeDrawer);
        });
    }

    // --- Scroll Reveal: IntersectionObserver (replaces GSAP ScrollTrigger, no gaps) ---
    const revealEls = document.querySelectorAll(
        'section, .service-card, .about-feature-card, .director-panel, ' +
        '.contact-item, .contact-form-panel, .mosaic-img, ' +
        '.project-card, .testimonial-card'
    );

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08 });

    revealEls.forEach(el => {
        el.classList.add('reveal');
        observer.observe(el);
    });

    // --- Hero Entry Animation Trigger ---
    triggerHeroAnimation();


    function triggerHeroAnimation() {
        const heroEls = [
            document.querySelector('.subheading-cyber'),
            document.querySelector('.hero-txt h1'),
            document.querySelector('.hero-txt p'),
            document.querySelector('.hero-ctas'),
            document.querySelector('.hero-telemetry'),
            document.querySelector('.hero-graphics-static'),
        ];
        heroEls.forEach((el, i) => {
            if (!el) return;
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = `opacity 0.7s ease ${i * 0.12}s, transform 0.7s ease ${i * 0.12}s`;
            requestAnimationFrame(() => {
                setTimeout(() => {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, 80);
            });
        });
    }

    // --- Stats Counters ---
    const stats = document.querySelectorAll('.stat-counter');
    const statObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const el = entry.target;
            const target = parseInt(el.getAttribute('data-target'), 10);
            let current = 0;
            const step = Math.ceil(target / 60);
            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                el.textContent = current;
            }, 30);
            statObserver.unobserve(el);
        });
    }, { threshold: 0.5 });

    stats.forEach(s => statObserver.observe(s));

    // --- Interactive SCADA Telemetry monitor loop ---
    const gauges = document.querySelectorAll('.gauge-fill');
    if (gauges.length > 0) {
        setInterval(() => {
            gauges.forEach(gauge => {
                const valEl = gauge.parentElement.parentElement.querySelector('.gauge-val');
                if (!valEl) return;
                let val = parseInt(valEl.textContent, 10);
                val += Math.floor(Math.random() * 5) - 2;
                val = Math.max(0, Math.min(100, val));
                valEl.textContent = `${val}%`;
                const circumference = 2 * Math.PI * 40;
                gauge.style.strokeDashoffset = circumference - (val / 100) * circumference;
            });
        }, 2000);
    }
    // --- Defer Motherboard Video Background Loading ---
    window.addEventListener('load', () => {
        const heroVideo = document.getElementById('heroVideo');
        if (heroVideo) {
            const source = heroVideo.querySelector('source');
            if (source && source.getAttribute('data-src')) {
                source.src = source.getAttribute('data-src');
                heroVideo.load();
                heroVideo.play().catch(err => {
                    console.log("Hero background video autoplay prevented or failed:", err);
                });
            }
        }
    });
});

