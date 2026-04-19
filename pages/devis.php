<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';

// Récupérer la liste des clients
$res_clients = mysqli_query($conn, "SELECT id, nom FROM clients ORDER BY nom ASC");
$clients = mysqli_fetch_all($res_clients, MYSQLI_ASSOC);
// Récupérer la liste des produits/services
$res_produits = mysqli_query($conn, "SELECT id, nom, prix_unitaire, tva FROM produits ORDER BY nom ASC");
$produits = mysqli_fetch_all($res_produits, MYSQLI_ASSOC);

$edit_mode = false;
$edit_devis = null;
$error = '';

// Traitement ajout/modification devis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['client_id'], $_POST['lignes'])) {
    $client_id = intval($_POST['client_id']);
    $lignes = $_POST['lignes']; // tableau de lignes
    $total_ht = 0; $total_tva = 0; $total_ttc = 0;
    if ($client_id <= 0) {
        $error = "Client invalide.";
    } elseif (!is_array($lignes) || count($lignes) === 0) {
        $error = "Le devis doit contenir au moins une ligne.";
    } else {
        foreach ($lignes as $i => $ligne) {
            $produit_id = isset($ligne['produit_id']) ? intval($ligne['produit_id']) : 0;
            $qte = isset($ligne['quantite']) ? floatval($ligne['quantite']) : 0;
            $pu = isset($ligne['prix_unitaire']) ? floatval($ligne['prix_unitaire']) : 0;
            $tva = isset($ligne['tva']) ? floatval($ligne['tva']) : 0;
            $remise = isset($ligne['remise']) ? floatval($ligne['remise']) : 0;
            if ($produit_id <= 0) {
                $error = "Ligne ".($i+1)." : produit invalide.";
                break;
            } elseif ($qte <= 0 || $qte > 100000) {
                $error = "Ligne ".($i+1)." : quantité invalide (1 à 100 000).";
                break;
            } elseif ($pu < 0 || $pu > 1000000) {
                $error = "Ligne ".($i+1)." : prix unitaire invalide (0 à 1 000 000).";
                break;
            } elseif ($tva < 0 || $tva > 100) {
                $error = "Ligne ".($i+1)." : TVA invalide (0 à 100).";
                break;
            } elseif ($remise < 0 || $remise > ($pu * $qte)) {
                $error = "Ligne ".($i+1)." : remise invalide (0 à total HT de la ligne).";
                break;
            }
            $ht = ($pu * $qte) - $remise;
            $tva_val = $ht * $tva / 100;
            $total_ht += $ht;
            $total_tva += $tva_val;
            $total_ttc += $ht + $tva_val;
        }
    }
    if (empty($error)) {
        // Insertion du devis
        $stmt = mysqli_prepare($conn, "INSERT INTO devis (client_id, total_ht, total_tva, total_ttc) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            $error = 'Erreur SQL lors de la création du devis : ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, "iddd", $client_id, $total_ht, $total_tva, $total_ttc);
            if (!mysqli_stmt_execute($stmt)) {
                $error = 'Erreur lors de l\'insertion du devis : ' . mysqli_stmt_error($stmt);
            }
            $devis_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);
            // Insertion des lignes
            foreach ($lignes as $ligne) {
                $produit_id = intval($ligne['produit_id']);
                $qte = floatval($ligne['quantite']);
                $pu = floatval($ligne['prix_unitaire']);
                $tva = floatval($ligne['tva']);
                $remise = floatval($ligne['remise']);
                $stmt = mysqli_prepare($conn, "INSERT INTO lignes (devis_id, produit_id, quantite, prix_unitaire, tva, remise) VALUES (?, ?, ?, ?, ?, ?)");
                if (!$stmt) {
                    $error = 'Erreur SQL lors de la création d\'une ligne : ' . mysqli_error($conn);
                    break;
                }
                mysqli_stmt_bind_param($stmt, "iidddd", $devis_id, $produit_id, $qte, $pu, $tva, $remise);
                if (!mysqli_stmt_execute($stmt)) {
                    $error = 'Erreur lors de l\'insertion d\'une ligne : ' . mysqli_stmt_error($stmt);
                    mysqli_stmt_close($stmt);
                    break;
                }
                mysqli_stmt_close($stmt);
            }
            if (empty($error)) {
                enregistrer_action($conn, "Ajout du devis #$devis_id pour le client #$client_id", $_SESSION['admin_email']);
                header('Location: devis.php?success=add');
                exit;
            }
        }
    }
}

