<?php

// Ce script génère un hachage sécurisé pour un mot de passe donné.

$password_to_hash = 'mirindra123';

$hashed_password = password_hash($password_to_hash, PASSWORD_DEFAULT);

echo "<h1>Hachage pour le mot de passe 'mirindra123'</h1>";
echo "<p>Copiez la ligne ci-dessous et utilisez-la dans votre requête SQL.</p>";
echo "<pre style='background-color:#f0f0f0; padding:15px; font-size:1.2rem; border:1px solid #ccc;'>" . htmlspecialchars($hashed_password) . "</pre>";

?>