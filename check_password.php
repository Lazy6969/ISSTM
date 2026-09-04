<?php

echo "<pre>"; // Pour un affichage plus lisible

require_once 'db_connect.php';

$admin_email = 'mirindra@gmail.com';
$password_to_check = 'mirindra123';

echo "Vérification du mot de passe pour l'utilisateur : " . htmlspecialchars($admin_email) . "\n";
echo "Mot de passe testé : '" . htmlspecialchars($password_to_check) . "'\n\n";

// 1. Récupérer le hachage depuis la base de données
$sql = "SELECT mot_de_passe FROM utilisateurs WHERE email = ?";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("s", $admin_email);
    $stmt->execute();
    $stmt->bind_result($hash_from_db);
    $stmt->fetch();
    $stmt->close();

    if ($hash_from_db) {
        echo "Hachage trouvé dans la BDD : " . htmlspecialchars($hash_from_db) . "\n\n";

        // 2. Utiliser password_verify() pour comparer
        if (password_verify($password_to_check, $hash_from_db)) {
            echo "✅ SUCCÈS : Le mot de passe est correct et le hachage est valide ! La connexion devrait fonctionner.";
        } else {
            echo "❌ ERREUR : Le mot de passe est incorrect ou le hachage dans la base de données n'est pas le bon.";
        }
    } else {
        echo "❌ ERREUR : Aucun utilisateur trouvé avec l'email '" . htmlspecialchars($admin_email) . "'.";
    }
}
$mysqli->close();
echo "</pre>";