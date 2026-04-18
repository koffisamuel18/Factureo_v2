<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

$res = mysqli_query($conn, "SELECT * FROM historique ORDER BY date DESC LIMIT 100");
$actions = mysqli_fetch_all($res, MYSQLI_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<style>
.historique-wrapper { min-height: 80vh; display: flex; flex-direction: column; justify-content: flex-start; align-items: center; padding: 2em 0 3em 0; }
.historique-table-section { width: 100%; max-width: 900px; margin: auto; background: #fff; border-radius: 14px; box-shadow: 0 2px 16px rgba(31,41,55,0.08); padding: 2.5em 2em 2em 2em; }
.historique-table-section h1 { text-align: center; color: #1F2937; margin-bottom: 1.5em; letter-spacing: 1px; font-size: 2em; }
.historique-table-section table { width: 100%; border-collapse: collapse; background: #fff; }
.historique-table-section th, .historique-table-section td { padding: 0.7em 0.5em; text-align: left; }
.historique-table-section th { background: #F3F4F6; }
.historique-table-section tr:nth-child(even) { background: #F9FAFB; }
@media (max-width: 900px) { .historique-table-section { padding: 1.2em 0.5em; } }
</style>
<div class="historique-wrapper">
    <section class="historique-table-section">
        <h1>Historique des actions</h1>
        <table class="clients-table">
            <thead>
                <tr>
                    <th class="date-col">Date</th>
                    <th>Action</th>
                    <th class="utilisateur-col">Utilisateur</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($actions as $a): ?>
                    <tr>
                        <td class="date-col"><?= htmlspecialchars($a['date']) ?></td>
                        <td><?= htmlspecialchars($a['action']) ?></td>
                        <td class="utilisateur-col"><?= htmlspecialchars($a['utilisateur']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($actions)): ?>
                    <tr><td colspan="3" style="text-align:center;">Aucune action enregistrée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>
<?php include '../includes/footer.php'; ?> 