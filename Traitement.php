<?php

// --- 1. Configuration de la base de données ---
$host = 'localhost'; // Habituellement 'localhost' pour un serveur local
$dbname = 'sunu_feedback'; // Le nom de la base de données que nous venons de créer
$username = 'noura_keita'; // Votre nom d'utilisateur phpMyAdmin (par défaut 'root' pour XAMPP/WAMP)
$password = 'Kassoum08@'; // Votre mot de passe phpMyAdmin (par défaut vide pour XAMPP/WAMP)

// --- 2. Connexion à la base de données ---
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // En cas d'erreur de connexion, arrêtez le script et affichez un message
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// --- 3. Vérification si le formulaire a été soumis via POST ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- 4. Récupération et nettoyage des données du formulaire ---
    // Utilisez htmlspecialchars pour prévenir les attaques XSS et trim pour nettoyer les espaces
    $nom = htmlspecialchars(trim($_POST['nom']));
    $prenom = htmlspecialchars(trim($_POST['prenom']));
    $numero_telephone = htmlspecialchars(trim($_POST['numero'])); // Numéro sans le préfixe +225 initialement
    $service_choisi_nom = htmlspecialchars(trim($_POST['id_service'])); // C'est l'ID du service envoyé par le formulaire
    $qualite_accueil = htmlspecialchars(trim($_POST['satisfaction']));
    $bien_informe = htmlspecialchars(trim($_POST['information']));
    $temps_attente = htmlspecialchars(trim($_POST['attente']));
    $recommandation = htmlspecialchars(trim($_POST['recommandation']));
    $justification = htmlspecialchars(trim($_POST['justification']));
    $commentaire = htmlspecialchars(trim($_POST['commentaire']));

    // Ajout du préfixe +225 au numéro de téléphone pour la base de données si nécessaire
    // Si votre input n'inclut pas le +225, ajoutez-le ici
    if (strpos($numero_telephone, '+225') !== 0) { // Vérifie si le préfixe n'est pas déjà présent
        $numero_telephone_db = '+225' . $numero_telephone;
    } else {
        $numero_telephone_db = $numero_telephone;
    }

    try {
        // --- 5. Gérer la table 'clients' ---
        // Vérifier si le client existe déjà basé sur le numéro de téléphone
        $stmt = $pdo->prepare("SELECT id_client FROM clients WHERE numero_telephone = :numero_telephone");
        $stmt->bindParam(':numero_telephone', $numero_telephone_db);
        $stmt->execute();
        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        $id_client = null;
        if ($client) {
            // Le client existe, récupérez son ID
            $id_client = $client['id_client'];
        } else {
            // Le client n'existe pas, insérez-le
            $stmt = $pdo->prepare("INSERT INTO clients (nom_client, prenom_client, numero_telephone) VALUES (:nom, :prenom, :numero_telephone)");
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':numero_telephone', $numero_telephone_db);
            $stmt->execute();
            $id_client = $pdo->lastInsertId(); // Récupérez l'ID du nouveau client inséré
        }

        // Vérifier si nous avons bien un id_client
        if (is_null($id_client)) {
            throw new Exception("Impossible d'obtenir l'ID du client.");
        }

        // --- 6. Récupérer l'ID du service à partir de l'ID envoyé par le formulaire ---
        // Le formulaire envoie déjà l'id_service (1, 2, 3, etc.)
        $id_service_feedback = (int)$service_choisi_nom; // Convertir en entier pour s'assurer du type correct

        // Optionnel mais recommandé : Vérifier que l'id_service existe dans la table 'services'
        $stmt = $pdo->prepare("SELECT id_service FROM services WHERE id_service = :id_service");
        $stmt->bindParam(':id_service', $id_service_feedback, PDO::PARAM_INT);
        $stmt->execute();
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            throw new Exception("L'ID du service soumis est invalide.");
        }


        // --- 7. Insérer le feedback dans la table 'feedbacks' ---
        $sql = "INSERT INTO feedbacks (
                    id_client,
                    id_service,
                    qualite_accueil,
                    bien_informe,
                    temps_attente,
                    recommandation,
                    justification,
                    commentaire
                ) VALUES (
                    :id_client,
                    :id_service,
                    :qualite_accueil,
                    :bien_informe,
                    :temps_attente,
                    :recommandation,
                    :justification,
                    :commentaire
                )";

        $stmt = $pdo->prepare($sql);

        // Liaison des valeurs
        $stmt->bindParam(':id_client', $id_client, PDO::PARAM_INT);
        $stmt->bindParam(':id_service', $id_service_feedback, PDO::PARAM_INT);
        $stmt->bindParam(':qualite_accueil', $qualite_accueil);
        $stmt->bindParam(':bien_informe', $bien_informe);
        $stmt->bindParam(':temps_attente', $temps_attente);
        $stmt->bindParam(':recommandation', $recommandation);
        $stmt->bindParam(':justification', $justification);
        $stmt->bindParam(':commentaire', $commentaire);

        // Exécution de la requête d'insertion du feedback
        $stmt->execute();

        // --- 8. Redirection vers une page de succès ---
        header("Location: succes.html");
        exit();

    } catch (Exception $e) {
        // Gérer les erreurs (ex: afficher un message d'erreur à l'utilisateur)
        echo "Une erreur est survenue lors du traitement de votre feedback : " . $e->getMessage();
        // Pour le débogage, vous pouvez afficher plus de détails :
        error_log("Erreur dans traitement.php: " . $e->getMessage()); // Pour logguer l'erreur sur le serveur
    }

} else {
    // Si le formulaire n'a pas été soumis via POST, redirigez ou affichez un message
    echo "Accès non autorisé.";
    // header("Location: index.html"); // Rediriger vers le formulaire
    // exit();
}

?>