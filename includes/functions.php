<?php
// Fonction pour enregistrer une action dans l'historique
function enregistrer_action($conn, $action, $utilisateur) {
    $stmt = mysqli_prepare($conn, "INSERT INTO historique (action, utilisateur) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ss", $action, $utilisateur);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Génère et stocke un token CSRF en session
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Vérifie le token CSRF soumis
function check_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Génère le numéro de facture au format FAC-000X/2025
function get_numero_facture_formate($id) {
    return 'FAC-' . str_pad($id, 4, '0', STR_PAD_LEFT) . '/2025';
} 