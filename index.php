<?php
session_start();

$message = "";
$response_message = "";

/* =======================
   PARTIE 1 : LOGIN
======================= */

if (isset($_POST["login"])) {

    $email = trim($_POST["email"]);
    $password_input = trim($_POST["password"]);

    $project_url = "https://uhqqzlpaybcyxrepisgi.supabase.co";
    $api_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InVocXF6bHBheWJjeXhyZXBpc2dpIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzA4NDAyNzgsImV4cCI6MjA4NjQxNjI3OH0.LNQMIQs7euI7-4MMJWU_maqT6WdXq6lWuueCtF3kE24";

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

    foreach ($data as $user) {

        if ($user["email"] === $email && $user["password"] === $password_input) {

            $_SESSION["user"] = $email;
            break;
        }
    }

    if (!isset($_SESSION["user"])) {
        $message = "❌ Email ou mot de passe incorrect.";
    }
}

/* =======================
   PARTIE 2 : QUESTION
======================= */

if (isset($_POST["ask"]) && isset($_SESSION["user"])) {

    $question = trim($_POST["question"]);

    $url = "https://n8n-9-dtnb.onrender.com/webhook/student-question";

    $data = [
        "question" => $question,
        "user" => $_SESSION["user"]
    ];

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);
    curl_close($ch);

    $response_message = $result;
}

/* =======================
   LOGOUT
======================= */

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

    <h2>Bienvenue <?php echo $_SESSION["user"]; ?></h2>

    <a href="?logout=true">Se déconnecter</a>

    <hr>

    <h3>Pose ta question</h3>

    <form method="POST">
        <input type="text" name="question" placeholder="Écris ta question..." required>
        <button type="submit" name="ask">Envoyer</button>
    </form>

    <div>
        <?php echo $response_message; ?>
    </div>

<?php endif; ?>

</body>
</html>
