<?php

require_once __DIR__ . '/../classi/Album.php';

//contengono i dati recuperati dal DB
$albumPreciso = null;
$artistiSelezionati = [];

//se sto modificando recupero i dati dell'album
if ($modifica) {

    //recupero album
    $query = $pdo->prepare("SELECT * FROM Album WHERE IdAlbum = ?");
    $query->execute([$id]);
    $dati_album = $query->fetch(PDO::FETCH_OBJ);

    if (!$dati_album) {
        die("Album non trovato");
    }

    $albumPreciso = new Album(
        $dati_album->IdAlbum,
        $dati_album->Titolo,
        $dati_album->Anno,
        $dati_album->Genere,
        $dati_album->Copertina
    );

    //recupero artisti produttori
    $query = $pdo->prepare("SELECT IdArtista FROM Produce WHERE IdAlbum = ?");
    $query->execute([$id]);
    $artistiSelezionati = $query->fetchAll(PDO::FETCH_OBJ);
}

//recupero artisti disponibili
$query = $pdo->query("SELECT IdArtista, NomeArte, Nome, Cognome FROM Artista ORDER BY NomeArte");
$artisti = $query->fetchAll(PDO::FETCH_OBJ);

?>

<form class="form-album" method="POST" action="/add.php" enctype="multipart/form-data">

    <h2><?php echo $modifica ? 'Modifica album' : 'Nuovo album'; ?></h2>
    
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

    <h3>Informazioni album</h3>

    <input type="text" name="titolo" placeholder="Titolo album"
        value="<?php echo htmlspecialchars($albumPreciso ? $albumPreciso->getTitolo() : ''); ?>" required>

    <input type="number" name="anno" placeholder="Anno"
        value="<?php echo htmlspecialchars($albumPreciso ? $albumPreciso->getAnno() : ''); ?>" required>

    <input type="text" name="genere" placeholder="Genere"
        value="<?php echo htmlspecialchars($albumPreciso ? $albumPreciso->getGenere() : ''); ?>" required>

    <label>Copertina album:</label>

    <?php
        if ($albumPreciso != null && !empty($albumPreciso->getCopertina())) {
            echo "<p>Copertina attuale: " . htmlspecialchars($albumPreciso->getCopertina()) . "</p>";
        }
        echo "<input type='hidden' name='vecchiaCopertina' value='" . htmlspecialchars($albumPreciso ? $albumPreciso->getCopertina() : '') . "'>";
    ?>

    <input type="file" name="copertina" accept="image/*">

    <h3>Artisti produttori</h3>
    <p>Seleziona gli artisti che hanno prodotto l'album:</p>

    <?php
    foreach ($artisti as $a) {
        echo "<label>";
        echo "<input type='checkbox' name='artisti[]' value='" . $a->IdArtista . "'";

        foreach ($artistiSelezionati as $artistaSelezionato) {
            if ($a->IdArtista == $artistaSelezionato->IdArtista) {
                echo " checked";
            }
        }     
        
        echo ">";
        echo htmlspecialchars(
            $a->NomeArte ?: $a->Nome . ' ' . $a->Cognome
        );
        echo "</label>";
    }
    ?>

    <button type="submit" name="aggiungiAlbum">
        <?php echo $modifica ? 'SALVA MODIFICHE' : 'SALVA E INVIA'; ?>
    </button>

</form>