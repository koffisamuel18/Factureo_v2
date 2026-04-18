<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factureo</title>
    <link rel="stylesheet" href="/factureo/assets/css/style.css">
</head>
<body>
<script src="/factureo/assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Sélecteur pour tous les liens d'ancre vers index.php
  const anchorLinks = document.querySelectorAll('a[href^="/factureo/index.php#"]');
  anchorLinks.forEach(function(link) {
    link.addEventListener('click', function(e) {
      const href = link.getAttribute('href');
      const anchor = href.split('#')[1];
      // Vérifie si on est déjà sur index.php
      if (window.location.pathname.endsWith('/index.php') || window.location.pathname === '/index.php' || window.location.pathname === '/factureo/index.php') {
        e.preventDefault();
        const target = document.getElementById(anchor);
        if (target) {
          window.scrollTo({ top: target.offsetTop - 70, behavior: 'smooth' });
        }
      } // sinon comportement normal (redirige vers index.php#ancre)
    });
  });
});
</script> 