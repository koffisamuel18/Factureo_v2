<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';

// Ajout ou modification d'un produit/service
$edit_mode = false;
$edit_produit = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $prix_unitaire = floatval($_POST['prix_unitaire']);
    $tva = floatval($_POST['tva']);
    // Validation renforcée
    if ($nom === '' || mb_strlen($nom) > 100 || !preg_match('/^[\p{L}0-9 .\'-]+$/u', $nom)) {
        $error = "Le nom est obligatoire, doit faire moins de 100 caractères et ne contenir que des lettres, chiffres, espaces, points, apostrophes ou tirets.";
    } elseif ($description !== '' && mb_strlen($description) > 255) {
        $error = "La description doit faire moins de 255 caractères.";
    } elseif (!is_numeric($_POST['prix_unitaire']) || $prix_unitaire < 0 || $prix_unitaire > 1000000) {
        $error = "Le prix unitaire doit être un nombre positif raisonnable (max 1 000 000).";
    } elseif (!is_numeric($_POST['tva']) || $tva < 0 || $tva > 100) {
        $error = "La TVA doit être un pourcentage entre 0 et 100.";
    } else {
        if (isset($_POST['produit_id']) && $_POST['produit_id'] !== '') {
            // Modification
            $id = intval($_POST['produit_id']);
            $stmt = mysqli_prepare($conn, "UPDATE produits SET nom=?, description=?, prix_unitaire=?, tva=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssdsi", $nom, $description, $prix_unitaire, $tva, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            enregistrer_action($conn, "Modification du produit #$id ($nom)", $_SESSION['admin_email']);
            header('Location: produits.php?success=edit');
            exit;
        } else {
            // Ajout
            $stmt = mysqli_prepare($conn, "INSERT INTO produits (nom, description, prix_unitaire, tva) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssdd", $nom, $description, $prix_unitaire, $tva);
            mysqli_stmt_execute($stmt);
            $new_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);
            enregistrer_action($conn, "Ajout du produit #$new_id ($nom)", $_SESSION['admin_email']);
            header('Location: produits.php?success=add');
            exit;
        }
    }
}

// Préparation modification
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = intval($_GET['edit']);
    $res = mysqli_query($conn, "SELECT * FROM produits WHERE id=$id");
    $edit_produit = mysqli_fetch_assoc($res);
}

// Suppression
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $res = mysqli_query($conn, "SELECT nom FROM produits WHERE id=$id");
    $row = mysqli_fetch_assoc($res);
    $nom = $row ? $row['nom'] : '';
    mysqli_query($conn, "DELETE FROM produits WHERE id=$id");
    enregistrer_action($conn, "Suppression du produit #$id ($nom)", $_SESSION['admin_email']);
    header('Location: produits.php');
    exit;
}

// Récupérer l'ordre depuis l'URL ou par défaut croissant
$ordre = (isset($_GET['ordre']) && $_GET['ordre'] === 'desc') ? 'DESC' : 'ASC';

// Liste des produits/services avec pagination et tri dynamique
$produits_par_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? intval($_GET['page']) : 1;
$sql_count = "SELECT COUNT(*) as total FROM produits";
$res_count = mysqli_query($conn, $sql_count);
$total_produits = mysqli_fetch_assoc($res_count)['total'];
$total_pages = ceil($total_produits / $produits_par_page);
$offset = ($page - 1) * $produits_par_page;
$sql = "SELECT * FROM produits ORDER BY nom $ordre LIMIT $produits_par_page OFFSET $offset";
$res = mysqli_query($conn, $sql);
$produits = mysqli_fetch_all($res, MYSQLI_ASSOC);

