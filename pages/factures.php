<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../vendor/fpdf/fpdf.php';
require_once '../includes/functions.php';

// Utilitaire pour obtenir le nom de fichier PDF au bon format
function get_nom_pdf_facture($id) {
    // FAC-0006/2025 => FAC-0006-2025.pdf
    return '../assets/factures/' . str_replace('/', '-', get_numero_facture_formate($id)) . '.pdf';
}

// Suppression d'une facture (id quelconque)
if (isset($_GET['delete_facture']) && is_numeric($_GET['delete_facture'])) {
    $id = intval($_GET['delete_facture']);
    // Supprimer les paiements liés
    mysqli_query($conn, "DELETE FROM paiements WHERE facture_id=$id");
    // Supprimer les lignes liées
    mysqli_query($conn, "DELETE FROM lignes WHERE facture_id=$id");
    // Supprimer la facture
    mysqli_query($conn, "DELETE FROM factures WHERE id=$id");
    // Supprimer le PDF associé si présent
    $pdf_path = get_nom_pdf_facture($id);
    if (file_exists($pdf_path)) unlink($pdf_path);
    header('Location: factures.php?success=Facture+supprim%C3%A9e');
    exit;
}

// Gestion de la régénération PDF (doit être AVANT tout affichage)
if (isset($_GET['regen_pdf']) && is_numeric($_GET['regen_pdf'])) {
    $facture_id = intval($_GET['regen_pdf']);
    $pdf_path = get_nom_pdf_facture($facture_id);
    generate_facture_pdf($conn, $facture_id, $pdf_path);
    header('Location: factures.php?success=PDF+reg%C3%A9n%C3%A9r%C3%A9+avec+succ%C3%A8s');
    exit;
}

// Marquer une facture comme payée
if (isset($_GET['pay']) && is_numeric($_GET['pay'])) {
    $facture_id = intval($_GET['pay']);
    $date = date('Y-m-d');
    // Récupérer le montant TTC
    $res = mysqli_query($conn, "SELECT total_ttc FROM factures WHERE id=$facture_id");
    $row = mysqli_fetch_assoc($res);
    $montant = $row ? $row['total_ttc'] : 0;
    // Insérer le paiement
    $stmt = mysqli_prepare($conn, "INSERT INTO paiements (facture_id, date_paiement, montant) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isd", $facture_id, $date, $montant);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    // Mettre à jour le statut de la facture
    mysqli_query($conn, "UPDATE factures SET statut='payée' WHERE id=$facture_id");
    enregistrer_action($conn, "Paiement enregistré pour la facture #$facture_id (montant : $montant €)", $_SESSION['admin_email']);
    // Régénérer le PDF automatiquement
    $pdf_path = get_nom_pdf_facture($facture_id);
    generate_facture_pdf($conn, $facture_id, $pdf_path);
    mysqli_query($conn, "UPDATE factures SET pdf_path='$pdf_path' WHERE id=$facture_id");
    header('Location: factures.php');
    exit;
}

