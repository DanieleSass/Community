<?php

require_once __DIR__ . '/../classi/Canzone.php';

$canzone = null;
$artistiSelezionati = [];


if ($modifica) {

    $query = $pdo->prepare("SELECT * FROM Canzone WHERE IdCanzone = ?");
    $query->execute([$id]);
    $dati_canzone = $query->fetch(PDO::FETCH_OBJ);

    if (!$dati_canzone) {
        die("Canzone non trovata");
    }

    $canzone = new Canzone(
        $dati_canzone->IdCanzone,
        $dati_canzone->Titolo,
        $dati_canzone->Anno,
        $dati_canzone->Durata,
        $dati_canzone->Esplicita,
        $dati_canzone->Ascolti,
        $dati_canzone->IdAlbum
    );

    // Recupero artisti che hanno composto la canzone
    $query = $pdo->prepare("SELECT IdArtista FROM Compone WHERE IdCanzone = ?");
    $query->execute([$id]);
    $artistiSelezionati = $query->fetchAll(PDO::FETCH_OBJ);
}

//recupero album disponibili
$query = $pdo->query("SELECT IdAlbum, Titolo FROM Album ORDER BY Titolo");
$album = $query->fetchAll(PDO::FETCH_OBJ);

//recupero artisti disponibili
$query = $pdo->query("SELECT IdArtista, NomeArte, Nome, Cognome FROM Artista ORDER BY NomeArte");
$artisti = $query->fetchAll(PDO::FETCH_OBJ);

?>

<form class="form-canzone" method="POST" action="/add.php">

    <h2><?php echo $modifica ? 'Modifica canzone' : 'Nuova canzone'; ?></h2>

    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

    <h3>Informazioni canzone</h3>

    <input type="text" name="titolo" placeholder="Titolo canzone"
        value="<?php echo htmlspecialchars($canzone ? $canzone->getTitolo() : ''); ?>" required>

    <input type="number" name="anno" placeholder="Anno"
        value="<?php echo htmlspecialchars($canzone ? $canzone->getAnno() : ''); ?>" required>

    <input type="number" name="durata" placeholder="Durata (secondi)"
        value="<?php echo htmlspecialchars($canzone ? $canzone->getDurata() : ''); ?>">

    <input type="number" name="ascolti" placeholder="Numero ascolti"
        value="<?php echo htmlspecialchars($canzone ? $canzone->getAscolti() : ''); ?>">     

    <label>
        <input type="checkbox" name="esplicita" value="1"
            <?php if ($canzone != null && $canzone->getEsplicita() == 1) echo 'checked'; ?>
            >
        Contenuto esplicito
    </label>

    <h3>Album</h3>
    <p>Seleziona l'album di appartenenza:</p>

    <select name="idAlbum">

        <option value="">Nessun album</option>

        <?php
        foreach ($album as $a) {
            echo "<option value='" . $a->IdAlbum . "'";
            
            if ($canzone != null && $canzone->getIdAlbum() ==$a->IdAlbum) {
                echo " selected";
            }
            echo ">";
            echo htmlspecialchars($a->Titolo);
            echo "</option>";
        }
        ?>

    </select>

    <h3>Artisti</h3>
    <p>Seleziona gli artisti che hanno composto la canzone:</p>

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

    <button type="submit" name="aggiungiCanzone">
        <?php echo $modifica ? 'SALVA MODIFICHE' : 'SALVA E INVIA'; ?>
    </button>

</form>