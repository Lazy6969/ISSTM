<?php
require_once 'db_connect.php';
require_once 'groupe_functions.php';

$groupe_id = (int) ($_GET['groupe_id'] ?? 0);
[$self, $groupe, $membership] = groupe_require_membership($mysqli, $groupe_id, false);

if (!groupe_can_download_presence($membership)) {
    header('Location: groupe_chat.php?id=' . $groupe_id);
    exit;
}

$session_id = isset($_GET['session_id']) ? (int) $_GET['session_id'] : 0;

if ($session_id > 0) {
    $sessions = $mysqli->query("SELECT id, session_date FROM groupe_presence_sessions WHERE id = $session_id AND groupe_id = " . (int) $groupe_id)->fetch_all(MYSQLI_ASSOC);
} else {
    $sessions = $mysqli->query("SELECT id, session_date FROM groupe_presence_sessions WHERE groupe_id = " . (int) $groupe_id . " ORDER BY session_date ASC")->fetch_all(MYSQLI_ASSOC);
}

foreach ($sessions as &$s) {
    $marks = $mysqli->query("SELECT u.nom, m.status FROM groupe_presence_marks m JOIN utilisateurs u ON u.id = m.user_id WHERE m.session_id = " . (int) $s['id'] . " ORDER BY u.nom ASC")->fetch_all(MYSQLI_ASSOC);
    $s['marks'] = $marks;
}
unset($s);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($groupe['nom']); ?> — Liste de présence</title>
<style>
    body { font-family: Arial, Helvetica, sans-serif; color: #222; max-width: 800px; margin: 30px auto; padding: 0 20px; }
    header.print-header { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #d4a017; padding-bottom: 12px; margin-bottom: 24px; }
    header.print-header h1 { font-size: 1.3rem; margin: 0; }
    header.print-header p { margin: 4px 0 0; color: #555; font-size: 0.9rem; }
    .print-btn { background: #d4a017; color: #fff; border: none; padding: 10px 18px; border-radius: 6px; cursor: pointer; font-size: 0.95rem; }
    .session-block { margin-bottom: 28px; page-break-inside: avoid; }
    .session-block h2 { font-size: 1.05rem; border-bottom: 1px solid #ccc; padding-bottom: 6px; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th, td { text-align: left; padding: 6px 10px; border-bottom: 1px solid #eee; font-size: 0.92rem; }
    th { background: #faf5e8; }
    .status-present { color: #1f8a3b; font-weight: 600; }
    .status-absent { color: #c0392b; font-weight: 600; }
    .empty { color: #777; font-style: italic; }
    @media print {
        .print-btn { display: none; }
    }
</style>
</head>
<body>
    <header class="print-header">
        <div>
            <h1><?php echo htmlspecialchars($groupe['nom']); ?> — Liste de présence</h1>
            <p>Institut Supérieur des Sciences et Technologies de Mahajanga</p>
        </div>
        <button class="print-btn" onclick="window.print();">🖨 Imprimer</button>
    </header>

    <?php if (empty($sessions)): ?>
        <p class="empty">Aucune séance enregistrée.</p>
    <?php else: ?>
        <?php foreach ($sessions as $s): ?>
            <div class="session-block">
                <h2>Séance du <?php echo date('d/m/Y', strtotime($s['session_date'])); ?></h2>
                <?php if (empty($s['marks'])): ?>
                    <p class="empty">Aucun étudiant enregistré pour cette séance.</p>
                <?php else: ?>
                    <table>
                        <thead><tr><th>Nom</th><th>Statut</th></tr></thead>
                        <tbody>
                            <?php foreach ($s['marks'] as $m): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($m['nom']); ?></td>
                                    <td class="status-<?php echo $m['status']; ?>"><?php echo $m['status'] === 'present' ? 'Présent' : 'Absent'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
