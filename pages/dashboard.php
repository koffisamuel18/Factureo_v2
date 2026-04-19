<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
// Indicateurs clés
$res_clients = mysqli_query($conn, "SELECT COUNT(*) FROM clients");
$nb_clients = mysqli_fetch_row($res_clients)[0];
$res_factures = mysqli_query($conn, "SELECT COUNT(*) FROM factures");
$nb_factures = mysqli_fetch_row($res_factures)[0];
$res_devis = mysqli_query($conn, "SELECT COUNT(*) FROM devis");
$nb_devis = mysqli_fetch_row($res_devis)[0];
$res_produits = mysqli_query($conn, "SELECT COUNT(*) FROM produits");
$nb_produits = mysqli_fetch_row($res_produits)[0];
$res_payees = mysqli_query($conn, "SELECT COUNT(*) FROM factures WHERE statut='payée'");
$nb_payees = mysqli_fetch_row($res_payees)[0];
$res_impayees = mysqli_query($conn, "SELECT COUNT(*) FROM factures WHERE statut='impayée'");
$nb_impayees = mysqli_fetch_row($res_impayees)[0];
$res_total_mois = mysqli_query($conn, "SELECT SUM(total_ttc) FROM factures WHERE MONTH(date)=MONTH(CURDATE()) AND YEAR(date)=YEAR(CURDATE())");
$total_mois = mysqli_fetch_row($res_total_mois)[0] ?: 0;
$res_total_paye_mois = mysqli_query($conn, "SELECT SUM(montant) FROM paiements WHERE MONTH(date_paiement)=MONTH(CURDATE()) AND YEAR(date_paiement)=YEAR(CURDATE())");
$total_paye_mois = mysqli_fetch_row($res_total_paye_mois)[0] ?: 0;
// Dernières actions
$res_actions = mysqli_query($conn, "SELECT * FROM historique ORDER BY date DESC LIMIT 5");
$actions = mysqli_fetch_all($res_actions, MYSQLI_ASSOC);
// Dernières factures
$res_last_factures = mysqli_query($conn, "SELECT f.*, c.nom AS client_nom FROM factures f LEFT JOIN clients c ON f.client_id = c.id ORDER BY f.date DESC LIMIT 3");
$last_factures = mysqli_fetch_all($res_last_factures, MYSQLI_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<style>
.dashboard-wrapper { min-height: 80vh; display: flex; flex-direction: column; justify-content: flex-start; align-items: center; padding: 2em 0 3em 0; margin-top: 6.5em; }
.dashboard-title { font-size: 2.2em; font-weight: 700; color: #1F2937; margin-bottom: 0.5em; letter-spacing: 1px; text-align: center; }
.dashboard-sub { color: #10B981; font-size: 1.1em; margin-bottom: 2.5em; text-align: center; }
.dashboard-cards { display: flex; gap: 2.5em; flex-wrap: wrap; justify-content: center; margin-bottom: 2em; }
.card { background: #fff; border-radius: 12px; box-shadow: 0 2px 16px rgba(31,41,55,0.08); padding: 2.2em 2.5em; min-width: 200px; text-align: center; display: flex; flex-direction: column; align-items: center; transition: box-shadow 0.2s; }
.card:hover { box-shadow: 0 6px 24px rgba(16,185,129,0.13); }
.card h2 { font-size: 2.5em; color: #10B981; margin: 0 0 0.3em 0; font-weight: 700; }
.card p { color: #1F2937; font-size: 1.1em; margin: 0; font-weight: 500; }
.dashboard-links { display: flex; gap: 1.2em; margin-bottom: 2em; flex-wrap: wrap; justify-content: center; }
.dashboard-links a { background: #10B981; color: #fff; border-radius: 7px; padding: 0.7em 1.5em; font-weight: 600; text-decoration: none; transition: background 0.2s; box-shadow: 0 2px 8px rgba(16,185,129,0.10); }
.dashboard-links a:hover { background: #1F2937; color: #10B981; }
.dashboard-section { 
    background: #fff; 
    border-radius: 12px; 
    box-shadow: 0 2px 16px rgba(31,41,55,0.08); 
    padding: 1.5em 2em; 
    margin-bottom: 2em; 
    width: 100%; 
    max-width: 900px; 
    overflow-x: auto;
}
.dashboard-section h3 { 
    color: #1F2937; 
    margin-top: 0; 
    margin-bottom: 1em;
    font-size: 1.3em;
}
.dashboard-section table { 
    width: 100%; 
    border-collapse: collapse; 
    background: #fff; 
    min-width: 600px;
}
.dashboard-section th, .dashboard-section td { 
    padding: 0.8em 0.6em; 
    text-align: left; 
    border-bottom: 1px solid #e5e7eb;
    font-size: 0.95em;
}
.dashboard-section th { 
    background: #F3F4F6; 
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #d1d5db;
}
.dashboard-section tr:nth-child(even) { 
    background: #F9FAFB; 
}
.dashboard-section tr:hover {
    background: #f3f4f6;
}
@media (max-width: 768px) { 
    .dashboard-cards { 
        flex-direction: column; 
        gap: 1.2em; 
        padding: 0 1em;
    } 
    .dashboard-wrapper { 
        padding: 1em 0 2em 0; 
        margin-top: 5.5em;
    } 
    .dashboard-section { 
        padding: 1em 0.8em; 
        margin: 1em 0.5em;
        border-radius: 8px;
        overflow-x: auto;
    }
    
    .dashboard-section h3 {
        font-size: 1.2em;
        margin-bottom: 0.8em;
        text-align: center;
    }
    
    .dashboard-section table {
        min-width: 500px;
        font-size: 0.9em;
    }
    
    .dashboard-section th, 
    .dashboard-section td {
        padding: 0.6em 0.4em;
        font-size: 0.85em;
    }
    .dashboard-title {
        font-size: 1.8em;
        padding: 0 1em;
    }
    .dashboard-sub {
        font-size: 1em;
        padding: 0 1em;
    }
    .dashboard-links {
        padding: 0 1em;
        gap: 0.8em;
    }
    .dashboard-links a {
        padding: 0.8em 1.2em;
        font-size: 0.95em;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .card {
        padding: 1.5rem;
        min-width: auto;
    }
    .card h2 {
        font-size: 2em;
    }
    .card p {
        font-size: 1em;
    }
}

@media (max-width: 480px) {
    .dashboard-wrapper {
        padding: 0.5em 0 1.5em 0;
        margin-top: 5em;
    }
    .dashboard-title {
        font-size: 1.6em;
        padding: 0 0.8em;
    }
    .dashboard-sub {
        font-size: 0.95em;
        padding: 0 0.8em;
    }
    .dashboard-cards {
        padding: 0 0.5em;
        gap: 1em;
    }
    .dashboard-section {
        padding: 0.8em 0.6em;
        margin: 0.8em 0.3em;
        overflow-x: auto;
    }
    
    .dashboard-section h3 {
        font-size: 1.1em;
        margin-bottom: 0.6em;
    }
    
    .dashboard-section table {
        min-width: 450px;
        font-size: 0.8em;
    }
    
    .dashboard-section th, 
    .dashboard-section td {
        padding: 0.5em 0.3em;
        font-size: 0.8em;
    }
    .dashboard-links {
        padding: 0 0.5em;
        gap: 0.6em;
        flex-direction: column;
    }
    .dashboard-links a {
        width: 100%;
        padding: 1em 1.2em;
    }
    .card {
        padding: 1.2rem;
    }
    .card h2 {
        font-size: 1.8em;
    }
}
</style>
<div class="dashboard-wrapper">
    <div class="dashboard-title">Bienvenue <?= isset($_SESSION['admin_nom']) ? htmlspecialchars($_SESSION['admin_nom']) : '' ?> !</div>
    <div class="dashboard-sub">Voici un aperçu de votre activité récente.</div>
    <div class="dashboard-cards">
        <div class="card"><h2><?= $nb_clients ?></h2><p>Clients</p></div>
        <div class="card"><h2><?= $nb_produits ?></h2><p>Produits</p></div>
        <div class="card"><h2><?= $nb_devis ?></h2><p>Devis</p></div>
        <div class="card"><h2><?= $nb_factures ?></h2><p>Factures</p></div>
        <div class="card"><h2><?= $nb_payees ?></h2><p>Factures payées</p></div>
        <div class="card"><h2><?= $nb_impayees ?></h2><p>Factures impayées</p></div>
        <div class="card"><h2><?= number_format($total_mois,2) ?> MAD</h2><p>Total facturé ce mois</p></div>
        <div class="card"><h2><?= number_format($total_paye_mois,2) ?> MAD</h2><p>Total encaissé ce mois</p></div>
    </div>
    <div class="dashboard-links">
        <a href="clients.php">+ Ajouter un client</a>
        <a href="produits.php">+ Ajouter un produit</a>
        <a href="devis.php">+ Nouveau devis</a>
        <a href="factures.php">+ Nouvelle facture</a>
        <a href="historique.php">Voir l'historique</a>
    </div>
    <div class="dashboard-section">
        <h3>Dernières actions</h3>
        <table>
            <thead><tr><th>Date</th><th>Action</th><th>Utilisateur</th></tr></thead>
            <tbody>
                <?php foreach ($actions as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['date']) ?></td>
                    <td><?= htmlspecialchars($a['action']) ?></td>
                    <td><?= htmlspecialchars($a['utilisateur']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($actions)): ?>
                <tr><td colspan="3" style="text-align:center;">Aucune action récente.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="dashboard-section">
        <h3>Dernières factures</h3>
        <table>
            <thead><tr><th>N°</th><th>Client</th><th>Date</th><th>Montant TTC</th><th>Statut</th></tr></thead>
            <tbody>
                <?php foreach ($last_factures as $f): ?>
                <tr>
                    <td><?= get_numero_facture_formate($f['id']) ?></td>
                    <td><?= htmlspecialchars($f['client_nom']) ?></td>
                    <td><?= htmlspecialchars($f['date']) ?></td>
                    <td><?= htmlspecialchars($f['total_ttc']) ?> €</td>
                    <td><?= $f['statut'] === 'payée' ? '<span style="color:#10B981;">Payée</span>' : '<span style="color:#e53e3e;">Impayée</span>' ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($last_factures)): ?>
                <tr><td colspan="5" style="text-align:center;">Aucune facture récente.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?> 