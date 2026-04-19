<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="home-navbar">
    <div class="nav-logo">
        <a href="/factureo/index.php#accueil" style="display:flex;align-items:center;gap:0.7em;text-decoration:none;">
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
        <?php if (isset($_SESSION['admin_id'])): ?>
            <a href="/factureo/pages/dashboard.php">Tableau de bord</a>
            <a href="/factureo/pages/clients.php">Clients</a>
            <a href="/factureo/pages/produits.php">Produits</a>
            <a href="/factureo/pages/devis.php">Devis</a>
            <a href="/factureo/pages/factures.php">Factures</a>
            <a href="/factureo/pages/historique.php">Historique</a>
            <a href="/factureo/pages/logout.php" class="nav-btn" style="color:#fff;">Déconnexion</a>
        <?php else: ?>
            <a href="/factureo/index.php#accueil">Accueil</a>
            <a href="/factureo/index.php#apropos">À propos</a>
            <a href="/factureo/index.php#services">Services</a>
            <a href="/factureo/index.php#benefices">Bénéfices</a>
            <a href="/factureo/pages/contact.php">Contact</a>
            <a href="/factureo/pages/login.php" class="nav-btn">Connexion</a>
        <?php endif; ?>
    </div>
</nav> 