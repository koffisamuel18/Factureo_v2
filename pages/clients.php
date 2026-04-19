<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';

// Ajout ou modification d'un client
$edit_mode = false;
$edit_client = null;
$error = '';

// Traitement ajout/modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !check_csrf_token($_POST['csrf_token'])) {
        $error = "Erreur de sécurité : le formulaire a expiré ou est invalide.";
    } else {
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $adresse = trim($_POST['adresse']);
        $email = trim($_POST['email']);
        $telephone = trim($_POST['telephone']);
        // Validation renforcée
        if ($nom === '' || mb_strlen($nom) > 100 || !preg_match('/^[\p{L}0-9 .\'-]+$/u', $nom)) {
            $error = "Le nom du client est obligatoire, doit faire moins de 100 caractères et ne contenir que des lettres, chiffres, espaces, points, apostrophes ou tirets.";
        } elseif ($prenom !== '' && (mb_strlen($prenom) > 100 || !preg_match('/^[\p{L}0-9 .\'-]+$/u', $prenom))) {
            $error = "Le prénom doit faire moins de 100 caractères et ne contenir que des lettres, chiffres, espaces, points, apostrophes ou tirets.";
        } elseif ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100)) {
            $error = "L'email doit être valide et faire moins de 100 caractères.";
        } elseif ($telephone !== '' && (mb_strlen($telephone) > 20 || !preg_match('/^[0-9 +().-]*$/', $telephone))) {
            $error = "Le téléphone doit faire moins de 20 caractères et ne contenir que des chiffres, espaces, +, -, (, ), ou .";
        } elseif ($adresse !== '' && mb_strlen($adresse) > 255) {
            $error = "L'adresse doit faire moins de 255 caractères.";
        } else {
            if (isset($_POST['client_id']) && $_POST['client_id'] !== '') {
                // Modification
                $id = intval($_POST['client_id']);
                $stmt = mysqli_prepare($conn, "UPDATE clients SET nom=?, prenom=?, adresse=?, email=?, telephone=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, "sssssi", $nom, $prenom, $adresse, $email, $telephone, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                enregistrer_action($conn, "Modification du client #$id ($nom)", $_SESSION['admin_email']);
            } else {
                // Ajout
                $stmt = mysqli_prepare($conn, "INSERT INTO clients (nom, prenom, adresse, email, telephone) VALUES (?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sssss", $nom, $prenom, $adresse, $email, $telephone);
                mysqli_stmt_execute($stmt);
                $new_id = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);
                enregistrer_action($conn, "Ajout du client #$new_id ($nom)", $_SESSION['admin_email']);
            }
            header('Location: clients.php?success=add');
            exit;
        }
    }
}

// Préparation modification
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = intval($_GET['edit']);
    $res = mysqli_query($conn, "SELECT * FROM clients WHERE id=$id");
    $edit_client = mysqli_fetch_assoc($res);
}

// Suppression
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $res = mysqli_query($conn, "SELECT nom FROM clients WHERE id=$id");
    $row = mysqli_fetch_assoc($res);
    $nom = $row ? $row['nom'] : '';
    mysqli_query($conn, "DELETE FROM clients WHERE id=$id");
    enregistrer_action($conn, "Suppression du client #$id ($nom)", $_SESSION['admin_email']);
    header('Location: clients.php?success=delete');
    exit;
}

// Récupérer l'ordre depuis l'URL ou par défaut croissant
$ordre = (isset($_GET['ordre']) && $_GET['ordre'] === 'desc') ? 'DESC' : 'ASC';

// Liste des clients avec pagination et tri DESC
$clients_par_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? intval($_GET['page']) : 1;
$sql_count = "SELECT COUNT(*) as total FROM clients";
$res_count = mysqli_query($conn, $sql_count);
$total_clients = mysqli_fetch_assoc($res_count)['total'];
$total_pages = ceil($total_clients / $clients_par_page);
$offset = ($page - 1) * $clients_par_page;
$sql = "SELECT * FROM clients ORDER BY id $ordre LIMIT $clients_par_page OFFSET $offset";
$res = mysqli_query($conn, $sql);
$clients = mysqli_fetch_all($res, MYSQLI_ASSOC);

