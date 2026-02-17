<?php
session_start();

$message = "";
$response_message = "";

/* =========================
   PARTIE 1 : CONNEXION
========================= */

if (isset($_POST["login"])) {

    $email = trim($_POST["email"]);
    $password_input = trim($_POST["password"]);

    $project_url = "https://uhqqzlpaybcyxrepisgi.supabase.co";
    $api_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InVocXF6bHBheWJjeXhyZXBpc2dpIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzA4NDAyNzgsImV4cCI6MjA4NjQxNjI3OH0.LNQMIQs7euI7-4MMJWU_maqT6WdXq6lWuueCtF3kE24"; // Mets ta clé

    $url = $project_url . "/rest/v1/login?select=*";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $api_key",
        "Authorization: Bearer $api_key",
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    $found = false;

    foreach ($data as $user) {

        $email_db = trim(str_replace(["\n", "\r"], '', $user["email"]));
        $password_db = trim(str_replace(["\n", "\r"], '', $user["password"]));

        if ($email_db === $email && $password_db === $password_input) {

            $_SESSION["user"] = $email_db;
            $_SESSION["matricule"] = $user["Matricule"];

            $found = true;
            break;
        }
    }

    if (!$found) {
        $message = "❌ Email ou mot de passe incorrect.";
    }
}

/* =========================
   PARTIE 2 : QUESTION → n8n
========================= */

if (isset($_POST["ask"]) && isset($_SESSION["user"])) {

    $question = trim($_POST["question"]);

    $webhook_url = "https://n8n-9-dtnb.onrender.com/webhook/student-question";

    $payload = [
        "email" => $_SESSION["user"],
        "matricule" => $_SESSION["matricule"],
        "question" => $question
    ];

    $ch = curl_init($webhook_url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        $response_message = "Erreur connexion n8n.";
    } else {
        $response_message = $result;
    }

    curl_close($ch);
}

/* =========================
   LOGOUT
========================= */

if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assistant Universitaire</title>
</head>
<body>

<?php if (!isset($_SESSION["user"])): ?>

    <h2>Connexion</h2>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Mot de passe" required><br><br>
        <button type="submit" name="login">Se connecter</button>
    </form>

    <div><?php echo $message; ?></div>

<?php else: ?>

    <h2>Bienvenue <?php echo htmlspecialchars($_SESSION["user"]); ?></h2>
    <p>Matricule : <?php echo htmlspecialchars($_SESSION["matricule"]); ?></p>

    <a href="?logout=true">Se déconnecter</a>

    <hr>

    <h3>Pose ta question</h3>

    <form method="POST">
        <input type="text" name="question" placeholder="Écris ta question..." required>
        <button type="submit" name="ask">Envoyer</button>
    </form>

    <br>

    <div>
        <?php echo $response_message; ?>
    </div>

<?php endif; ?>

</body>
</html>