// Message de succès
$success = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'add') $success = 'Produit ajouté avec succès.';
    elseif ($_GET['success'] === 'edit') $success = 'Produit modifié avec succès.';
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<style>
.produits-wrapper { min-height: 80vh; display: flex; flex-direction: column; justify-content: flex-start; align-items: center; padding: 2em 0 3em 0; }
.produit-form-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 16px rgba(31,41,55,0.08); padding: 2.2em 2.5em; max-width: 600px; width: 100%; margin-bottom: 2.5em; }
.produit-form-card h2 { text-align: center; color: #1F2937; margin-bottom: 1.2em; }
.produit-form-card form { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1em 2em; row-gap: 1.7em; }
.produit-form-card label { margin-bottom: 0.3em; color: #1F2937; font-weight: 500; }
.produit-form-card input, .produit-form-card textarea { width: 100%; border: 2px solid #1F2937; border-radius: 7px; padding: 0.8em 1em; font-size: 1em; background: #fff; color: #1F2937; transition: border 0.2s; }
.produit-form-card input:focus, .produit-form-card textarea:focus { border: 2px solid #10B981; outline: none; }
.produit-form-card input[name="nom"], .produit-form-card input[name="prix_unitaire"] { max-width: 250px; margin-left: auto; margin-right: auto; }
.produit-form-card textarea { min-height: 60px; resize: vertical; }
@media (max-width: 700px) { .produit-form-card { padding: 1.2em 0.7em; } .produit-form-card form { grid-template-columns: 1fr; } }
</style>
<div class="produits-wrapper">
    <div class="produit-form-card">
        <h2><?= $edit_mode ? 'Modifier un produit/service' : 'Ajouter un produit/service' ?></h2>
        <?php if ($success): ?>
            <div class="success-message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="produit_id" value="<?= $edit_mode ? $edit_produit['id'] : '' ?>">
            <div>
                <label>Nom *</label>
                <input type="text" name="nom" value="<?= $edit_mode ? htmlspecialchars($edit_produit['nom']) : '' ?>" required>
            </div>
            <div>
                <label>Description</label>
                <textarea name="description"><?= $edit_mode ? htmlspecialchars($edit_produit['description']) : '' ?></textarea>
            </div>
            <div>
                <label>Prix unitaire (MAD) *</label>
                <input type="number" name="prix_unitaire" min="0" step="0.01" value="<?= $edit_mode ? htmlspecialchars($edit_produit['prix_unitaire']) : '' ?>" required>
            </div>
            <div>
                <label>TVA (%) *</label>
                <input type="number" name="tva" min="0" step="0.01" value="<?= $edit_mode ? htmlspecialchars($edit_produit['tva']) : '20.00' ?>" required>
            </div>
            <div style="grid-column:span 2; text-align:right; margin-top:1em;">
                <button type="submit"><?= $edit_mode ? 'Enregistrer les modifications' : 'Ajouter le produit/service' ?></button>
                <?php if ($edit_mode): ?>
                    <a href="produits.php" style="margin-left:1em; color:#1F2937;">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <section>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1em;">
            <h2 style="margin:0;">Liste des produits / services</h2>
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
        <table class="clients-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th class="description-col">Description</th>
                    <th class="prix-col">Prix unitaire (MAD)</th>
                    <th class="tva-col">TVA (%)</th>
                    <th class="actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produits as $produit): ?>
                    <tr>
                        <td><?= htmlspecialchars($produit['nom']) ?></td>
                        <td class="description-col"><?= htmlspecialchars($produit['description']) ?></td>
                        <td class="prix-col"><?= htmlspecialchars($produit['prix_unitaire']) ?> MAD</td>
                        <td class="tva-col"><?= htmlspecialchars($produit['tva']) ?></td>
                        <td class="actions-cell">
                            <a href="produits.php?edit=<?= $produit['id'] ?>" class="btn btn-green btn-edit">Modifier</a>
                            <a href="produits.php?delete=<?= $produit['id'] ?>" class="btn btn-red btn-delete">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($produits)): ?>
                    <tr><td colspan="5" style="text-align:center;">Aucun produit/service enregistré.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
    <?php if ($total_pages > 1): ?>
    <nav style="margin-top:1.5em; display:flex; justify-content:center; gap:0.5em; flex-wrap:wrap;">
        <?php
        $url_base = 'produits.php?';
        $params_url = $_GET;
        foreach(['page'] as $unset) unset($params_url[$unset]);
        $url_base .= http_build_query($params_url);
        if ($url_base !== 'produits.php?' && substr($url_base, -1) !== '&' && substr($url_base, -1) !== '?') $url_base .= '&';
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
</div>
<?php include '../includes/footer.php'; ?>
<script>
const deleteBtns = document.querySelectorAll('.btn-delete');
deleteBtns.forEach(btn => btn.addEventListener('click', function(e) {
    if (!confirm('Voulez-vous vraiment supprimer ce produit/service ? Cette action est irréversible.')) e.preventDefault();
}));
</script> 