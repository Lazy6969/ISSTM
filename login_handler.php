<?php
require_once 'db_connect.php';
session_start(); // La session est nécessaire pour stocker les infos de l'utilisateur

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['username'] ?? ''; // Le champ du formulaire s'appelle 'username' mais contient l'email
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header('Location: login.php?error=2'); // Erreur 2: champs vides
        exit;
    }

    // Préparer une requête pour éviter les injections SQL
    $sql = "SELECT id, nom, email, mot_de_passe, role, avatar_path, is_scolarite FROM utilisateurs WHERE email = ?";

    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("s", $email);

        if ($stmt->execute()) {
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $nom, $db_email, $hashed_password_from_db, $role, $avatar_path, $is_scolarite);
                $stmt->fetch();

                // C'est ici que la magie opère : on vérifie le mot de passe tapé avec celui haché dans la BDD
                if (password_verify($password, $hashed_password_from_db)) {
                    // Mot de passe correct, on démarre la session
                    session_regenerate_id(true); // Sécurité : régénère l'ID de session
                    $_SESSION["user_logged_in"] = true;
                    $_SESSION["user_id"] = $id;
                    $_SESSION["user_email"] = $db_email;
                    $_SESSION["user_nom"] = $nom;
                    $_SESSION["user_role"] = $role;
                    $_SESSION["user_avatar"] = $avatar_path;
                    $_SESSION["user_is_scolarite"] = (bool) $is_scolarite;

                    // Redirection en fonction du rôle
                    if ($role === 'admin') {
                        header('Location: administrateur.php');
                    } elseif ($role === 'materiel') {
                        header('Location: admin_materiel.php');
                    } elseif ($is_scolarite) {
                        header('Location: admin_etudiants.php');
                    } elseif ($role === 'enseignant') {
                        header('Location: mes_groupes.php');
                    } else {
                        header('Location: profil.php');
                    }
                    exit;
                } else {
                    // Le mot de passe est incorrect
                    header('Location: login.php?error=1');
                    exit;
                }
            } else {
                // Pas trouvé comme utilisateur ISSTM : on tente le compte administrateur de la
                // bibliothèque (base "bibliotheque", séparée de isstm_db), afin de n'avoir qu'un
                // seul point d'entrée de connexion sur tout le site. Les clés de session posées
                // (admin_id / admin_username / admin_role) sont celles attendues par
                // bibliotheque/functions.php::isAdminLogged(), inchangées par ailleurs.
                $stmt->close();

                $bibMysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, 'bibliotheque');
                $bibStmt = $bibMysqli->prepare("SELECT id, username, password, avatar_path, role FROM admins WHERE username = ?");
                $bibStmt->bind_param('s', $email);
                $bibStmt->execute();
                $bibAdmin = $bibStmt->get_result()->fetch_assoc();
                $bibStmt->close();
                $bibMysqli->close();

                if ($bibAdmin && password_verify($password, $bibAdmin['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = $bibAdmin['id'];
                    $_SESSION['admin_username'] = $bibAdmin['username'];
                    $_SESSION['admin_avatar'] = $bibAdmin['avatar_path'];
                    $_SESSION['admin_role'] = $bibAdmin['role'];
                    header('Location: bibliotheque/admin/dashboard.php');
                    exit;
                }

                header('Location: login.php?error=1');
                exit;
            }
        }
        $stmt->close();
    }
    $mysqli->close();
}

// Si on arrive ici, c'est que la méthode n'est pas POST ou qu'il y a eu un autre problème.
header('Location: index.php');
exit;