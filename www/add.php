
<?php

require_once __DIR__ . '/classi/Artista.php';
require_once __DIR__ . '/classi/Album.php';
require_once __DIR__ . '/classi/Canzone.php';

   session_start();
    if (!isset($_SESSION['username'])) {
        session_unset();
        session_destroy();
        header("Location: /authentication/login.php");
        exit;
    }     

   $pdo = new PDO(
            "mysql:host=db;dbname=community;charset=utf8mb4",
            getenv("DB_USER"),
            getenv("DB_PASSWORD")
    );


            //GESTISCE SALVATAGGIO DOPO CHE SI HA PREMUTO SALVA/INVIA NEI FORM_X


//QUESTE TRE VARIABILI SONO IMPORTANTI NEL MOMENTO IN CUI APRO LA PAGINA E LE 
//VALORIZZO -> IN PARTICOLARE TIPO PER CAPIRE CHE FORM APRIRE
//- > ID NEL MOMENTO IN CUI VOGLIO MODIFICARE QUALCOSA DI GIà ESISTENTE

//tipo modulo (form_xxx) da mostrare
//info che prende da URL con get
$tipo = $_GET['tipo'] ?? '';


//id del record da modificare -> id vuoto -> nuovo record e non modifica
$id = $_GET['id'] ?? '';

    //controlla se siamo in modifica oppure in aggiunta
    //è una variabile aggiunta x semplificare la lettura/spiegazione codice
