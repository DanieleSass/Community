<?php
$frase = "";

try {
    $pdo = new PDO(
        "mysql:host=db;dbname=community;charset=utf8mb4",
        getenv("DB_USER"),
        getenv("DB_PASSWORD")
    );

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $username = $_POST['Username'] ?? '';
        $password = $_POST['Password'] ?? '';

        if (empty($username) || empty($password)) {

            $frase = "Errore: compila tutti i campi";

        } else {

            //cerca se lo username esiste già
            $sql_user = "SELECT * FROM Utenti WHERE Username = ?";
            $query_user = $pdo->prepare($sql_user);
            $query_user->execute([$username]);
            $user = $query_user->fetchAll(PDO::FETCH_OBJ);

            //se non esiste, registra l'utente
            if (count($user) == 0) {

                //cripta password
                $password_criptata = password_hash($password, PASSWORD_DEFAULT);

                $sql_insert_utente = "INSERT INTO Utenti (Username, Password) VALUES (?, ?)";
                $query_insert_utente = $pdo->prepare($sql_insert_utente);
                $query_insert_utente->execute([$username, $password_criptata]);

                header("Location: /index.php");
                exit();

            } else {

                $frase = "Errore: scegli un altro username, quello che hai inserito è già in uso";

            }
        }
    }

} catch(PDOException $e) {
    die("Errore di connessione");
}
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style/style_authentication.css">
    <title>Registrazione</title>
</head>
<body>
    <form method="POST" action="register.php" class="form-register">
        <h2>Registrazione</h2>
        <label for="input_user">Username</label>
        <input type="text" name="Username" id="input_user" maxlength="100" placeholder="Scegli un username..." required>
        <br>

        <label for="input_password">Password</label>
        <input type="password" name="Password" id="input_password" maxlength="100" placeholder="Scegli una password..." required>

        <button type="submit">Registrati</button>

            <?php
            if ($frase != "") {
                echo "<h4>$frase</h4>";
            }
            ?>


        <div class="auth-link">
            <a href="login.php">Hai già un account? Fai il login!</a>
        </div>
    </form>


</body>
</html>