<?php

// --- 1. Configuration de la base de données ---
$host = 'localhost';
$dbname = 'sunu_feedback';
$username = 'noura_keita';
$password = 'Kassoum08@';

// --- 2. Connexion à la base de données ---
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// --- 3. Vérification si le formulaire a été soumis via POST ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 4. Récupération et nettoyage des données du formulaire ---
    $nom = htmlspecialchars(trim($_POST['nom']));
    $prenom = htmlspecialchars(trim($_POST['prenom']));
    $numero_telephone = htmlspecialchars(trim($_POST['numero']));
    $id_service_post = htmlspecialchars(trim($_POST['id_service'])); // ID reçu du formulaire
    $qualite_accueil = htmlspecialchars(trim($_POST['satisfaction']));
    $bien_informe = htmlspecialchars(trim($_POST['information']));
    $temps_attente = htmlspecialchars(trim($_POST['attente']));
    $recommandation = htmlspecialchars(trim($_POST['recommandation']));
    $justification = htmlspecialchars(trim($_POST['justification']));
    $commentaire = htmlspecialchars(trim($_POST['commentaire']));

    // Validation simple du numéro de téléphone (ex : uniquement chiffres et longueur entre 8 et 15)
    $numero_sans_prefixe = preg_replace('/\D/', '', $numero_telephone); // Retire tout sauf chiffres
    if (strlen($numero_sans_prefixe) < 8 || strlen($numero_sans_prefixe) > 15) {
        die("Le numéro de téléphone est invalide.");
    }

    // Ajout du préfixe +225 si absent
    if (strpos($numero_telephone, '+225') !== 0) {
        $numero_telephone_db = '+225' . $numero_sans_prefixe;
    } else {
        $numero_telephone_db = $numero_telephone;
    }

    try {
    // --- 5. Gérer la table 'clients' ---
// Chercher un client avec le même numéro, nom et prénom
$stmt = $pdo->prepare("SELECT id_client FROM clients WHERE numero_telephone = :numero_telephone AND nom_client = :nom_client AND prenom_client = :prenom_client");
$stmt->bindParam(':numero_telephone', $numero_telephone_db);
$stmt->bindParam(':nom_client', $nom);
$stmt->bindParam(':prenom_client', $prenom);
$stmt->execute();
$client = $stmt->fetch(PDO::FETCH_ASSOC);

$id_client = null;
if ($client) {
    // Client existant trouvé, on réutilise son id_client
    $id_client = $client['id_client'];
} else {
    // Aucun client trouvé, on en crée un nouveau
    $stmt = $pdo->prepare("INSERT INTO clients (nom_client, prenom_client, numero_telephone) VALUES (:nom, :prenom, :numero_telephone)");
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':prenom', $prenom);
    $stmt->bindParam(':numero_telephone', $numero_telephone_db);
    $stmt->execute();
    $id_client = $pdo->lastInsertId();
}

if (is_null($id_client)) {
    throw new Exception("Impossible d'obtenir l'ID du client.");
}
        // --- 6. Valider et convertir id_service ---
        $id_service_feedback = (int)$id_service_post;

        // Vérifier que l'id_service existe
        $stmt = $pdo->prepare("SELECT id_service FROM services WHERE id_service = :id_service");
        $stmt->bindParam(':id_service', $id_service_feedback, PDO::PARAM_INT);
        $stmt->execute();
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            throw new Exception("L'ID du service soumis est invalide.");
        }

        // --- 7. Insérer le feedback ---
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

        $stmt->bindParam(':id_client', $id_client, PDO::PARAM_INT);
        $stmt->bindParam(':id_service', $id_service_feedback, PDO::PARAM_INT);
        $stmt->bindParam(':qualite_accueil', $qualite_accueil);
        $stmt->bindParam(':bien_informe', $bien_informe);
        $stmt->bindParam(':temps_attente', $temps_attente);
        $stmt->bindParam(':recommandation', $recommandation);
        $stmt->bindParam(':justification', $justification);
        $stmt->bindParam(':commentaire', $commentaire);

        $stmt->execute();

        // --- 8. Redirection vers la page de succès ---
        header("Location: succes.html");
        exit();

    } catch (Exception $e) {
        echo "Une erreur est survenue lors du traitement de votre feedback : " . $e->getMessage();
        error_log("Erreur dans traitement.php: " . $e->getMessage());
    }

} else {
    echo "Accès non autorisé.";
    // header("Location: index.html");
    // exit();
}
?>
