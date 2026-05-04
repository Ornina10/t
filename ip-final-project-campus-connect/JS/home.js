// home.js - ONLY home page specific functions
// (global.js handles navbar, auth, notifications, burger menu)

// ========== HOME PAGE SPECIFIC FUNCTIONS ==========

// Create floating particles for hero section
function createParticles() {
    const particleContainer = document.getElementById('heroParticles');
    if (!particleContainer) return;
    
    const particleCount = 20;
    
    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        
        const size = Math.random() * 6 + 2;
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        
        particle.style.left = `${Math.random() * 100}%`;
        particle.style.animationDelay = `${Math.random() * 15}s`;
        particle.style.animationDuration = `${Math.random() * 10 + 10}s`;
        particle.style.animationName = 'floatParticle';
        particle.style.animationTimingFunction = 'linear';
        particle.style.animationIterationCount = 'infinite';
        
        particleContainer.appendChild(particle);
    }
}

// Parallax scroll effect for hero
function initParallax() {
    const hero = document.querySelector('.hero');
    if (hero) {
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            hero.style.transform = `translate3d(0px, ${rate}px, 0px)`;
        });
    }
}

// Scroll indicator click handler
function initScrollIndicator() {
    const scrollIndicator = document.querySelector('.scroll-indicator');
    if (scrollIndicator) {
        scrollIndicator.addEventListener('click', function() {
            const features = document.querySelector('.features');
            if (features) {
                features.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
}

// ========== INITIALIZE HOME PAGE ==========
document.addEventListener('DOMContentLoaded', function() {
    // Home page specific features
    createParticles();
    initParallax();
    initScrollIndicator();
});