// Suppression d'un devis
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $res = mysqli_query($conn, "SELECT client_id FROM devis WHERE id=$id");
    $row = mysqli_fetch_assoc($res);
    $client_id = $row ? $row['client_id'] : '';
    mysqli_query($conn, "DELETE FROM lignes WHERE devis_id=$id");
    mysqli_query($conn, "DELETE FROM devis WHERE id=$id");
    enregistrer_action($conn, "Suppression du devis #$id pour le client #$client_id", $_SESSION['admin_email']);
    header('Location: devis.php');
    exit;
}

// Récupérer l'ordre depuis l'URL ou par défaut croissant
$ordre = (isset($_GET['ordre']) && $_GET['ordre'] === 'desc') ? 'DESC' : 'ASC';

// Liste des devis avec pagination et tri dynamique
$devis_par_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? intval($_GET['page']) : 1;
$sql_count = "SELECT COUNT(*) as total FROM devis";
$res_count = mysqli_query($conn, $sql_count);
$total_devis = mysqli_fetch_assoc($res_count)['total'];
$total_pages = ceil($total_devis / $devis_par_page);
$offset = ($page - 1) * $devis_par_page;
$sql = "SELECT d.*, c.nom AS client_nom FROM devis d LEFT JOIN clients c ON d.client_id = c.id ORDER BY d.id $ordre LIMIT $devis_par_page OFFSET $offset";
$res = mysqli_query($conn, $sql);
$devis = mysqli_fetch_all($res, MYSQLI_ASSOC);

