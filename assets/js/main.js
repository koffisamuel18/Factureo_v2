// Menu hamburger simple et efficace
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== MENU HAMBURGER SIMPLE ===');
    
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    console.log('Bouton hamburger:', mobileMenuToggle);
    console.log('Menu navigation:', navLinks);
    
    if (mobileMenuToggle && navLinks) {
        console.log('✅ Éléments trouvés!');
        
        // Événement de clic sur le bouton hamburger
        mobileMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('🍔 CLIC SUR HAMBURGER!');
            
            // Toggle simple
            navLinks.classList.toggle('active');
            mobileMenuToggle.classList.toggle('active');
            
            console.log('Menu actif:', navLinks.classList.contains('active'));
        });
        
        // Fermer le menu quand on clique sur un lien
        const navLinksItems = navLinks.querySelectorAll('a');
        navLinksItems.forEach(link => {
            link.addEventListener('click', function() {
                console.log('🔗 Lien cliqué');
                navLinks.classList.remove('active');
                mobileMenuToggle.classList.remove('active');
            });
        });
        
        // Fermer le menu quand on clique en dehors
        document.addEventListener('click', function(event) {
            if (!mobileMenuToggle.contains(event.target) && !navLinks.contains(event.target)) {
                navLinks.classList.remove('active');
                mobileMenuToggle.classList.remove('active');
            }
        });
        
        console.log('✅ Menu hamburger configuré!');
        
    } else {
        console.error('❌ Problème: éléments non trouvés');
    }
});

// Amélioration de l'expérience mobile pour les tableaux
document.addEventListener('DOMContentLoaded', function() {
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        const container = table.closest('.table-container') || table.parentElement;
        if (container && window.innerWidth <= 768) {
            // Ajouter un indicateur de scroll pour les tableaux larges
            if (table.scrollWidth > container.clientWidth) {
                const indicator = document.createElement('div');
                indicator.className = 'table-scroll-indicator';
                indicator.textContent = '← Glissez →';
                container.style.position = 'relative';
                container.appendChild(indicator);
                
                // Masquer l'indicateur après quelques secondes
                setTimeout(() => {
                    indicator.style.opacity = '0';
                    setTimeout(() => indicator.remove(), 500);
                }, 3000);
            }
        }
    });
    
    // Amélioration des formulaires sur mobile
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        // Éviter le zoom sur iOS
        if (input.type !== 'file') {
            input.style.fontSize = '16px';
        }
        
        // Améliorer l'accessibilité tactile
        input.addEventListener('focus', function() {
            this.style.outline = '2px solid var(--success-green)';
            this.style.outlineOffset = '2px';
        });
        
        input.addEventListener('blur', function() {
            this.style.outline = '';
            this.style.outlineOffset = '';
        });
    });
    
    // Amélioration des boutons sur mobile
    const buttons = document.querySelectorAll('button, .btn, a[href]');
    buttons.forEach(button => {
        // Augmenter la zone de clic sur mobile
        if (window.innerWidth <= 768) {
            button.style.minHeight = '44px';
            button.style.minWidth = '44px';
        }
        
        // Feedback tactile
        button.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.95)';
        });
        
        button.addEventListener('touchend', function() {
            this.style.transform = '';
        });
    });
    
    // Optimisation du scroll sur mobile
    let scrollTimeout;
    document.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        document.body.classList.add('scrolling');
        
        scrollTimeout = setTimeout(function() {
            document.body.classList.remove('scrolling');
        }, 150);
    });
    
    // Amélioration de la navigation par ancres sur mobile
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                e.preventDefault();
                const offsetTop = targetElement.offsetTop - 80; // Ajuster pour la navbar fixe
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
});

// Détection de l'orientation mobile
function handleOrientationChange() {
    if (window.innerWidth <= 768) {
        document.body.classList.add('mobile-orientation');
    } else {
        document.body.classList.remove('mobile-orientation');
    }
}

window.addEventListener('resize', handleOrientationChange);
window.addEventListener('orientationchange', handleOrientationChange);
handleOrientationChange(); // Appel initial

// Amélioration de la performance sur mobile
if ('serviceWorker' in navigator && window.innerWidth <= 768) {
    // Optimisations spécifiques pour mobile
    document.addEventListener('DOMContentLoaded', function() {
        // Lazy loading des images
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    });
} 