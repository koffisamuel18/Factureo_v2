<?php
require_once '../includes/header.php';
?>
<?php include '../includes/navbar.php'; ?>
<style>
main {
    min-height: 80vh;
    padding: 2em 0;
    margin-top: 2.5em;
}

.contact-form {
    max-width: 500px;
    margin: 2em auto 0 auto;
    display: flex;
    flex-direction: column;
    gap: 1.2em;
}

.contact-form input,
.contact-form textarea {
    border: 1.5px solid var(--secondary-grey);
    border-radius: 8px;
    padding: 1em;
    font-size: 1em;
    font-family: inherit;
    background: #fff;
    resize: none;
    color: var(--main-blue);
    transition: border 0.2s;
}

.contact-form input:focus,
.contact-form textarea:focus {
    border-color: var(--success-green);
    outline: none;
}

.contact-form button {
    background: var(--success-green);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 1em;
    font-size: 1.1em;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.2s;
    min-height: 44px;
}

.contact-form button:hover {
    background: #0e9e6e;
}

.contact-infos {
    margin-top: 2em;
    color: var(--main-blue);
    font-size: 1.1em;
    font-weight: 500;
    text-align: center;
}

/* Responsive mobile */
@media (max-width: 768px) {
    main {
        padding: 1em 0;
        margin-top: 1.5em;
    }
    
    .home-section {
        margin: 1em 0.5em;
        padding: 2em 1em 1.5em 1em;
    }
    
    .contact-form {
        max-width: 100%;
        gap: 1em;
        margin: 1.5em auto 0 auto;
    }
    
    .contact-form input,
    .contact-form textarea {
        padding: 1em;
        font-size: 16px; /* Évite le zoom sur iOS */
    }
    
    .contact-form button {
        padding: 1em;
        font-size: 1em;
        min-height: 44px;
    }
    
    .contact-infos {
        font-size: 1em;
        margin-top: 1.5em;
        display: flex;
        flex-direction: column;
        gap: 0.5em;
    }
    
    .contact-infos span {
        padding: 0.5em 0;
    }
}

@media (max-width: 480px) {
    main {
        padding: 0.5em 0;
    }
    
    .home-section {
        margin: 0.5em 0.3em;
        padding: 1.5em 0.8em 1em 0.8em;
    }
    
    .contact-form {
        gap: 0.8em;
        margin: 1em auto 0 auto;
    }
    
    .contact-form input,
    .contact-form textarea {
        padding: 0.9em;
    }
    
    .contact-form button {
        padding: 0.9em;
    }
    
    .contact-infos {
        font-size: 0.95em;
        margin-top: 1.2em;
    }
}
</style>
<main>
<section class="home-section" id="contact">
        <h2>Contact</h2>
        <p class="section-desc">Une question ? Un besoin spécifique ? Contactez-nous, notre équipe vous répond sous 24h.</p>
        <form class="contact-form" method="post" action="#">
            <input type="text" name="nom" placeholder="Votre nom" required>
            <input type="email" name="email" placeholder="Votre email" required>
            <textarea name="message" placeholder="Votre message" rows="4" required></textarea>
            <button type="submit">Envoyer</button>
        </form>
        <div class="contact-infos">
            <span>📧 contact@factureo.com</span> &nbsp; | &nbsp; <span>📞 +212 0625085226</span>
        </div>
    </section>
</main>
<?php include '../includes/footer.php'; ?> 