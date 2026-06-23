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
    // --- Contact Form Submission (PHP Mail & WhatsApp Redirect) ---
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const submitBtn = contactForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            
            // Disable button and show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Sending Enquiry... <i class="fa-solid fa-spinner fa-spin" style="margin-left: 6px;"></i>';
            
            const name = document.getElementById('name').value;
            const company = document.getElementById('company').value || 'N/A';
            const phone = document.getElementById('phone').value;
            const email = document.getElementById('email').value;
            const service = document.getElementById('service').value || 'General Enquiry';
            const message = document.getElementById('message').value || 'N/A';
            
            // Send AJAX to PHP Mail Server
            fetch('php/send_mail.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    company: company,
                    phone: phone,
                    email: email,
                    service: service,
                    message: message
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Server returned error status');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    // Update email display inside success modal
                    if (clientEmailDisplay) {
                        clientEmailDisplay.textContent = email;
                    }
                    
                    // Show modal and trigger sparkles
                    if (successModal) {
                        successModal.classList.add('is-active');
                        triggerSparkles();
                    }
                    
                    // Format WhatsApp redirect text
                    const text = `*New Enquiry from Website*\n\n` +
                                 `*Full Name:* ${name}\n` +
                                 `*Company:* ${company}\n` +
                                 `*Phone:* ${phone}\n` +
                                 `*Email:* ${email}\n` +
                                 `*Service Interest:* ${service}\n` +
                                 `*Message:* ${message}`;
                    
                    pendingWhatsAppUrl = `https://wa.me/919422323128?text=${encodeURIComponent(text)}`;
                    
                    // Reset form
                    contactForm.reset();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Mail Error:', error);
                
                // For fail scenario, directly prompt to redirect
                const confirmWhatsApp = confirm('We had trouble sending the email. Would you like to send your enquiry directly via WhatsApp?');
                if (confirmWhatsApp) {
                    const text = `*New Enquiry from Website*\n\n` +
                                 `*Full Name:* ${name}\n` +
                                 `*Company:* ${company}\n` +
                                 `*Phone:* ${phone}\n` +
                                 `*Email:* ${email}\n` +
                                 `*Service Interest:* ${service}\n` +
                                 `*Message:* ${message}`;
                    
                    const whatsappUrl = `https://wa.me/919422323128?text=${encodeURIComponent(text)}`;
                    window.open(whatsappUrl, '_blank');
                }
            })
            .finally(() => {
                // Restore button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    }

    // --- Success Modal & Sparkles Logic ---
    const successModal = document.getElementById('successModal');
    const modalCloseBtn = document.getElementById('modalCloseBtn');
    const modalOkBtn = document.getElementById('modalOkBtn');
    const clientEmailDisplay = document.getElementById('clientEmailDisplay');
    let pendingWhatsAppUrl = '';

    function closeSuccessModal(shouldRedirect = false) {
        if (successModal) {
            successModal.classList.remove('is-active');
            if (shouldRedirect && pendingWhatsAppUrl) {
                window.open(pendingWhatsAppUrl, '_blank');
            }
            pendingWhatsAppUrl = '';
        }
    }

    if (modalCloseBtn) {
        modalCloseBtn.addEventListener('click', () => closeSuccessModal(false));
    }
    if (modalOkBtn) {
        modalOkBtn.addEventListener('click', () => closeSuccessModal(true));
    }
    if (successModal) {
        successModal.addEventListener('click', (e) => {
            if (e.target === successModal) {
                closeSuccessModal(false);
            }
        });
    }

    function triggerSparkles() {
        if (!successModal) return;
        const colors = ['#FFF0C4', '#8C1007', '#33e6ff', '#ff33e6', '#ffe633'];
        const characters = ['✨', '🌟', '⭐', '🔸', '✨'];
        
        for (let i = 0; i < 45; i++) {
            const sparkle = document.createElement('div');
            sparkle.className = 'sparkle-particle';
            
            sparkle.textContent = characters[Math.floor(Math.random() * characters.length)];
            sparkle.style.left = Math.random() * 100 + 'vw';
            
            const size = Math.random() * 15 + 15; // 15px to 30px
            sparkle.style.fontSize = size + 'px';
            
            const delay = Math.random() * 1.5;
            const duration = Math.random() * 2 + 2; // 2s to 4s
            sparkle.style.animationDelay = delay + 's';
            sparkle.style.animationDuration = duration + 's';
            
            if (Math.random() > 0.5) {
                sparkle.style.color = colors[Math.floor(Math.random() * colors.length)];
            }
            
            successModal.appendChild(sparkle);
            
            // Remove after animation finishes
            setTimeout(() => {
                sparkle.remove();
            }, (delay + duration) * 1000);
        }
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

