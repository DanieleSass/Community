<?php
try {
        $pdo = new PDO(
            "mysql:host=db;dbname=community;charset=utf8mb4",
            getenv("DB_USER"),
            getenv("DB_PASSWORD")
        );

        //se sbaglia a loggarsi poi viene mostrato questo
        $errore = "";

        //si poteva anche fare il controllo isset()
        if ($_SERVER["REQUEST_METHOD"] === "POST") 
        {
        $username = $_POST['Username'] ?? '';
        $password = $_POST['Password'] ?? '';

        if(empty($username) || empty($password)){
            echo "Errore: compila tutti i campi";
            die;
        }

        $sql_user = "SELECT * FROM Utenti WHERE 
        Username = ?
        ";

        $query_user = $pdo->prepare($sql_user);
        $query_user->execute(
            [
                $username
            ]
        );

        $login_user = $query_user->fetchAll(PDO::FETCH_OBJ);

        //ora in login user dovrei avere un solo elemento in quanto il nome utente è univoco
        //c'è una corrispondenza con il db -> user e psw giusti
        if(count($login_user) == 1){
            if(password_verify($password, $login_user[0]->Password)){

            //faccio partire la sessione
            session_start();
            $_SESSION['username'] = $username;

            session_regenerate_id();

            header("Location: /index.php");
            exit();
        }
        else{
            $errore = "Username o password non corretti";
        }
        }
        else{
            $errore = "Impossibile fare login";
        }
    }

    }  
    catch(PDOException $e) {
        die("Errore di connessione");
    
    }
?>



<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style/style_authentication.css">
    <title>Login</title>
</head>
<body>
    <form  method="POST" action="login.php" class="form-login">

        <h2>Login</h2>

        <label for="input_user">Username</label>
        <input type="text" name="Username" id="input_user" maxlength="100" placeholder="Inserisci il tuo username..." required>
        <br>
        <label for="input_password">Password</label>
        <input type="password" name="Password" id="input_password" maxlength="100" placeholder="Inserisci la tua password: ***" required>

        <button type="submit">Login</button>

            <?php
            if ($errore != "") {
                echo "<h4>$errore</h4>";
            }
            ?>


        <div class="auth-link">
            <a href="register.php">Non hai un account? Registrati!</a>
        </div>

    </form>
    
</body>
</html>