<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../includes/functions.php';
    if (!isset($_POST['csrf_token']) || !check_csrf_token($_POST['csrf_token'])) {
        $error = "Erreur de sécurité : le formulaire a expiré ou est invalide.";
    } else {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $stmt = mysqli_prepare($conn, "SELECT id, password, nom FROM admin WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $id, $hash, $nom);
        if (mysqli_stmt_fetch($stmt)) {
            if (password_verify($password, $hash)) {
                $_SESSION['admin_id'] = $id;
                $_SESSION['admin_nom'] = $nom;
                $_SESSION['admin_email'] = $email;
                mysqli_stmt_close($stmt); // fermer AVANT enregistrer_action
                enregistrer_action($conn, "Connexion réussie", $email);
                header('Location: dashboard.php');
                exit;
            } else {
                $error = "Mot de passe incorrect.";
            }
        } else {
            $error = "Email non trouvé.";
        }
        if ($stmt) mysqli_stmt_close($stmt); // sécurité
    }
}
?>
<?php include '../includes/header.php'; ?>
<style>
.login-bg {
    min-height: 100vh;
    background: linear-gradient(135deg, #1F2937 60%, #10B981 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}
.login-container {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 4px 32px rgba(31,41,55,0.18);
    padding: 2.5em 2em 2em 2em;
    max-width: 370px;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}
.login-container h2 {
    text-align: center;
  color: var(--main-blue);
  font-size: 2.1em;
  font-weight: 700;
  margin-bottom: 1.2em;
  letter-spacing: 1px;
  line-height: 1.2;
  text-shadow: 0 2px 8px rgba(31,41,55,0.07);
}
.login-container form {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 1.1em;
}
.login-container input[type="email"],
.login-container input[type="password"] {
    padding: 0.9em 1em;
    border: 1.5px solid #1F2937;
    border-radius: 7px;
    font-size: 1em;
    background: #F3F4F6;
    color: #1F2937;
    transition: border 0.2s;
    max-width: 335px;
}
.login-container h2 .admini {
  color: #1F2937;
}
.login-container h2 .strateur {
  color: #10B981;
}
.login-container input[type="email"]:focus,
.login-container input[type="password"]:focus {
    border: 1.5px solid #10B981;
    outline: none;
}
.login-container button[type="submit"] {
    background: #10B981;
    color: #fff;
    border: none;
    border-radius: 7px;
    padding: 0.9em 0;
    font-size: 1.1em;
    font-weight: 600;
    cursor: pointer;
    margin-top: 0.5em;
    box-shadow: 0 2px 8px rgba(16,185,129,0.08);
    transition: background 0.2s;
}
.login-container button[type="submit"]:hover {
    background: #1F2937;
    color: #10B981;
}
.login-container .error {
    background: #1F2937;
    color: #fff;
    border-left: 4px solid #10B981;
    padding: 0.7em 1em;
    border-radius: 6px;
    margin-bottom: 1em;
    width: 100%;
    text-align: left;
    font-size: 1em;
}
.login-container .default-cred {
    color: #888;
    font-size: 0.95em;
    margin-top: 0.7em;
    margin-bottom: 0.5em;
    text-align: left;
    width: 100%;
}
.login-container .back-home {
    display: block;
    margin: 2em auto 0 auto;
    background: #1F2937;
    color: #fff;
    border: none;
    border-radius: 7px;
    padding: 0.8em 0;
    width: 100%;
    max-width: 335px;
    font-size: 1em;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(31,41,55,0.08);
    transition: background 0.2s, color 0.2s;
    box-sizing: border-box;
}
.login-container .back-home:hover {
    background: #10B981;
    color: #fff;
}

/* Responsive mobile */
@media (max-width: 768px) {
    .login-bg {
        padding: 1em;
        min-height: 100vh;
    }
    .login-container {
        padding: 2em 1.5em 1.5em 1.5em;
        max-width: 100%;
        border-radius: 12px;
        margin: 0;
    }
    .login-container h2 {
        font-size: 1.8em;
        margin-bottom: 1em;
    }
    .login-container input[type="email"],
    .login-container input[type="password"] {
        padding: 1em;
        font-size: 16px; /* Évite le zoom sur iOS */
        max-width: 100%;
    }
    .login-container button[type="submit"] {
        padding: 1em;
        font-size: 1em;
        min-height: 44px;
    }
    .login-container .back-home {
        padding: 1em;
        font-size: 1em;
        min-height: 44px;
        margin-top: 1.5em;
        max-width: 100%;
        width: 100%;
    }
    .login-container .error {
        padding: 1em;
        font-size: 0.95em;
    }
    .login-container .default-cred {
        font-size: 0.9em;
        margin-top: 0.5em;
    }
}

@media (max-width: 480px) {
    .login-bg {
        padding: 0.5em;
    }
    .login-container {
        padding: 1.5em 1.2em 1.2em 1.2em;
    }
    .login-container h2 {
        font-size: 1.6em;
    }
    .login-container input[type="email"],
    .login-container input[type="password"] {
        padding: 0.9em;
    }
    .login-container button[type="submit"] {
        padding: 0.9em;
    }
    .login-container .back-home {
        padding: 0.9em;
        max-width: 100%;
        width: 100%;
    }
}
</style>
<div class="login-bg">
    <div class="login-container">
        <h2>Connexion <span class="admini">admini</span><span class="strateur">strateur</span></h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
            <div class="default-cred">Si vous avez oublié vos identifiants, contactez l'administrateur du site.</div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
            <input type="email" name="email" placeholder="Email" required autocomplete="username">
            <input type="password" name="password" placeholder="Mot de passe" required autocomplete="current-password">
            <button type="submit">Se connecter</button>
        </form>
        <a href="../index.php" class="back-home">&#8592; Retour à l'accueil</a>
    </div>
</div>
<?php include '../includes/footer.php'; ?> 