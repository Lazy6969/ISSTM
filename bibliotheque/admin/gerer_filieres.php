<?php
include_once '../../language.php';
require_once '../../db_connect.php';
require '../config.php';
require '../functions.php';
requireAdmin();

$page_title = t('bib_gerer_filieres_titre');
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter_mention'])) {
        $nom = trim($_POST['nom_mention'] ?? '');
        $abrev = trim($_POST['abreviation_mention'] ?? '');
        if ($nom !== '' && $abrev !== '') {
            $stmt = $pdo->prepare("INSERT IGNORE INTO mentions (nom, abreviation) VALUES (?, ?)");
            $stmt->execute([$nom, $abrev]);
            $succes = t('bib_succes_mention_ajoutee');
        }
    } elseif (isset($_POST['ajouter_filiere'])) {
        $nom = trim($_POST['nom'] ?? '');
        $abrev = trim($_POST['abreviation'] ?? '');
        $niveau = $_POST['niveau'] ?? '';
        $mention_id = $_POST['mention_id'] ?? '';
        if ($nom !== '' && $abrev !== '' && in_array($niveau, ['Licence', 'Master']) && $mention_id) {
            $stmt = $pdo->prepare("INSERT INTO filieres (nom, abreviation, niveau, mention_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nom, $abrev, $niveau, $mention_id]);
            $succes = t('bib_succes_filiere_ajoutee');
        }
    } elseif (isset($_POST['ajouter_annee'])) {
        $libelle = trim($_POST['libelle'] ?? '');
        if (preg_match('/^\d{4}-\d{4}$/', $libelle)) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO annees_universitaires (libelle) VALUES (?)");
            $stmt->execute([$libelle]);
            $succes = t('bib_succes_annee_ajoutee');
        }
    } elseif (isset($_POST['supprimer_mention'])) {
        // Supprime aussi les filières et mémoires rattachés (cascade)
        $pdo->prepare("DELETE FROM mentions WHERE id = ?")->execute([$_POST['supprimer_mention']]);
    } elseif (isset($_POST['supprimer_filiere'])) {
        $pdo->prepare("DELETE FROM filieres WHERE id = ?")->execute([$_POST['supprimer_filiere']]);
    } elseif (isset($_POST['supprimer_annee'])) {
        $pdo->prepare("DELETE FROM annees_universitaires WHERE id = ?")->execute([$_POST['supprimer_annee']]);
    }
}