// Création d'une facture à partir d'un devis
if (isset($_GET['from_devis'])) {
    $devis_id = intval($_GET['from_devis']);
    $res_devis = mysqli_query($conn, "SELECT * FROM devis WHERE id=$devis_id");
    $devis = mysqli_fetch_assoc($res_devis);
    if ($devis) {
        $stmt = mysqli_prepare($conn, "INSERT INTO factures (devis_id, client_id, total_ht, total_tva, total_ttc) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iiddd", $devis_id, $devis['client_id'], $devis['total_ht'], $devis['total_tva'], $devis['total_ttc']);
        mysqli_stmt_execute($stmt);
        $facture_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        $res_lignes = mysqli_query($conn, "SELECT * FROM lignes WHERE devis_id=$devis_id");
        while ($ligne = mysqli_fetch_assoc($res_lignes)) {
            $stmt = mysqli_prepare($conn, "INSERT INTO lignes (facture_id, produit_id, quantite, prix_unitaire, tva, remise) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iidddd", $facture_id, $ligne['produit_id'], $ligne['quantite'], $ligne['prix_unitaire'], $ligne['tva'], $ligne['remise']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        $pdf_path = get_nom_pdf_facture($facture_id);
        if (!is_dir('../assets/factures')) mkdir('../assets/factures', 0777, true);
        generate_facture_pdf($conn, $facture_id, $pdf_path);
        mysqli_query($conn, "UPDATE factures SET pdf_path='$pdf_path' WHERE id=$facture_id");
        enregistrer_action($conn, "Création de la facture #$facture_id à partir du devis #$devis_id", $_SESSION['admin_email']);
        header('Location: factures.php');
        exit;
    }
}

// Enregistrement d'un paiement (manuel)
if (isset($_POST['pay_facture_id']) && is_numeric($_POST['pay_facture_id'])) {
    $facture_id = intval($_POST['pay_facture_id']);
    $date = $_POST['date_paiement'] ?: date('Y-m-d');
    $montant = floatval($_POST['montant']);
    // Récupérer le montant TTC de la facture
    $res = mysqli_query($conn, "SELECT total_ttc FROM factures WHERE id=$facture_id");
    $row = mysqli_fetch_assoc($res);
    $total_ttc = $row ? floatval($row['total_ttc']) : 0;
    // Validation renforcée
    $error = '';
    if (!is_numeric($_POST['montant']) || $montant <= 0 || $montant > $total_ttc) {
        $error = "Le montant doit être positif et inférieur ou égal au total TTC de la facture.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $error = "La date de paiement doit être au format AAAA-MM-JJ.";
    }
    if (empty($error)) {
        // Insérer le paiement
        $stmt = mysqli_prepare($conn, "INSERT INTO paiements (facture_id, date_paiement, montant) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isd", $facture_id, $date, $montant);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        // Mettre à jour le statut de la facture
        if ($montant >= $total_ttc) {
            mysqli_query($conn, "UPDATE factures SET statut='payée' WHERE id=$facture_id");
        } else {
            mysqli_query($conn, "UPDATE factures SET statut='impayée' WHERE id=$facture_id");
        }
        // Régénérer le PDF automatiquement
        $pdf_path = get_nom_pdf_facture($facture_id);
        generate_facture_pdf($conn, $facture_id, $pdf_path);
        mysqli_query($conn, "UPDATE factures SET pdf_path='$pdf_path' WHERE id=$facture_id");
        enregistrer_action($conn, "Paiement enregistré pour la facture #$facture_id (montant : $montant €)", $_SESSION['admin_email']);
        header('Location: factures.php');
        exit;
    }
}
function generate_facture_pdf($conn, $facture_id, $pdf_path) {
    // Supprimer l'ancien PDF pour éviter le cache
    if (file_exists($pdf_path)) {
        unlink($pdf_path);
    }
    $res = mysqli_query($conn, "SELECT f.*, c.nom, c.adresse, c.email, f.date FROM factures f JOIN clients c ON f.client_id = c.id WHERE f.id=$facture_id");
    $facture = mysqli_fetch_assoc($res);
    $res_lignes = mysqli_query($conn, "SELECT l.*, p.nom AS produit_nom FROM lignes l JOIN produits p ON l.produit_id = p.id WHERE l.facture_id=$facture_id");
    $pdf = new FPDF();
    $pdf->AddPage();
    $logo_path = __DIR__ . '/../assets/images/logo-factureo.png.png'; // logo Factureo
    if (file_exists($logo_path)) {
        $pdf->Image($logo_path, 10, 10, 32, 0, 'PNG');
    }
    $pdf->SetFont('Arial','B',20);
    $pdf->SetTextColor(16,185,129); // vert
    // Numéro formaté FAC-000X/2025
    $numero_formate = get_numero_facture_formate($facture_id);
    $pdf->Cell(0,18,utf8_decode('Facture ' . $numero_formate),0,1,'C');
    $pdf->SetDrawColor(16,185,129);
    $pdf->SetLineWidth(1.5);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(2);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','',12);
    // Encadré infos client
    $pdf->SetFillColor(243,244,246); // gris très clair
    $pdf->SetDrawColor(209,213,219); // gris bordure
    $pdf->SetLineWidth(0.5);
    $pdf->SetXY(10,28);
    $pdf->Cell(120,28,'',1,0,'L',true);
    $pdf->SetXY(12,30);
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(30,8,utf8_decode('Client :'),0,0,'L');
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(80,8,utf8_decode($facture['nom']),0,1,'L');
    $pdf->SetX(12);
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(30,8,utf8_decode('Adresse :'),0,0,'L');
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(80,8,utf8_decode($facture['adresse']),0,1,'L');
    $pdf->SetX(12);
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(30,8,utf8_decode('Email :'),0,0,'L');
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(80,8,utf8_decode($facture['email']),0,1,'L');
    $pdf->Ln(8);
    // Tableau produits/services
    $pdf->SetFont('Arial','B',12);
    $pdf->SetFillColor(243,244,246); // gris clair
    $pdf->SetDrawColor(16,185,129); // vert
    $pdf->Cell(60,10,utf8_decode('Produit/Service'),1,0,'C',true);
    $pdf->Cell(18,10,utf8_decode('Qté'),1,0,'C',true);
    $pdf->Cell(32,10,'PU',1,0,'C',true);
    $pdf->Cell(20,10,'TVA',1,0,'C',true);
    $pdf->Cell(22,10,'Remise',1,0,'C',true);
    $pdf->Cell(32,10,'Total HT',1,1,'C',true);
    $pdf->SetFont('Arial','',12);
    $pdf->SetDrawColor(209,213,219); // gris bordure
    while ($ligne = mysqli_fetch_assoc($res_lignes)) {
        $ht = ($ligne['prix_unitaire'] * $ligne['quantite']) - $ligne['remise'];
        $pdf->Cell(60,10,utf8_decode($ligne['produit_nom']),1,0,'L');
        $pdf->Cell(18,10,$ligne['quantite'],1,0,'C');
        $pdf->Cell(32,10,number_format($ligne['prix_unitaire'],2).' MAD',1,0,'R');
        $pdf->Cell(20,10,number_format($ligne['tva'],2).' %',1,0,'R');
        $pdf->Cell(22,10,number_format($ligne['remise'],2).' MAD',1,0,'R');
        $pdf->Cell(32,10,number_format($ht,2).' MAD',1,1,'R');
    }
    $pdf->Ln(5);
    // Encadré totaux à droite
    $pdf->SetXY(120, $pdf->GetY());
    $pdf->SetFont('Arial','',12);
    $pdf->SetFillColor(255,255,255);
    $pdf->SetDrawColor(209,213,219);
    $pdf->Cell(80,10,'Total HT : '.number_format($facture['total_ht'],2).' MAD',1,1,'R',true);
    $pdf->SetX(120);
    $pdf->Cell(80,10,'Total TVA : '.number_format($facture['total_tva'],2).' MAD',1,1,'R',true);
    $pdf->SetX(120);
    $pdf->SetFont('Arial','B',14);
    $pdf->SetTextColor(16,185,129);
    $pdf->Cell(80,12,'Total TTC : '.number_format($facture['total_ttc'],2).' MAD',1,1,'R',true);
    $pdf->SetTextColor(0,0,0);
    $pdf->Ln(10);
    // Message de remerciement
    $pdf->SetDrawColor(16,185,129);
    $pdf->SetLineWidth(0.7);
    $y = $pdf->GetY();
    $pdf->Line(10, $y, 200, $y);
    $pdf->Ln(3);
    $pdf->SetFont('Arial','B',12);
    $pdf->SetTextColor(16,185,129);
    $pdf->Cell(0,10,utf8_decode('Merci pour votre confiance !'),0,1,'C');
    $pdf->SetTextColor(0,0,0);
    $pdf->Output('F', $pdf_path);
    file_put_contents(__DIR__.'/debug_facture_pdf.txt', 'ID: '.$facture_id.' | '.date('Y-m-d H:i:s').PHP_EOL, FILE_APPEND);
}

// Liste des factures avec paiement
// --- Recherche et filtrage ---
$where = [];
$params = [];
if (!empty($_GET['q'])) {
    $q = mysqli_real_escape_string($conn, $_GET['q']);
    $where[] = "(f.id LIKE '%$q%' OR c.nom LIKE '%$q%' OR c.email LIKE '%$q%')";
}
if (!empty($_GET['statut'])) {
    $statut = mysqli_real_escape_string($conn, $_GET['statut']);
    $where[] = "f.statut = '$statut'";
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
// --- Pagination ---
$factures_par_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? intval($_GET['page']) : 1;
// Compter le total pour la pagination
$sql_count = "SELECT COUNT(*) as total FROM factures f LEFT JOIN clients c ON f.client_id = c.id $where_sql";
$res_count = mysqli_query($conn, $sql_count);
$total_factures = mysqli_fetch_assoc($res_count)['total'];
$total_pages = ceil($total_factures / $factures_par_page);
$offset = ($page - 1) * $factures_par_page;
$sql = "SELECT f.*, c.nom AS client_nom, p.date_paiement, p.montant FROM factures f LEFT JOIN clients c ON f.client_id = c.id LEFT JOIN paiements p ON f.id = p.facture_id $where_sql ORDER BY f.id ASC LIMIT $factures_par_page OFFSET $offset";
$res = mysqli_query($conn, $sql);
$factures = mysqli_fetch_all($res, MYSQLI_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<style>
.factures-wrapper { min-height: 80vh; display: flex; flex-direction: column; justify-content: flex-start; align-items: center; padding: 2em 0 3em 0; }
.factures-table-section { width: 100%; max-width: 1100px; margin: auto; background: #fff; border-radius: 14px; box-shadow: 0 2px 16px rgba(31,41,55,0.08); padding: 2.5em 2em 2em 2em; }
.factures-table-section h1 { text-align: center; color: #1F2937; margin-bottom: 1.5em; letter-spacing: 1px; font-size: 2em; }
.factures-table-section table { width: 100%; border-collapse: collapse; background: #fff; }
.factures-table-section th, .factures-table-section td { padding: 0.7em 0.5em; text-align: left; }
.factures-table-section th { background: #F3F4F6; }
.factures-table-section tr:nth-child(even) { background: #F9FAFB; }
.paiement-form-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 16px rgba(31,41,55,0.08); padding: 1.2em 1.5em; margin-bottom: 1.5em; display: flex; flex-direction: column; align-items: center; }
.paiement-form-card form { display: flex; flex-direction: column; gap: 0.7em; width: 100%; max-width: 220px; }
@media (max-width: 900px) { .factures-table-section { padding: 1.2em 0.5em; } }
</style>
<div class="factures-wrapper">
    <section class="factures-table-section">
        <h1>Liste des factures</h1>
        <!-- Formulaire de recherche -->
        <form method="get" style="margin-bottom:2em; display:flex; gap:1em; flex-wrap:wrap; align-items:center; justify-content:center;">
            <input type="text" name="q" placeholder="N° facture, client, email..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>" style="padding:0.5em; min-width:200px; border-radius:6px; border:1px solid #ccc;">
            <select name="statut" style="padding:0.5em; border-radius:6px; border:1px solid #ccc;margin-bottom:14px">
                <option value="">Tous statuts</option>
                <option value="payée" <?= (isset($_GET['statut']) && $_GET['statut']==='payée') ? 'selected' : '' ?>>Payée</option>
                <option value="impayée" <?= (isset($_GET['statut']) && $_GET['statut']==='impayée') ? 'selected' : '' ?>>Impayée</option>
            </select>
            <button type="submit" class="btn btn-green" style="padding:0.3em 1.5em; font-size:0.95em; min-width:auto; width:auto; height:42px;">Rechercher</button>
            <?php if (!empty($_GET['q']) || !empty($_GET['statut'])): ?>
                <a href="factures.php" class="btn" style="background:#eee; color:#222; padding:0.3em 1.5em; font-size:0.95em; min-width:auto; width:auto; height:32px;margin-bottom:15px">Réinitialiser</a>
            <?php endif; ?>
        </form>
        <table class="clients-table">
            <thead>
                <tr>
                    <th class="numero-col">N°</th>
                    <th>Client</th>
                    <th class="date-col">Date</th>
                    <th class="prix-col">Total HT (MAD)</th>
                    <th class="prix-col">Total TVA (MAD)</th>
                    <th class="prix-col">Total TTC (MAD)</th>
                    <th class="pdf-col">PDF</th>
                    <th class="statut-col">Statut</th>
                    <th class="date-col">Date paiement</th>
                    <th class="prix-col">Montant payé (MAD)</th>
                    <th class="actions-col">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($factures as $f): ?>
                    <tr>
                        <td class="numero-col"><?= get_numero_facture_formate($f['id']) ?></td>
                        <td><?= htmlspecialchars($f['client_nom']) ?></td>
                        <td class="date-col"><?= htmlspecialchars($f['date']) ?></td>
                        <td class="prix-col"><?= htmlspecialchars($f['total_ht']) ?> MAD</td>
                        <td class="prix-col"><?= htmlspecialchars($f['total_tva']) ?> MAD</td>
                        <td class="prix-col"><?= htmlspecialchars($f['total_ttc']) ?> MAD</td>
                        <td class="pdf-col">
                            <?php 
                                $pdf_path = get_nom_pdf_facture($f['id']);
                                if (file_exists($pdf_path)) : ?>
                                <a href="<?= $pdf_path ?>" target="_blank" class="btn btn-green" style="margin-bottom:0.3em;">Voir PDF</a>
                            <?php else: ?>
                                <a href="factures.php?regen_pdf=<?= $f['id'] ?>" class="btn btn-green">Générer PDF</a>
                            <?php endif; ?>
                        </td>
                        <td class="statut-col">
                            <?php if ($f['statut'] === 'payée'): ?>
                                <span style="color:#10B981;">Payée</span>
                            <?php else: ?>
                                <span style="color:#e53e3e;">Impayée</span>
                            <?php endif; ?>
                        </td>
                        <td class="date-col"><?= $f['date_paiement'] ? htmlspecialchars($f['date_paiement']) : '-' ?></td>
                        <td class="prix-col"><?= $f['montant'] ? htmlspecialchars($f['montant']) . ' MAD' : '-' ?></td>
                        <td class="actions-cell">
                            <?php if ($f['statut'] === 'impayée'): ?>
                                <form method="post" style="display:flex; flex-direction:column; gap:0.3em; min-width:140px; align-items:center;">
                                    <input type="hidden" name="pay_facture_id" value="<?= $f['id'] ?>">
                                    <input type="number" name="montant" min="0.01" step="0.01" value="<?= htmlspecialchars($f['total_ttc']) ?>" placeholder="Montant (€)" required style="width:100%;">
                                    <input type="date" name="date_paiement" value="<?= date('Y-m-d') ?>" required style="width:100%;">
                                    <button type="submit" class="btn btn-green">Enregistrer paiement</button>
                                </form>
                            <?php else: ?>
                                <span style="color:#10B981; font-size:1.3em;">✔</span>
                            <?php endif; ?>
                            <a href="factures.php?delete_facture=<?= $f['id'] ?>" class="btn btn-red" style="margin-top:0.5em;" onclick="return confirm('Voulez-vous vraiment supprimer cette facture ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($factures)): ?>
                    <tr><td colspan="11" style="text-align:center;">Aucune facture enregistrée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav style="margin-top:1.5em; display:flex; justify-content:center; gap:0.5em; flex-wrap:wrap;">
            <?php
            $url_base = 'factures.php?';
            $params_url = $_GET;
            foreach(['page'] as $unset) unset($params_url[$unset]);
            $url_base .= http_build_query($params_url);
            if ($url_base !== 'factures.php?' && substr($url_base, -1) !== '&' && substr($url_base, -1) !== '?') $url_base .= '&';
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
<?php include '../includes/footer.php'; ?> 