$modifica = ($id != '');



    //quando premo salva/invia su uno dei form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $cartella = "images/";     //la cartella per le foto dell'artista o copertina dell' album

    if (!is_dir($cartella)) {   //se non essite cartella la crea
        mkdir($cartella);
    }


    //artista
    //se utente preme aggiungiartista (pulsante nel form artista) 
    if (isset($_POST['aggiungiArtista'])) {

        //recupera valori inseriti da utente
        $id = $_POST['id'] ?? '';
        
        //vedi form-artista
        //id potrebbe essere vuoto ->aggiungo nuovo cantante 
        //id potrebbe essere valorizzato con input hidden ->modifico quel cantante
        $modifica = ($id != '');    //aggiorno relativa variabile

        $nome = $_POST['nome'];
        $cognome = $_POST['cognome'];
        $nomeArte = $_POST['nomeArte'];

        if ($nome == '' || $cognome == '' || $nomeArte == '') {
            die("Nome, cognome e nome d'arte sono obbligatori");
        }


        $eta = null;    //siccome è un numero prima setto a null, siccome è nullable nel db e poi eventualmente valorizzo

        if ($_POST['eta'] != '') {
            $eta = $_POST['eta'];
            if($eta < 10 || $eta > 120){
                die("Età non valida");
            }
        }

        $nazionalita = $_POST['nazionalita'];

        //mantiene la vecchia foto se non ne carico una nuova
        $foto = $_POST['vecchiaFoto'] ?? null;

        //controlla che sia stata effettivamente inviata una foto
        //e che durante il caricamento non ci siano stati errori

        //se non carico foto non entra qua
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {

            //prende solo l'ultima porzione del percorso (quella doppo ultimo "/" compresa estensione ovviamente)
            $nomeFile = basename($_FILES['foto']['name']);

            //sposta file dentro cartella "images"
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $cartella . $nomeFile)) {
                 //salva nella variabile x poi mettere il valore (percorso img) nel db
                $foto = $nomeFile;  //sovrascrive quella vecchia
            } else {
                die("Errore durante lo spostamento del file");
            }
        }

        //crea nuovo artista
        $artista = new Artista(
            $id,
            $nome,
            $cognome,
            $nomeArte,
            $eta,
            $nazionalita,
            $foto
        );

        if ($modifica == FALSE) {
            $sql = "INSERT INTO Artista
                    (Nome, Cognome, NomeArte, Eta, Nazionalita, Foto)
                    VALUES (?, ?, ?, ?, ?, ?)";

            $query = $pdo->prepare($sql);

            $query->execute([
                $nome,
                $cognome,
                $nomeArte,
                $eta,
                $nazionalita,
                $foto
            ]);

            $id = $pdo->lastInsertId(); //serve dopo, devo averlo valorizzato
            //in modifica invece mi arriva già quindi non lo devo "riprendere"

        } else {
            //altrimenti modifica l'artista già esistente
            $sql = "UPDATE Artista A
                    SET Nome = ?, Cognome = ?, NomeArte = ?,
                        Eta = ?, Nazionalita = ?, Foto = ?
                    WHERE IdArtista = ?";

            $query = $pdo->prepare($sql);

            $query->execute([
                $nome,
                $cognome,
                $nomeArte,
                $eta,
                $nazionalita,
                $foto,
                $id
            ]);

            //dopo che modifico elimino i collegamenti con album 
            //quindi nella tabella ponte Produce (siccome è N:N)
            $query = $pdo->prepare("DELETE FROM Produce WHERE IdArtista = ?");
            $query->execute([$id]);

            //idem x canzoni
            $query = $pdo->prepare("DELETE FROM Compone WHERE IdArtista = ?");
            $query->execute([$id]);

        }

        //re inserisce i collegamenti con album e canzoni

        //inserisce i nuovi collegamenti con album e canzone
        //a prescindere se si è aggiunto oppure modificato artista

        //nel formartista infatti ho anche un checkbox che mi permette di inviare quali album sono associati al cantante
        $album = $_POST['album'] ?? array();

        foreach ($album as $idAlbum) {
            $query = $pdo->prepare("INSERT INTO Produce (IdArtista, IdAlbum) VALUES (?, ?)");
            $query->execute([$id, $idAlbum]);
        }

        $canzoni = $_POST['canzoni'] ?? array();

        foreach ($canzoni as $idCanzone) {
            $query = $pdo->prepare("INSERT INTO Compone (IdArtista, IdCanzone) VALUES (?, ?)");
            $query->execute([$id, $idCanzone]);
        }

        header("Location: index.php");  //dopo aver premuto il bottono per salvare/modificare i dati
        //e aver salvato tutto nel db, ritorna nella pagina di landing
        exit;
    }
    


    
        //canzoni
    if (isset($_POST['aggiungiCanzone'])) {

        $id = $_POST['id'] ?? '';

        $modifica = ($id != '');

        $titolo = $_POST['titolo'];

        if ($titolo == '') {
            die("Il titolo è obbligatorio");
        }

        $anno = $_POST['anno'];
        
        if($anno<1500 || $anno>date("Y")){
            die("Anno non valido");
        }

        $durata = null;
        if ($_POST['durata'] != '') {
            $durata = $_POST['durata'];
            if($durata<10){
                die("Durata non valida");
            }
        }

        $ascolti = null;
        if ($_POST['ascolti'] != '') {
            $ascolti = $_POST['ascolti'];
            if($ascolti<0){
                die("Ascolti non validi");
            }
        }

        $esplicita = isset($_POST['esplicita']) ? 1 : 0;

        $idAlbum = null;
        if (isset($_POST['idAlbum']) && $_POST['idAlbum'] != '') {
            $idAlbum = $_POST['idAlbum'];
        }

        $canzone = new Canzone(
            $id,
            $titolo,
            $anno,
            $durata,
            $esplicita,
            $ascolti,
            $idAlbum
        );


        if ($modifica == FALSE) {

            //aggiunge una nuova canzone
            $sql = "INSERT INTO Canzone
                    (Titolo, Anno, Durata, Esplicita, Ascolti,  IdAlbum)
                    VALUES (?, ?, ?, ?, ?, ?)";

            $query = $pdo->prepare($sql);

            $query->execute([
                $titolo,
                $anno,
                $durata,
                $esplicita,
                $ascolti,
                $idAlbum
            ]);

            $id = $pdo->lastInsertId(); //serve dopo

        } else {

            //modifica canzone già esistente
            $sql = "UPDATE Canzone
                    SET Titolo = ?, Anno = ?, Durata = ?,
                        Esplicita = ?,  Ascolti = ?, IdAlbum = ?
                    WHERE IdCanzone = ?";

            $query = $pdo->prepare($sql);

            $query->execute([
                $titolo,
                $anno,
                $durata,
                $esplicita,
                $ascolti,
                $idAlbum,
                $id
            ]);

            //perde vecchi collegamenti con artisti
            $query = $pdo->prepare("DELETE FROM Compone WHERE IdCanzone = ?");
            $query->execute([$id]);
        }

        //inserisce nuovo collegamento con artista
        //sia quando aggiunge sia quando modifica la canzone
        $artisti = $_POST['artisti'] ?? array(); 
        foreach ($artisti as $idArtista) {

            $query = $pdo->prepare("INSERT INTO Compone (IdArtista, IdCanzone) VALUES (?, ?)");
            $query->execute([$idArtista, $id]);
        }

        header("Location: index.php");
        exit;
}



        //album
        if (isset($_POST['aggiungiAlbum'])) {

            $id = $_POST['id'] ?? '';

            $modifica = ($id != '');

            $titolo = $_POST['titolo'];
            $anno = $_POST['anno'];
            $genere = $_POST['genere'];

            if ($titolo=='' || $genere=='') {
                die("Titolo e genere sono obbligatori");
            }

            if($anno<1500 || $anno>date("Y")){
                die("Anno non valido");
            }

            //mantiente vecchiacopertina se non carico nulla di nuovo
            $copertina = $_POST['vecchiaCopertina'] ?? null;

            //controlla se è stata inviata foto e correttamente senza errori
            if (isset($_FILES['copertina']) && $_FILES['copertina']['error'] == 0) {
                $nomeFile = basename($_FILES['copertina']['name']);

                //sposta il file nel images
                if (move_uploaded_file($_FILES['copertina']['tmp_name'], $cartella . $nomeFile)) {
                //salva il nuovo percorso
                $copertina =$nomeFile;
                } else {
                    die("Errore durante lo spostamento della copertina");
                }

            }

            $album = new Album(
            $id,
            $titolo,
            $anno,
            $genere,
            $copertina
            );

            if ($modifica == FALSE) {

                //aggiunge nuovo album
                $sql = "INSERT INTO Album
                        (Titolo, Anno, Genere, Copertina)
                        VALUES (?, ?, ?, ?)";

                $query = $pdo->prepare($sql);
                $query->execute([
                    $titolo,
                    $anno,
                    $genere,
                    $copertina
                ]);
                
                $id = $pdo->lastInsertId();

            } else {

                //modifica album già esistente
                $sql = "UPDATE Album A
                        SET Titolo = ?, Anno = ?, Genere = ?, Copertina = ?
                        WHERE IdAlbum = ?";

                $query = $pdo->prepare($sql);

                $query->execute([
                    $titolo,
                    $anno,
                    $genere,
                    $copertina,
                    $id
                ]);

                //elimina vecchi collegamenti con artisti
                $query = $pdo->prepare("DELETE FROM Produce WHERE IdAlbum = ?");
                $query->execute([$id]);
            }

            //inserisce i nuovi collegamenti con artisti
            //sia quando aggiunge sia quando modifica l'album
            $artisti = $_POST['artisti'] ?? array();

            foreach ($artisti as $idArtista) {

                $query = $pdo->prepare("INSERT INTO Produce (IdArtista, IdAlbum) VALUES (?, ?)");
                $query->execute([$idArtista, $id]);
            }

            header("Location: index.php");
            exit;
        }
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="style/style_add.css">
    <link rel="stylesheet" href="style/style_barra_navigazione.css">

    <link rel="stylesheet" href="style/style_form.css">

    <title>Community</title>
</head>
<body>

    <header>
        <?php include 'barra_navigazione.php'; ?>
    </header>

    <?php

    //qua controlla se ho premuto per modificare artista/ecc... e allora mostra solo
        // il relativo form

    //se invece ho premuto aggiungi ->tipo="" e quindi mostra tutti e 3 i form    

    if ($tipo == 'artista') {

        include 'form/form_artista.php';

    } elseif ($tipo == 'canzone') {

        include 'form/form_canzone.php';

    } elseif ($tipo == 'album') {

        include 'form/form_album.php';

    } else {
        //se non ho specificato il tipo allora inserisco qualcosa di nuovo e mostro tutti i form
        include 'form/form_artista.php';
        echo "<br>";

        include 'form/form_canzone.php';
        echo "<br>";

        include 'form/form_album.php';
    }
    ?>

    <br>

    <a href="index.php" id="pulsante_elimina">
        ELIMINA O MODIFICA DATI
    </a>

</body>
</html>