// Message de succès
$success = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'add') $success = 'Devis ajouté avec succès.';
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<style>
.devis-wrapper { min-height: 80vh; display: flex; flex-direction: column; justify-content: flex-start; align-items: center; padding: 2em 0 3em 0; }
.devis-form-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 16px rgba(31,41,55,0.08); padding: 2.2em 2.5em; max-width: 700px; width: 100%; margin-bottom: 2.5em; }
.devis-form-card h1 { text-align: center; color: #1F2937; margin-bottom: 1.2em; }
.devis-form-card form { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1em 2em; row-gap: 1.7em; }
.devis-form-card label { margin-bottom: 0.3em; color: #1F2937; font-weight: 500; }
.devis-form-card input, .devis-form-card select { width: 100%; border: 2px solid #1F2937; border-radius: 7px; padding: 0.8em 1em; font-size: 1em; background: #fff; color: #1F2937; transition: border 0.2s; }
.devis-form-card input:focus, .devis-form-card select:focus { border: 2px solid #10B981; outline: none; }
.devis-form-card select[name="client_id"] { max-width: 250px; margin-left: auto; margin-right: auto; }
@media (max-width: 900px) { .devis-form-card { padding: 1.2em 0.7em; } .devis-form-card form { grid-template-columns: 1fr; } }
/* Inputs dynamiques du tableau */
#lignes-table input, #lignes-table select { border: 2px solid #1F2937; border-radius: 7px; padding: 0.5em 0.7em; font-size: 1em; background: #fff; color: #1F2937; transition: border 0.2s; }
#lignes-table input:focus, #lignes-table select:focus { border: 2px solid #10B981; outline: none; }
#lignes-table select {
    margin-top: -10px;
    line-height: 1.1;
    height: 44px;
    padding-top: 0.2em;
    padding-bottom: 0.2em;
}
</style>
<div class="devis-wrapper">
    <div class="devis-form-card">
        <h1>Créer un devis</h1>
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lignes'])): ?>
            <pre style="background:#f9f9f9; color:#222; font-size:0.98em; padding:0.7em 1em; border-radius:7px; margin-bottom:1em;">POST['lignes'] = <?php var_dump($_POST['lignes']); ?></pre>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error">Erreur : <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post" id="devis-form">
            <div>
                <label>Client *</label>
                <select name="client_id" required>
                    <option value="">Sélectionner un client</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="grid-column:span 2;">
                <h3>Lignes du devis</h3>
                <table id="lignes-table" style="width:100%; background:#fff; border-radius:10px; box-shadow:0 1px 6px rgba(31,41,55,0.06);">
                    <thead style="background:#F3F4F6;">
                        <tr>
                            <th>Produit/Service</th>
                            <th>Quantité</th>
                            <th>Prix unitaire (MAD)</th>
                            <th>TVA (%)</th>
                            <th>Remise (MAD)</th>
                            <th>Total HT (MAD)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="lignes-body">
                    </tbody>
                </table>
                <button type="button" id="add-ligne" style="margin:1em 0;">+ Ajouter une ligne</button>
            </div>
            <div style="grid-column:span 2; display:flex; gap:2em; flex-wrap:wrap;">
                <div><strong>Total HT :</strong> <span id="total_ht">0.00</span> MAD</div>
                <div><strong>Total TVA :</strong> <span id="total_tva">0.00</span> MAD</div>
                <div><strong>Total TTC :</strong> <span id="total_ttc">0.00</span> MAD</div>
            </div>
            <div style="grid-column:span 2; text-align:right; margin-top:1em;">
                <button type="submit">Enregistrer le devis</button>
            </div>
        </form>
    </div>
    <section style="width:100%; max-width:1100px; margin:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1em;">
            <h2 style="margin:0;">Liste des devis</h2>
            <form method="get" style="margin:0; display:flex; align-items:center; gap:0.7em;">
                <label for="ordre" style="font-weight:500;">Ordre :</label>
                <select name="ordre" id="ordre" onchange="this.form.submit()">
                    <option value="asc"<?= $ordre==='ASC' ? ' selected' : '' ?>>Croissant</option>
                    <option value="desc"<?= $ordre==='DESC' ? ' selected' : '' ?>>Décroissant</option>
                </select>
                <?php
                foreach($_GET as $k=>$v) {
                    if ($k !== 'ordre') echo '<input type="hidden" name="'.htmlspecialchars($k).'" value="'.htmlspecialchars($v).'">';
                }
                ?>
            </form>
        </div>
        <div style="margin-bottom:0.5em; color:#888; font-size:1em;">Nombre de devis trouvés : <?= count($devis) ?></div>
        <table class="clients-table">
            <thead>
                <tr>
                    <th class="numero-col">N°</th>
                    <th>Client</th>
                    <th class="date-col">Date</th>
                    <th class="prix-col">Total HT (MAD)</th>
                    <th class="prix-col">Total TVA (MAD)</th>
                    <th class="prix-col">Total TTC (MAD)</th>
                    <th class="actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($devis as $d): ?>
                    <tr>
                        <td class="numero-col">#<?= $d['id'] ?></td>
                        <td><?= $d['client_nom'] ? htmlspecialchars($d['client_nom']) : '<span style="color:#e53e3e;">Client inconnu</span>' ?></td>
                        <td class="date-col"><?= htmlspecialchars($d['date']) ?></td>
                        <td class="prix-col"><?= htmlspecialchars($d['total_ht']) ?> MAD</td>
                        <td class="prix-col"><?= htmlspecialchars($d['total_tva']) ?> MAD</td>
                        <td class="prix-col"><?= htmlspecialchars($d['total_ttc']) ?> MAD</td>
                        <td class="actions-cell">
                            <a href="factures.php?from_devis=<?= $d['id'] ?>" class="btn btn-green">Convertir en facture</a>
                            <a href="devis.php?delete=<?= $d['id'] ?>" class="btn btn-red btn-delete">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($devis)): ?>
                    <tr><td colspan="7" style="text-align:center;">Aucun devis enregistré.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if ($total_pages > 1): ?>
        <nav style="margin-top:1.5em; display:flex; justify-content:center; gap:0.5em; flex-wrap:wrap;">
            <?php
            $url_base = 'devis.php?';
            $params_url = $_GET;
            foreach(['page'] as $unset) unset($params_url[$unset]);
            $url_base .= http_build_query($params_url);
            if ($url_base !== 'devis.php?' && substr($url_base, -1) !== '&' && substr($url_base, -1) !== '?') $url_base .= '&';
            ?>
            <?php if ($page > 1): ?>
                <a href="<?= $url_base . 'page=' . ($page-1) ?>" class="btn">&laquo; Précédent</a>
            <?php endif; ?>
            <?php for ($i=1; $i<=$total_pages; $i++): ?>
                <a href="<?= $url_base . 'page=' . $i ?>" class="btn<?= $i==$page ? ' btn-green' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="<?= $url_base . 'page=' . ($page+1) ?>" class="btn">Suivant &raquo;</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </section>
</div>
<script>
const produits = <?= json_encode($produits) ?>;
let ligneIndex = 0;
function createLigneRow(selected = null) {
    const idx = ligneIndex++;
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
            <select name="lignes[${idx}][produit_id]" class="prod-select" required>
                <option value="">Sélectionner</option>
                ${produits.map(p => `<option value="${p.id}" data-prix="${p.prix_unitaire}" data-tva="${p.tva}">${p.nom}</option>`).join('')}
            </select>
        </td>
        <td><input type="number" name="lignes[${idx}][quantite]" min="1" value="1" style="width:60px;" required></td>
        <td><input type="number" name="lignes[${idx}][prix_unitaire]" min="0" step="0.01" value="" style="width:90px;" required></td>
        <td><input type="number" name="lignes[${idx}][tva]" min="0" step="0.01" value="20" style="width:60px;" required></td>
        <td><input type="number" name="lignes[${idx}][remise]" min="0" step="0.01" value="0" style="width:70px;"></td>
        <td class="ligne-ht">0.00</td>
        <td><button type="button" class="remove-ligne">✕</button></td>
    `;
    return tr;
}
function updateTotals() {
    let total_ht = 0, total_tva = 0, total_ttc = 0;
    document.querySelectorAll('#lignes-body tr').forEach(tr => {
        const qte = parseFloat(tr.querySelector('[name$="[quantite]"]').value) || 0;
        const pu = parseFloat(tr.querySelector('[name$="[prix_unitaire]"]').value) || 0;
        const tva = parseFloat(tr.querySelector('[name$="[tva]"]').value) || 0;
        const remise = parseFloat(tr.querySelector('[name$="[remise]"]').value) || 0;
        const ht = (pu * qte) - remise;
        const tva_val = ht * tva / 100;
        tr.querySelector('.ligne-ht').textContent = ht.toFixed(2);
        total_ht += ht;
        total_tva += tva_val;
        total_ttc += ht + tva_val;
    });
    document.getElementById('total_ht').textContent = total_ht.toFixed(2);
    document.getElementById('total_tva').textContent = total_tva.toFixed(2);
    document.getElementById('total_ttc').textContent = total_ttc.toFixed(2);
}
document.getElementById('add-ligne').onclick = function() {
    const tr = createLigneRow();
    document.getElementById('lignes-body').appendChild(tr);
    tr.querySelectorAll('input,select').forEach(input => {
        input.oninput = updateTotals;
        if(input.classList.contains('prod-select')){
            input.onchange = function() {
                const opt = input.selectedOptions[0];
                if(opt && opt.value) {
                    tr.querySelector('[name$="[prix_unitaire]"]').value = opt.getAttribute('data-prix');
                    tr.querySelector('[name$="[tva]"]').value = opt.getAttribute('data-tva');
                    updateTotals();
                }
            }
        }
    });
    tr.querySelector('.remove-ligne').onclick = function() {
        tr.remove();
        updateTotals();
    };
    updateTotals();
};
// Ajouter une ligne par défaut au chargement
if(document.getElementById('lignes-body').children.length === 0) {
    document.getElementById('add-ligne').click();
}
</script>
<?php include '../includes/footer.php'; ?> 