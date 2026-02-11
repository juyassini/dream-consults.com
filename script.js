// ==============================================================
// ANIMATIONS & INTERACTIONS FOR DREAM CONSULTS
// ==============================================================

document.addEventListener('DOMContentLoaded', () => {
    initScrollAnimations();
    initContactForm();
    initNavMenu();
});

// ==============================================================
// Scroll Animations - Fade in elements as they come into view
// ==============================================================
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Add fade-in class to elements that should animate
    const animateElements = document.querySelectorAll(
        'section, .service-card, .team-member, .contact-container'
    );

    animateElements.forEach(el => {
        el.classList.add('fade-in');
        observer.observe(el);
    });
}

// ==============================================================
// Contact Form Handling
// ==============================================================
function initContactForm() {
    const contactForm = document.getElementById('contactForm');
    const formFeedback = document.getElementById('formFeedback');

    if (!contactForm) return;

    contactForm.addEventListener('submit', (e) => {
        e.preventDefault();

        // Get form values
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const service = document.getElementById('service').value;
        const message = document.getElementById('message').value.trim();

        // Validation
        if (!name || !email || !service || !message) {
            showFormFeedback('Please fill in all required fields.', 'error');
            return;
        }

        if (!isValidEmail(email)) {
            showFormFeedback('Please enter a valid email address.', 'error');
            return;
        }

        // POST to backend API
        const payload = { name, email, phone, service, message };

        fetch('/contact.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.status === 'ok') {
                showFormFeedback('Thank you! Your message has been received. We\'ll get back to you within 24 hours.', 'success');
                contactForm.reset();
            } else {
                showFormFeedback((data && data.message) ? data.message : 'Submission failed. Please try again later.', 'error');
            }
            formFeedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        })
        .catch(err => {
            console.error('Submit error', err);
            // Fallback: save submission locally to retry later
            try {
                const pending = JSON.parse(localStorage.getItem('pendingSubmissions') || '[]');
                pending.push(payload);
                localStorage.setItem('pendingSubmissions', JSON.stringify(pending));
                showFormFeedback('Server unavailable — your message was saved and will be retried automatically.', 'success');
                contactForm.reset();
                formFeedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } catch (e) {
                showFormFeedback('Unable to contact server. Please try again later.', 'error');
            }
        });
    });

    function showFormFeedback(message, type) {
        formFeedback.textContent = message;
        formFeedback.className = `form-feedback ${type}`;
        
        // Auto-clear error messages after 5 seconds
        if (type === 'error') {
            setTimeout(() => {
                formFeedback.textContent = '';
                formFeedback.className = 'form-feedback';
            }, 5000);
        }
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
}

// ==============================================================
// Navigation Menu - Smooth scroll to sections
// ==============================================================
function initNavMenu() {
    const navLinks = document.querySelectorAll('nav a[href^="#"]');

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = link.getAttribute('href').substring(1);
            const targetSection = document.getElementById(targetId);

            if (targetSection) {
                targetSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
}

// ==============================================================
// Add fade-in animation styles dynamically
// ==============================================================
function addAnimationStyles() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            opacity: 0;
            animation: fadeIn 0.8s ease-out forwards;
        }

        .fade-in-visible {
            opacity: 1;
        }

        /* Add staggered animation for multiple elements */
        section:nth-of-type(1) { animation-delay: 0s; }
        section:nth-of-type(2) { animation-delay: 0.1s; }
        section:nth-of-type(3) { animation-delay: 0.2s; }
        
        .service-card {
            opacity: 0;
            animation: fadeIn 0.8s ease-out forwards;
        }
        
        .service-card:nth-child(1) { animation-delay: 0.1s; }
        .service-card:nth-child(2) { animation-delay: 0.3s; }
        .service-card:nth-child(3) { animation-delay: 0.5s; }

        .team-member {
            opacity: 0;
            animation: fadeIn 0.8s ease-out forwards;
        }

        .team-member:nth-child(1) { animation-delay: 0.1s; }
        .team-member:nth-child(2) { animation-delay: 0.3s; }
        .team-member:nth-child(3) { animation-delay: 0.5s; }
        .team-member:nth-child(4) { animation-delay: 0.7s; }

        /* Hover effects for interactive elements */
        a {
            position: relative;
            transition: color 0.3s ease;
        }

        .service-card {
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        /* Button hover effects */
        button, .cta-button {
            position: relative;
            overflow: hidden;
        }

        button::before, .cta-button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        button:active::before, .cta-button:active::before {
            width: 300px;
            height: 300px;
        }
    `;
    document.head.appendChild(style);
}

// Initialize animation styles
addAnimationStyles();

// Try resending any pending submissions saved locally when the page loads
async function resendPendingSubmissions() {
    const formFeedback = document.getElementById('formFeedback');
    const pendingRaw = localStorage.getItem('pendingSubmissions');
    if (!pendingRaw) return;
    let pending = [];
    try { pending = JSON.parse(pendingRaw); } catch (e) { return; }
    if (!pending.length) return;

    const remaining = [];
    for (const item of pending) {
        try {
            const res = await fetch('/contact.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(item)
            });
            const data = await res.json();
            if (!(data && data.status === 'ok')) {
                remaining.push(item);
            }
        } catch (e) {
            remaining.push(item);
        }
    }

    localStorage.setItem('pendingSubmissions', JSON.stringify(remaining));
    if (remaining.length < pending.length) {
        if (formFeedback) {
            formFeedback.textContent = 'Some pending messages were sent.';
            formFeedback.className = 'form-feedback success';
            setTimeout(() => { formFeedback.textContent = ''; formFeedback.className = 'form-feedback'; }, 5000);
        }
    }
}

// Attempt resend on load and every 60s
resendPendingSubmissions();
setInterval(resendPendingSubmissions, 60000);

// ==============================================================
// Parallax Scroll Effect (for header)
// ==============================================================
window.addEventListener('scroll', () => {
    const header = document.querySelector('header');
    const scrollPosition = window.scrollY;
    
    // Only apply parallax on larger screens
    if (window.innerWidth > 768) {
        header.style.transform = `translateY(${scrollPosition * 0.5}px)`;
    }
});

// ==============================================================
// Counter Animation (optional - for stats if added later)
// ==============================================================
function animateCounter(element, target, duration = 2000) {
    const increment = target / (duration / 16);
    let current = 0;

    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

// ==============================================================
// Utility: Check if element is in viewport
// ==============================================================
function isElementInViewport(el) {
    const rect = el.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}