$mentions = $pdo->query("SELECT * FROM mentions ORDER BY nom")->fetchAll();
$filieres = $pdo->query("SELECT f.*, m.nom AS mention_nom, m.abreviation AS mention_abrev
                          FROM filieres f
                          JOIN mentions m ON m.id = f.mention_id
                          ORDER BY m.nom, f.niveau, f.nom")->fetchAll();
$annees = $pdo->query("SELECT * FROM annees_universitaires ORDER BY libelle DESC")->fetchAll();

require '../../header.php';
?>

<a href="dashboard.php" class="btn-outline btn-back-sticky"><i class="fas fa-arrow-left"></i> <?php echo t('admin_retour_tableau_bord'); ?></a>

<div class="page-banner search-banner">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
            <a href="<?php echo SITE_URL; ?>/index.php"><i class="fas fa-house"></i> <?php echo t('accueil'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="dashboard.php"><?php echo t('bib_admin_dashboard_titre'); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo t('bib_gerer_filieres_titre'); ?></span>
        </nav>
        <h1><i class="fas fa-school"></i> <?php echo t('bib_gerer_filieres_titre'); ?></h1>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <?php if ($succes): ?><div class="admin-flash admin-flash-success"><i class="fas fa-circle-check"></i> <?php echo e($succes); ?></div><?php endif; ?>

        <div class="bib-colonnes">
            <section>
                <h2 class="admin-section-title"><?php echo t('bib_mentions_titre'); ?></h2>
                <form method="post" class="admin-form bib-form-inline">
                    <div class="form-group">
                        <input type="text" name="nom_mention" placeholder="<?php echo t('bib_nom_mention_placeholder'); ?>" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="abreviation_mention" placeholder="<?php echo t('bib_abreviation_placeholder'); ?>" required>
                    </div>
                    <button type="submit" name="ajouter_mention" class="btn-add-item"><i class="fas fa-plus"></i> <?php echo t('bib_ajouter_mention'); ?></button>
                </form>
                <ul class="bib-liste-admin">
                    <?php foreach ($mentions as $m): ?>
                        <li>
                            <span><?php echo e($m['nom']); ?> <strong>(<?php echo e($m['abreviation']); ?>)</strong></span>
                            <form method="post" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('bib_confirmer_suppression_mention')); ?>">
                                <input type="hidden" name="supprimer_mention" value="<?php echo $m['id']; ?>">
                                <button type="submit" class="btn-delete btn-delete-sm" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-xmark"></i></button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                    <?php if (!$mentions): ?><li class="bib-liste-vide"><?php echo t('bib_aucune_mention'); ?></li><?php endif; ?>
                </ul>

                <h2 class="admin-section-title"><?php echo t('bib_annees_titre'); ?></h2>
                <form method="post" class="admin-form bib-form-inline">
                    <div class="form-group">
                        <input type="text" name="libelle" placeholder="<?php echo t('bib_annee_placeholder'); ?>" pattern="\d{4}-\d{4}" required>
                    </div>
                    <button type="submit" name="ajouter_annee" class="btn-add-item"><i class="fas fa-plus"></i> <?php echo t('bib_ajouter'); ?></button>
                </form>
                <ul class="bib-liste-admin">
                    <?php foreach ($annees as $a): ?>
                        <li>
                            <span><?php echo e($a['libelle']); ?></span>
                            <form method="post" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('bib_confirmer_suppression_annee')); ?>">
                                <input type="hidden" name="supprimer_annee" value="<?php echo $a['id']; ?>">
                                <button type="submit" class="btn-delete btn-delete-sm" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-xmark"></i></button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                    <?php if (!$annees): ?><li class="bib-liste-vide"><?php echo t('bib_aucune_annee'); ?></li><?php endif; ?>
                </ul>
            </section>

            <section>
                <h2 class="admin-section-title"><?php echo t('bib_filieres_specialites_titre'); ?></h2>
                <form method="post" class="admin-form bib-form-inline">
                    <div class="form-group">
                        <input type="text" name="nom" placeholder="<?php echo t('bib_nom_filiere_placeholder'); ?>" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="abreviation" placeholder="<?php echo t('bib_abreviation_placeholder'); ?>" required>
                    </div>
                    <div class="form-group">
                        <select name="niveau" required>
                            <option value="Licence"><?php echo t('bib_licence'); ?></option>
                            <option value="Master"><?php echo t('bib_master'); ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <select name="mention_id" required>
                            <option value=""><?php echo t('bib_mention'); ?></option>
                            <?php foreach ($mentions as $m): ?>
                                <option value="<?php echo $m['id']; ?>"><?php echo e($m['nom']); ?> (<?php echo e($m['abreviation']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="ajouter_filiere" class="btn-add-item"><i class="fas fa-plus"></i> <?php echo t('bib_ajouter_filiere'); ?></button>
                </form>

                <?php $mentionCourante = null; foreach ($filieres as $f): ?>
                    <?php if ($mentionCourante !== $f['mention_nom']): $mentionCourante = $f['mention_nom']; ?>
                        <p class="bib-sous-groupe-titre"><strong><?php echo e($f['mention_nom']); ?> (<?php echo e($f['mention_abrev']); ?>)</strong></p>
                    <?php endif; ?>
                    <ul class="bib-liste-admin bib-liste-compacte">
                        <li>
                            <span><?php echo e($f['niveau']); ?> - <?php echo e($f['nom']); ?> (<?php echo e($f['abreviation']); ?>)</span>
                            <form method="post" class="js-confirm-submit" data-confirm-msg="<?php echo htmlspecialchars(t('bib_confirmer_suppression_filiere')); ?>">
                                <input type="hidden" name="supprimer_filiere" value="<?php echo $f['id']; ?>">
                                <button type="submit" class="btn-delete btn-delete-sm" title="<?php echo t('admin_supprimer'); ?>"><i class="fas fa-xmark"></i></button>
                            </form>
                        </li>
                    </ul>
                <?php endforeach; ?>
                <?php if (!$filieres): ?><p class="bib-liste-vide"><?php echo t('bib_aucune_filiere'); ?></p><?php endif; ?>
            </section>
        </div>

    </div>
</div>

<?php require '../../footer.php'; ?>
