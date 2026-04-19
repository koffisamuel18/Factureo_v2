<?php
session_start();
// Redirection supprimée : l'accueil s'affiche pour tout le monde
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factureo - Accueil</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="home-navbar">
        <div class="nav-logo">
            <a href="" style="display:flex;align-items:center;gap:0.7em;text-decoration:none;">
                <svg width="38" height="38" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="8" y="6" width="32" height="36" rx="6" fill="#fff" stroke="#1F2937" stroke-width="2"/>
                    <rect x="14" y="14" width="20" height="3" rx="1.5" fill="#10B981"/>
                    <rect x="14" y="22" width="20" height="3" rx="1.5" fill="#1F2937"/>
                    <rect x="14" y="30" width="12" height="3" rx="1.5" fill="#10B981"/>
                    <circle cx="36" cy="33" r="4" fill="#10B981" stroke="#1F2937" stroke-width="2"/>
                    <text x="36" y="36" text-anchor="middle" fill="#fff" font-size="2em" font-family="Segoe UI, Arial, sans-serif" font-weight="bold" dy=".3em">€</text>
                </svg>
                <span>Factureo</span>
            </a>
        </div>
        <button class="mobile-menu-toggle" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <div class="nav-links">
            <a href="#accueil">Accueil</a>
            <a href="#apropos">À propos</a>
            <a href="#services">Services</a>
            <a href="#benefices">Bénéfices</a>
            <a href="pages/contact.php" >Contact</a>
            <a href="pages/login.php" class="nav-btn">Connexion</a>
        </div>
    </nav>
    <div class="home-hero home-hero-flex" id="accueil" style="padding-top:5em;">
        <div class="hero-content">
            <div class="home-title">Factureo</div>
            <div class="home-desc">
                <span class="hero-slogan">La facturation professionnelle, simple et rapide.</span><br>
                Solution moderne de gestion de facturation pour entrepreneurs, PME et indépendants.<br>
                Créez, gérez et suivez vos clients, produits, devis et factures en toute simplicité.
            </div>
            <a href="#services" class="home-btn">Découvrir les services</a>
        </div>
        <div class="hero-illu">
            <!-- Illustration SVG facture stylisée -->
            <svg width="260" height="180" viewBox="0 0 260 180" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="30" y="20" width="200" height="140" rx="16" fill="#fff" stroke="#1F2937" stroke-width="3"/>
                <rect x="50" y="50" width="120" height="12" rx="6" fill="#10B981"/>
                <rect x="50" y="80" width="160" height="10" rx="5" fill="#F3F4F6"/>
                <rect x="50" y="100" width="100" height="10" rx="5" fill="#1F2937"/>
                <rect x="50" y="120" width="80" height="10" rx="5" fill="#10B981"/>
                <circle cx="200" cy="130" r="14" fill="#10B981" stroke="#1F2937" stroke-width="2"/>
                <text x="200" y="137" text-anchor="middle" fill="#fff" font-size="1.5em" font-family="Segoe UI, Arial, sans-serif" font-weight="bold">€</text>
            </svg>
        </div>
    </div>
    <section class="home-section" id="apropos">
        <h2>À propos</h2>
        <p class="section-desc">
            Factureo est une solution SaaS de facturation pensée pour les entrepreneurs, PME et indépendants.<br>
            Notre mission : simplifier la gestion commerciale et la rendre accessible à tous, sans compromis sur la modernité et la sécurité.
        </p>
        <div class="apropos-points">
            <div class="apropos-point"><span class="apropos-icon">🔒</span> Sécurité & confidentialité</div>
            <div class="apropos-point"><span class="apropos-icon">⚡</span> Rapidité & simplicité</div>
            <div class="apropos-point"><span class="apropos-icon">💡</span> Support & accompagnement</div>
        </div>
    </section>
    <section class="home-section" id="services">
        <h2>Nos services</h2>
        <div class="services-cards">
            <div class="service-card">
                <div class="service-icon">
                    <svg width="36" height="36" fill="none" viewBox="0 0 36 36"><circle cx="18" cy="12" r="6" fill="#1F2937"/><rect x="6" y="22" width="24" height="10" rx="5" fill="#10B981"/></svg>
                </div>
                <h3>Gestion des clients</h3>
                <p>Ajoutez, modifiez et suivez vos clients en toute simplicité.</p>
                
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <svg width="36" height="36" fill="none" viewBox="0 0 36 36"><rect x="8" y="6" width="20" height="24" rx="4" fill="#1F2937"/><rect x="12" y="12" width="12" height="2" fill="#10B981"/><rect x="12" y="18" width="12" height="2" fill="#10B981"/></svg>
                </div>
                <h3>Facturation & Devis <span class="badge-new">Nouveau</span></h3>
                <p>Créez des devis et factures professionnels, convertissez-les en PDF en un clic.</p>
                
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <svg width="36" height="36" fill="none" viewBox="0 0 36 36"><rect x="6" y="12" width="24" height="12" rx="4" fill="#10B981"/><rect x="10" y="16" width="8" height="4" fill="#1F2937"/></svg>
                </div>
                <h3>Suivi des paiements</h3>
                <p>Visualisez l’état des paiements et relancez vos clients facilement.</p>
            
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <svg width="36" height="36" fill="none" viewBox="0 0 36 36"><rect x="8" y="8" width="20" height="20" rx="6" fill="#1F2937"/><rect x="12" y="16" width="12" height="4" rx="2" fill="#10B981"/></svg>
                </div>
                <h3>Archivage sécurisé</h3>
                <p>Conservez vos documents en toute sécurité, accessibles à tout moment.</p>
                
            </div>
        </div>
    </section>
    <section class="home-section" id="benefices">
        <h2>Pourquoi choisir Factureo ?</h2>
        <div class="benefices-list">
            <div class="benefice-item"><span class="benefice-icon">⏱️</span> Gain de temps au quotidien</div>
            <div class="benefice-item"><span class="benefice-icon">✅</span> Conformité légale et fiscale</div>
            <div class="benefice-item"><span class="benefice-icon">🔔</span> Relances automatiques</div>
            <div class="benefice-item"><span class="benefice-icon">📱</span> Accessible sur tous supports</div>
        </div>
        
    </section>
    
    <footer class="home-footer">
        <div class="footer-links">
            <a href="#accueil">Accueil</a>
            <a href="#apropos">À propos</a>
            <a href="#services">Services</a>
            <a href="#benefices">Bénéfices</a>
            <a href="pages/contact.php">Contact</a>
        </div>
        
        <div class="footer-copy">&copy; <?php echo date('Y'); ?> Factureo. Tous droits réservés. &nbsp;|&nbsp; <a href="mentions-legales.php" style="color:#10B981; text-decoration:underline;">Mentions légales</a> &nbsp;|&nbsp; <a href="politique-confidentialite.php" style="color:#10B981; text-decoration:underline;">Politique de confidentialité</a></div>
    </footer>
    <script src="assets/js/main.js"></script>
</body>
</html> 