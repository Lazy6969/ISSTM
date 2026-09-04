<?php
include_once 'language.php';
$page_title = t('se_connecter');
include 'header.php';

// Si l'utilisateur est déjà connecté, on le redirige vers son profil ou le dashboard admin
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: administrateur.php');
    } else {
        header('Location: profil.php');
    }
    exit;
}

// Récupérer le message d'erreur depuis l'URL s'il existe
$error_message = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] == 1) {
        $error_message = 'Email ou mot de passe incorrect.';
    } elseif ($_GET['error'] == 2) {
        $error_message = 'Veuillez remplir tous les champs.';
    }
}

?>

<div class="page-banner inscription-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('se_connecter'); ?></span>
        </nav>
        <h1><?php echo t('se_connecter'); ?></h1>
        <p><?php echo t('profil_soustitre'); ?></p>
    </div>
</div>

<div class="page-content login-page-content">
    <div class="container">
        <div class="login-ripple-decor" aria-hidden="true">
            <span class="login-ripple"></span>
            <span class="login-ripple"></span>
            <span class="login-ripple"></span>
            <span class="login-ripple-dot"></span>
        </div>
        <form action="login_handler.php" method="POST" id="login-page-form" class="login-form" autocomplete="off">
            <h2><?php echo t('connexion_admin'); ?></h2>

            <?php if (!empty($error_message)): ?>
                <p class="admin-error" style="display: block;"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>

            <div class="form-group">
                <i class="fas fa-envelope input-icon"></i>
                <input type="text" id="username" name="username" required>
                <label for="username"><?php echo t('nom_utilisateur'); ?></label>
            </div>
            <div class="form-group">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" id="password" name="password" required>
                <label for="password"><?php echo t('mot_de_passe'); ?></label>
            </div>
            <button type="submit" class="btn btn-login"><?php echo t('se_connecter'); ?></button>
            <div class="form-footer">
                <p><a href="mot_de_passe_oublie.php" class="login-forgot-link"><i class="fas fa-key"></i> <?php echo t('mdp_oublie_lien'); ?></a></p>
                <p><?php echo t('pas_de_compte'); ?> <a href="inscription.php"><?php echo t('inscrivez_vous'); ?></a></p>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cible les champs de saisie du formulaire de connexion
    const inputs = document.querySelectorAll('#login-page-form input[type="text"], #login-page-form input[type="password"]');

    inputs.forEach(input => {
        // Ajoute un écouteur d'événement pour le 'focus' (quand on clique dans le champ)
        input.addEventListener('focus', function() {
            // Si le champ a été pré-rempli par le navigateur, il aura l'attribut 'readonly'
            if (this.hasAttribute('readonly')) {
                this.value = ''; // On vide le champ
                this.removeAttribute('readonly'); // On retire l'attribut pour que l'utilisateur puisse taper
            }
        }, { once: true }); // L'option 'once: true' assure que cela ne se produit qu'une seule fois
    });
});
</script>