// Gestion des messages de succès
$success = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'add') $success = 'Client ajouté avec succès.';
    elseif ($_GET['success'] === 'edit') $success = 'Client modifié avec succès.';
    elseif ($_GET['success'] === 'delete') $success = 'Client supprimé avec succès.';
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<style>
.clients-wrapper { min-height: 80vh; display: flex; flex-direction: column; justify-content: flex-start; align-items: center; padding: 2em 0 3em 0; }
.client-form-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 16px rgba(31,41,55,0.08); padding: 2.2em 2.5em; max-width: 600px; width: 100%; margin-bottom: 2.5em; }
.client-form-card h2 { text-align: center; color: #1F2937; margin-bottom: 1.2em; }
.client-form-card form { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1em 2em; row-gap: 1.7em; }
.client-form-card label { margin-bottom: 0.3em; color: #1F2937; font-weight: 500; }
.client-form-card input, .client-form-card textarea { width: 100%; border: 2px solid #1F2937; border-radius: 7px; padding: 0.8em 1em; font-size: 1em; background: #fff; color: #1F2937; transition: border 0.2s; }
.client-form-card input:focus, .client-form-card textarea:focus { border: 2px solid #10B981; outline: none; }
.client-form-card input[name="nom"], .client-form-card input[name="email"] { max-width: 250px; margin-left: auto; margin-right: auto; }
.client-form-card textarea { min-height: 60px; resize: vertical; }
@media (max-width: 768px) { 
    .clients-wrapper { 
        padding: 1em 0 2em 0; 
        margin-top: 1.5em;
    }
    .client-form-card { 
        padding: 1.5em 1.2em; 
        margin: 0 1em 2em 1em;
        border-radius: 8px;
    } 
    .client-form-card form { 
        grid-template-columns: 1fr; 
        gap: 1em;
        row-gap: 1.2em;
    }
    .client-form-card h2 {
        font-size: 1.6em;
        margin-bottom: 1em;
    }
    .client-form-card input, 
    .client-form-card textarea {
        padding: 1em;
        font-size: 16px; /* Évite le zoom sur iOS */
    }
    .client-form-card input[name="nom"], 
    .client-form-card input[name="email"] {
        max-width: 100%;
    }
    .client-form-card button {
        width: 100%;
        padding: 1em;
        font-size: 1em;
        min-height: 44px;
    }
    .client-form-card a {
        display: block;
        text-align: center;
        margin-top: 1em;
        padding: 0.8em;
        background: #f3f4f6;
        border-radius: 6px;
        text-decoration: none;
    }
    
    /* Section liste des clients */
    section {
        padding: 0 1em;
    }
    section > div {
        flex-direction: column;
        gap: 1em;
        align-items: stretch;
    }
    section h2 {
        font-size: 1.5em;
        text-align: center;
    }
    section form {
        justify-content: center;
    }
    section select {
        padding: 0.8em;
        font-size: 1em;
        min-height: 44px;
    }
    
    /* Pagination */
    nav {
        padding: 0 1em;
        gap: 0.3em;
    }
    nav a {
        padding: 0.8em 1em;
        font-size: 0.9em;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .clients-wrapper {
        padding: 0.5em 0 1.5em 0;
    }
    .client-form-card {
        padding: 1.2em 1em;
        margin: 0 0.5em 1.5em 0.5em;
    }
    .client-form-card h2 {
        font-size: 1.4em;
    }
    .client-form-card input, 
    .client-form-card textarea {
        padding: 0.9em;
    }
    
    section {
        padding: 0 0.5em;
    }
    section h2 {
        font-size: 1.3em;
    }
    
    nav {
        padding: 0 0.5em;
        flex-direction: column;
        align-items: center;
    }
    nav a {
        width: 100%;
        max-width: 200px;
    }
}
</style>
<div class="clients-wrapper">
    <div class="client-form-card">
        <h2><?= $edit_mode ? 'Modifier un client' : 'Ajouter un client' ?></h2>
        <?php if ($success): ?>
            <div class="success-message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
            <input type="hidden" name="client_id" value="<?= $edit_mode ? $edit_client['id'] : '' ?>">
            <div>
                <label>Nom *</label>
                <input type="text" name="nom" value="<?= $edit_mode ? htmlspecialchars($edit_client['nom']) : '' ?>" required>
            </div>
            <div>
                <label>Prénom</label>
                <input type="text" name="prenom" value="<?= $edit_mode ? htmlspecialchars($edit_client['prenom']) : '' ?>">
            </div>
            <div>
                <label>Email</label>
                <input type="email" name="email" placeholder="exemple@email.com" value="<?= $edit_mode ? htmlspecialchars($edit_client['email']) : '' ?>">
            </div>
            <div>
                <label>Téléphone</label>
                <input type="text" name="telephone" placeholder="+212 670112233" pattern="\+212 ?[5-7][0-9]{8}" value="<?= $edit_mode ? htmlspecialchars($edit_client['telephone']) : '' ?>">
            </div>
            <div style="grid-column:span 2;">
                <label>Adresse</label>
                <textarea name="adresse" placeholder="12, Rue des Fleurs, Rabat"><?= $edit_mode ? htmlspecialchars($edit_client['adresse']) : '' ?></textarea>
            </div>
            <div style="grid-column:span 2; text-align:right; margin-top:1em;">
                <button type="submit"><?= $edit_mode ? 'Enregistrer les modifications' : 'Ajouter le client' ?></button>
                <?php if ($edit_mode): ?>
                    <a href="clients.php" style="margin-left:1em; color:#1F2937;">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <section>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1em;">
            <h2 style="margin:0;">Liste des clients</h2>
            <form method="get" style="margin:0; display:flex; align-items:center; gap:0.7em;">
                <label for="ordre" style="font-weight:500;">Ordre :</label>
                <select name="ordre" id="ordre" onchange="this.form.submit()">
                    <option value="asc"<?= $ordre==='ASC' ? ' selected' : '' ?>>Croissant</option>
                    <option value="desc"<?= $ordre==='DESC' ? ' selected' : '' ?>>Décroissant</option>
                </select>
                <?php
                // Garder les autres paramètres (page, recherche, etc.)
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
                    <th>Prénom</th>
                    <th class="email-col">Email</th>
                    <th class="telephone-col">Téléphone</th>
                    <th class="adresse-col">Adresse</th>
                    <th class="date-col">Date création</th>
                    <th class="actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?= htmlspecialchars($client['nom']) ?></td>
                        <td><?= htmlspecialchars($client['prenom']) ?></td>
                        <td class="email-col"><?= htmlspecialchars($client['email']) ?></td>
                        <td class="telephone-col"><?= htmlspecialchars($client['telephone']) ?></td>
                        <td class="adresse-col"><?= htmlspecialchars($client['adresse']) ?></td>
                        <td class="date-col"><?= htmlspecialchars($client['date_creation']) ?></td>
                        <td class="actions-cell">
                            <a href="clients.php?edit=<?= $client['id'] ?>" class="btn btn-green btn-edit">Modifier</a>
                            <a href="clients.php?delete=<?= $client['id'] ?>" class="btn btn-red btn-delete">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($clients)): ?>
                    <tr><td colspan="7" style="text-align:center;">Aucun client enregistré.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
    <?php if ($total_pages > 1): ?>
    <nav style="margin-top:1.5em; display:flex; justify-content:center; gap:0.5em; flex-wrap:wrap;">
        <?php
        $url_base = 'clients.php?';
        $params_url = $_GET;
        foreach(['page'] as $unset) unset($params_url[$unset]);
        $url_base .= http_build_query($params_url);
        if ($url_base !== 'clients.php?' && substr($url_base, -1) !== '&' && substr($url_base, -1) !== '?') $url_base .= '&';
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
<script>
// Confirmation JS plus explicite
const editBtns = document.querySelectorAll('.btn-edit');
editBtns.forEach(btn => btn.addEventListener('click', function(e) {
    if (!confirm('Voulez-vous vraiment modifier ce client ?')) e.preventDefault();
}));
const deleteBtns = document.querySelectorAll('.btn-delete');
deleteBtns.forEach(btn => btn.addEventListener('click', function(e) {
    if (!confirm('Voulez-vous vraiment supprimer ce client ? Cette action est irréversible.')) e.preventDefault();
}));
</script>
<?php include '../includes/footer.php'; ?> 