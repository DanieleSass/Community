
<?php

//avvia la sessione
session_start();

//controlla che l'utente abbia effettuato il login
if (!isset($_SESSION['username'])) {
    session_unset();        //libera variabili di sessione
    session_destroy();      //distrugge tutti i dati associati alla sessione corretnte
    header("Location: /authentication/login.php");
    exit();
}

//controlla che la richiesta sia POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: /index.php");
    exit();
}

try {

    $pdo = new PDO(
        "mysql:host=db;dbname=community;charset=utf8mb4",
        getenv("DB_USER"),
        getenv("DB_PASSWORD")
    );

        //eliminazione artista                          

        //verifica che nella richiesta POST esista 
        //il parametro eliminaartista e che sia != null -> se è così da true

        //attraverso il "name= " -> nel caso delle eliminazioni è stato il pulsante all' intenro del form
        


    //eliminazione artista
    if (isset($_POST['eliminaArtista'])) {

        $id = $_POST['id'];

        //elimina i collegamenti nelle tabelle ponte
        $query = $pdo->prepare("DELETE FROM Produce WHERE IdArtista = ?");
        $query->execute([$id]);

        $query = $pdo->prepare("DELETE FROM Compone WHERE IdArtista = ?");
        $query->execute([$id]);

        //elimina l'artista
        $query = $pdo->prepare("DELETE FROM Artista WHERE IdArtista = ?");
        $query->execute([$id]);

    }

    //eliminazione album
    if (isset($_POST['eliminaAlbum'])) {

        $id = $_POST['id'];

        //elimina i collegamenti dell'album
        $query = $pdo->prepare("DELETE FROM Produce WHERE IdAlbum = ?");
        $query->execute([$id]);

        //elimina i collegamenti degli artisti alle canzoni dell'album
        $query = $pdo->prepare("
            DELETE FROM Compone
            WHERE IdCanzone IN (
                SELECT IdCanzone FROM Canzone WHERE IdAlbum = ?
            )
        ");
        $query->execute([$id]);

        //elimina le canzoni dell'album
        $query = $pdo->prepare("DELETE FROM Canzone WHERE IdAlbum = ?");
        $query->execute([$id]);

        //elimina l'album
        $query = $pdo->prepare("DELETE FROM Album WHERE IdAlbum = ?");
        $query->execute([$id]);

    }

    //eliminazione canzone
    if (isset($_POST['eliminaCanzone'])) {

        $id = $_POST['id'];

        //elimina i collegamenti nella tabella ponte
        $query = $pdo->prepare("DELETE FROM Compone WHERE IdCanzone = ?");
        $query->execute([$id]);

        //elimina la canzone
        $query = $pdo->prepare("DELETE FROM Canzone WHERE IdCanzone = ?");
        $query->execute([$id]);

    }

    //torna alla pagina principale
    header("Location: index.php");
    exit;

} catch(PDOException $e) {
    die("Errore di connessione");
}

?>
