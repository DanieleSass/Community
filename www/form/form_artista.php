<?php

require_once __DIR__ . '/../classi/Artista.php';

//usa la pdo di add.php per evitare doppi colleegamenti

    //FORM CHE MOSTRA TUTTI I DATI RELATIVI A ARTISTA SE SONO IN MOD MODIFICA
    //ALTRIMENTI FORM VUOTO SE DEVO CREARLO DA 0

    //A PRESCINDERE MOSTRA TUTTI ALBUM E CANZONI A CUI POSSO ASSOCIARLO

$artista = null;    //poi valorizzato in un oggetto se in mod modifica
$albumSelezionati = []; //se in mod modifica gli album da lui prodotti
$canzoniSelezionate = [];

//se sto modificando recupero i dati di artisa
if ($modifica) {

    //recupero artista
    $query = $pdo->prepare("SELECT * FROM Artista WHERE IdArtista = ?");
    $query->execute([$id]);     //valore preso all' inizio di add.php con $GET[id]
    $dati_artista = $query->fetch(PDO::FETCH_OBJ);

    if (!$dati_artista) {
        die("Artista non trovato");
    }

    $artista = new Artista(
        $dati_artista->IdArtista,
        $dati_artista->Nome,
        $dati_artista->Cognome,
        $dati_artista->NomeArte,
        $dati_artista->Eta,
        $dati_artista->Nazionalita,
        $dati_artista->Foto
    );

    //prende anche gli album relativi
    $query = $pdo->prepare("SELECT IdAlbum FROM Produce WHERE IdArtista = ?");
    $query->execute([$id]);
    $albumSelezionati = $query->fetchAll(PDO::FETCH_OBJ);

    //idem per canzoni
    $query = $pdo->prepare("SELECT IdCanzone FROM Compone WHERE IdArtista = ?");
    $query->execute([ $id]);
    $canzoniSelezionate = $query->fetchAll(PDO::FETCH_OBJ);
}

//a prescindere se sia da aggiungere o modificare artista:

//sotto ho la possibilità di collegare un album a un artistsa, idem x canzone
//recupero album TUTTI
$query = $pdo->query("SELECT IdAlbum, Titolo FROM Album ORDER BY Titolo");
$album = $query->fetchAll(PDO::FETCH_OBJ);

//recupero canzoni TUTTI
$query = $pdo->query("SELECT IdCanzone, Titolo FROM Canzone ORDER BY Titolo");
$canzoni = $query->fetchAll(PDO::FETCH_OBJ);

?>

<!-- multipart/form-data permette di inviare anche file multimediali tipo le foto -->
<form class="form-artista" method="POST" action="/add.php" enctype="multipart/form-data">

    <h2><?php echo $modifica ? 'Modifica artista' : 'Nuovo artista'; ?></h2>

    <!-- campo di input invisibile che serve per portarsi dietro il valore delle colonne importanti (id )
    questo valore lo recuper con il name = id -> quindi con $_POST['id'], con ovviamente il corrispettivo valore 
    -->

    <!-- posso inviare id oppure stringa vuota -> poi ricontrollo in add.php infatti -->
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

    <h3>Informazioni artista</h3>

    <input type="text" name="nome" placeholder="Nome"
        value="<?php echo htmlspecialchars($artista ? $artista->getNome() : ''); ?>" required>

    <input type="text" name="cognome" placeholder="Cognome"
        value="<?php echo htmlspecialchars($artista ? $artista->getCognome() : ''); ?>" required>

    <input type="text" name="nomeArte" placeholder="Nome d'Arte"
        value="<?php echo htmlspecialchars($artista ? $artista->getNomeArte() : ''); ?>" required>

    <input type="number" name="eta" placeholder="Età" min="10" max="120"
        value="<?php echo htmlspecialchars($artista ? $artista->getEta() : ''); ?>">

    <input type="text" name="nazionalita" placeholder="Nazionalità"
        value="<?php echo htmlspecialchars($artista ? $artista->getNazionalita() : ''); ?>">

    <label>Foto artista:</label>

    <?php
        if ($artista != null && !empty($artista->getFoto())) {  //se ha già una foto la mostra come percorso
            echo "<p>Foto attuale: " . htmlspecialchars($artista->getFoto()) . "</p>";
        }

        //crea il campo invisibile che dopo viene mandato a add.php  per conservare la vecchia foto
        echo "<input type='hidden' name='vecchiaFoto' value='" . htmlspecialchars($artista ? $artista->getFoto() : '') . "'>";
    ?>

    <input type="file" name="foto" accept="image/*">


    <!-- qua si può associare album ad artista, sotto anche le canzoni -->
    <h3>Album prodotti</h3>
    <p>Seleziona gli album associati all'artista:</p>

        <?php
        foreach ($album as $a) {
            echo "<label>";

            //album[] vuol dire che ammette più valori da inviare
            //poi recuperati con un array
            echo "<input type='checkbox' name='album[]' value='" . $a->IdAlbum . "'";

            //per avere già selezionate album di quell' artistsa, idem sotto x canzoni
            foreach ($albumSelezionati as $albumSelezionato) {
            if ($a->IdAlbum == $albumSelezionato->IdAlbum) {
            echo " checked";
                }
            }
            echo ">";
            echo htmlspecialchars($a->Titolo);
            echo "</label>";
        }
        ?>
    <h3>Canzoni composte</h3>
    <p>Seleziona le canzoni associate all'artista:</p>

        <?php
        foreach ($canzoni as $c) {
            echo "<label>";
            echo "<input type='checkbox' name='canzoni[]' value='" . $c->IdCanzone . "'";

            foreach ($canzoniSelezionate as $canzoneSelezionata) {
                if ($c->IdCanzone == $canzoneSelezionata->IdCanzone) {
                    echo " checked";
                }
            }
            echo ">";
            echo htmlspecialchars($c->Titolo);
            echo "</label>";
        }
        ?>

    <button type="submit" name="aggiungiArtista">
        <?php echo $modifica ? 'SALVA MODIFICHE' : 'SALVA E INVIA'; ?>
    </button>

</form>