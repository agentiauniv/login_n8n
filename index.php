<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$responseMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['question'])) {

    $question = $_POST['question'];

    $webhook_url = "https://n8n-9-dtnb.onrender.com/webhook/student-log";

    $data = [
        "email" => $_SESSION['user'],
        "question" => $question
    ];

    $options = [
        "http" => [
            "header"  => "Content-Type: application/json\r\n",
            "method"  => "POST",
            "content" => json_encode($data),
            "ignore_errors" => true
        ],
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($webhook_url, false, $context);

    if ($result === FALSE) {
        $responseMessage = "❌ Erreur connexion n8n";
    } else {
        $responseMessage = $result;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>

<h2>Bienvenue <?php echo $_SESSION['user']; ?></h2>

<a href="logout.php">Se déconnecter</a>

<hr>

<h3>Pose ta question</h3>

<form method="POST">
    <input type="text" name="question" placeholder="Écris ta question..." required>
    <button type="submit">Envoyer</button>
</form>

<br>

<?php
if ($responseMessage != "") {
    echo "<h4>Réponse n8n :</h4>";
    echo "<pre>$responseMessage</pre>";
}
?>

</body>
</html>


