<?php

//obblica di inlcludere il file (le classi)
//inluderlo una sola volta
//DIR restituisce la cartella in cui si trova progetto -> /var/wwww/file
//genera errore e interrompe esecuzione se file nonesiste
require_once __DIR__ . '/classi/Artista.php';
require_once __DIR__ . '/classi/Album.php';
require_once __DIR__ . '/classi/Canzone.php';

    

    try {
        $pdo = new PDO(
            "mysql:host=db;dbname=community;charset=utf8mb4",
            getenv("DB_USER"),
            getenv("DB_PASSWORD")
        );

        
        //recupera le opzioni che verranno poi inserite nel menu a tendina per gestire i filtri 
        $sql_artista_filtro = "SELECT * FROM Artista ORDER BY NomeArte";
        $query_artista_filtro = $pdo->query($sql_artista_filtro);
        $artistiFiltro = $query_artista_filtro->fetchAll(PDO::FETCH_OBJ);

        $sql_album_filtro = "SELECT * FROM Album ORDER BY Titolo";
        $query_album_filtro = $pdo -> query($sql_album_filtro);
        $albumFiltro = $query_album_filtro->fetchAll(PDO::FETCH_OBJ);

        //recupera i filtri /valori selezionati
        //artista/album sono i "name" del select =menu a tendina per i filtri
        //li prende grazie al componente select con name="artista" o name="album"
        $idArtista = $_GET['artista'] ?? '';
        $idAlbum = $_GET['album'] ?? '';

        //query artisti (prende tutti i campi di artista)
        //con left join così da vedere ancge artisti che non ha album associati
        $sql_artista = "SELECT DISTINCT A.* FROM Artista A
        LEFT JOIN Produce P ON A.IdArtista = P.IdArtista
        LEFT JOIN Album ON P.IdAlbum = Album.IdAlbum
        WHERE 1=1";
        //il where 1=1 mi semplifica la gestione di eventuali filtri perchè non devo controllare se 
        //era già stata usata la parola chiave where o se devo concatenare la query con "and"
        //con 1=1 so già che se ho filtri da applicare basta fare "and filtro=valore"

        //se è stato applicato il filtro sull' album concatena alla query il filtro (con ovviamente il join)
        if ($idArtista != '') {
            $sql_artista .= " AND A.IdArtista = :artista";
            // ":artista" è un segnaposto non posizionale come il "?" ma nominale -> si basa sul nome e non sulla sua posizione
            //più semplice da gestire nel momento in cui non è obbligatorio inserire dei filtri
        }
        //potrei già mettere i valori numerici dei filtri in quanto li ho già ottenuti
        //però è meglio usare prepare ed execute
        //come abbiamo fatto in java
        //per ragioni di sicurezza
        if ($idAlbum != '') {
            $sql_artista .= " AND Album.IdAlbum = :album";
        }

        $sql_artista .= " ORDER BY A.NomeArte";

        $query_artista = $pdo->prepare($sql_artista);

        if ($idArtista != '') { 
            //si riferisce al segnaposto dell' artista  
            $query_artista->bindValue(":artista", $idArtista);
        }

        if ($idAlbum != '') {
            //si riferisce al secondo segnaposto->album
            $query_artista->bindValue(":album", $idAlbum);
        }

        //siccome la query è parametrica non posso usare ->query()
        //ma deve usare execute

        //se avessi impostato il valore dei filtri direttamente nella query senza usare segnaposti 
        //allora avrei potuto usare query

        $query_artista->execute();  //la esegue
        $dati_artisti = $query_artista->fetchAll(PDO::FETCH_OBJ);

        $artisti = array();

        foreach ($dati_artisti as $a) {
            $artisti[] = new Artista(
                $a->IdArtista,
                $a->Nome,
                $a->Cognome,
                $a->NomeArte,
                $a->Eta,
                $a->Nazionalita,
                $a->Foto
            );
        }



        //query sull' album, stesso concetto
        $sql_album = "SELECT DISTINCT AL.* 
              FROM Album AL
              LEFT JOIN Produce P ON AL.IdAlbum = P.IdAlbum
              WHERE 1=1";

        //se è stato applicato il filtro sull'artista
        if ($idArtista != '') {
            $sql_album .= " AND P.IdArtista = :artista";
        }

        //se è stato applicato il filtro sull'album
        if ($idAlbum != '') {
            $sql_album .= " AND AL.IdAlbum = :album";
        }

        $sql_album .= " ORDER BY AL.IdAlbum";

        $query_album = $pdo->prepare($sql_album);

        if ($idArtista != '') {
            //associa il valore al segnaposto artista
            $query_album->bindValue(':artista', $idArtista);
        }

        if ($idAlbum != '') {
            //associa il valore al segnaposto album
            $query_album->bindValue(':album', $idAlbum);
        }

        $query_album->execute();
        $dati_album = $query_album->fetchAll(PDO::FETCH_OBJ);

        $album = array();

        foreach ($dati_album as $a) {
            $album[] = new Album(
                $a->IdAlbum,
                $a->Titolo,
                $a->Anno,
                $a->Genere,
                $a->Copertina
            );
        }

        //query canzoni
        // per ricavara anche possibile album di appartenenza
        //per ricavare anche autori 
            $sql_canzoni = "
                SELECT C.*, AL.Titolo AS NomeAlbum,
                GROUP_CONCAT(DISTINCT A.NomeArte SEPARATOR ', ') AS Artisti
                FROM Canzone C
                LEFT JOIN Album AL ON C.IdAlbum = AL.IdAlbum
                LEFT JOIN Compone P ON C.IdCanzone = P.IdCanzone
                LEFT JOIN Artista A ON P.IdArtista = A.IdArtista
                WHERE 1=1
            ";

        //se è stato applicato il filtro sull'artista
        if ($idArtista != '') {
            $sql_canzoni .= " AND P.IdArtista = :artista";
        }

        //se è stato applicato il filtro sull'album
        if ($idAlbum != '') {
            $sql_canzoni .= " AND C.IdAlbum = :album";
        }

        $sql_canzoni .= " GROUP BY C.IdCanzone, AL.Titolo ORDER BY C.Titolo";

        $query_canzoni = $pdo->prepare($sql_canzoni);

        if ($idArtista != '') {
            $query_canzoni->bindValue(':artista', $idArtista);
        }

        if ($idAlbum != '') {
            $query_canzoni->bindValue(':album', $idAlbum);
        }

        $query_canzoni->execute();
        $dati_canzoni = $query_canzoni->fetchAll(PDO::FETCH_OBJ);

        $canzoni = array();

        foreach ($dati_canzoni as $c) {
            $canzoni[] = new Canzone(
                $c->IdCanzone,
                $c->Titolo,
                $c->Anno,
                $c->Durata,
                $c->Esplicita,
                $c->Ascolti,
                $c->IdAlbum
            );
        }

        //per mostrare nella tabella a quale album appartiene quella canzone
        //e da chi è stata cantata
            $artistiCanzoni = array();
            $albumCanzoni = array();

            foreach ($dati_canzoni as $c) {
                //usando idcanzone come indice, inserisci artisti di quella canzone
                //array associativo
                //chiave = id canzone
                //valore = artisti che la cantano
                $artistiCanzoni[$c->IdCanzone] = $c->Artisti;   //nome Artisti creato nella query con AS

                //usando idcanzone come indice, inserisci album di appartenenza di quella canzone
                $albumCanzoni[$c->IdCanzone] = $c->NomeAlbum;   //nome NomeAlbum creato nella query con AS
            }


    }
    catch(PDOException $e) {
        die("Errore di connessione: " . $e->getMessage());
    }
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--css specifico di questa pagina index-->
    <link rel="stylesheet" href="/style/style_index.css">
    <!--css dedicato alla barra orizzontale superiore \-->
    <link rel="stylesheet" href="/style/style_barra_navigazione.css">
    <title>Community</title>
</head>
<body>
    <header>
        <?php
        include 'barra_navigazione.php';    //incorpora il codice di quella pagina php e lo copia qua dentro
        ?>
    </header>



    <!-- form per la gestione dei filtri -> deve prendere i valori ->
      quindi GET, la cui azione ricade su se stesso (stessa paginA)-->
<!-- ci saranno i filtri che viaggiano in chiaro su url grazie a method get e i dati vengono inviati alla stessa 
 pagina per aggiornare i filtri -->
    <form method="GET" action="index.php">

    <label>Artista:</label>
    <!-- name artista serve per prendere i valori nel tag php sopra -->
     <!-- quando cambia filtro ricarica la pagina -->
<!-- name stabilisce nome parmetro-->

    <!-- il name artistsa  viene usato sopra per recuperare poi il valore con GET -->
    <select name="artista" onchange="this.form.submit()">
        <option value="">Tutti gli artisti</option> <!-- opzione di default -->

        <?php
        foreach ($artistiFiltro as $a) {
            //costruisce il tag per scegliere quel valore di quel filtro
            echo '<option value="'   .   $a->IdArtista  .   '"'   ;
            //logica per gestire la selezione, cioè ogni volta che cambio selezione
            //quel tag avrà il flag selected che serve al browser per capire che quella è l'opzione selezionata
            //infatti quando php ricostruisce il menu controlla la condizione e impone il "selected" così
            //da mantenere viva la condizione
            if ($idArtista == $a->IdArtista) {
                echo ' selected';
            }
            echo '>'   .   htmlspecialchars($a->NomeArte) . '</option>';
            //htmlspecialchars() serve a convertire caratteri speciali in "entità" html,
            //evita che eventuali caratteri come "<" e ">" siano interpretati come codice html
        }
        ?>
    </select>


    <label>Album:</label>
    <!-- quando cambia filtro ricarica la pagina -->
    <select name="album" onchange="this.form.submit()">
        <option value="">Tutti gli album</option>
            <?php
            foreach ($albumFiltro as $a) {
                //costruisce il tag per scegliere quel valore del filtro
                echo '<option value="' . $a->IdAlbum . '"';
                if ($idAlbum == $a->IdAlbum) {
                    echo ' selected';
                }
                echo '>' . htmlspecialchars($a->Titolo) . '</option>';
            }
            ?>
    </select>

    <!--questo link porta alla pagina stessa quindi è come se resettassi tutto e la aprissi per la prima volta -->
    <a href="index.php">Reset filtri</a>

    </form>




    <?php
    //se non c'è niente stampa questo ed esce
    if(empty($artisti) && empty($canzoni) && empty($album)){
        echo "<p>Nessuna informazione, aggiungine una!</p>";
		return;
    }?>

    <!-- creazione tabelle-->
    <table id="tabellaArtista">
        <thead>
            <tr>
            <th>Nome d'Arte</th>
            <th>Nome</th>
            <th>Cognome</th>
            <th>Nazionalità</th>
            <th>Età</th>
            <th>Foto</th>
            <th>Azioni</th> <!-- modifica /elimina -->
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($artisti as $artista) {
                //crea una nuova riga della tabella
                echo '<tr>';
                //inserisce artista nelle celle
                echo '<td>' . htmlspecialchars($artista->getNomeArte() ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($artista->getNome() ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($artista->getCognome() ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($artista->getNazionalita() ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($artista->getEta() ?? '') . '</td>';

                echo '<td>';

                if (!empty($artista->getFoto())) {
                    echo '<img src="/images/'   .   htmlspecialchars($artista->getFoto())   .   '">';
                } else {
                    echo 'Nessuna foto';
                }

                echo '</td>';

                //crea la cella contenente i link per modifica/eliminazione
                echo '<td>';

                //il link <a> utilizza automaticamente il metodo GET quando si sposta da pagina a pagina
                //qua inoltre uso get perchè poi il form artista dovrà recuperare i dati e mostrarli a schermo
                echo '<a href="add.php?tipo=artista&id=' . $artista->getIdArtista() . '">Modifica</a>';
                
                
                //la " \" serve per poter inserire apostrofo e quindi stringa senza chiudere la stringa che la contiene (quindi tag html)
                //per modificare il db uso il metodo post, che è associabile a un form
                echo '<form method="POST" action="delete.php" onsubmit="return confirm(\'Sicuro?\');">';
                echo '<input type="hidden" name="id" value="' . $artista->getIdArtista() . '">';
                echo '<button type="submit" name="eliminaArtista">Elimina</button>';
                echo '</form>';

                echo '</td>';

                //chiude la riga
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>

    <table id="tabellaCanzoni">
        <thead>
            <tr>
            <th>Titolo</th>
            <th>Artisti</th>
            <th>Album</th>
            <th>Anno d'Uscita</th>
            <th>Durata (secondi)</th>
            <th>Esplicita</th>
            <th>Ascolti </th>
            <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($canzoni as $canzone) {
                //crea una nuova riga
                echo '<tr>';
                //inserisce canzone
                echo '<td>' . htmlspecialchars($canzone->getTitolo() ?? '') . '</td>';
                
                echo '<td>' . htmlspecialchars($artistiCanzoni[$canzone->getIdCanzone()] ?? 'Nessun artista') . '</td>';
                echo '<td>' . htmlspecialchars($albumCanzoni[$canzone->getIdCanzone()] ?? 'Nessun album') . '</td>';      
                
                echo '<td>' . htmlspecialchars($canzone->getAnno() ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($canzone->getDurata() ?? '') . '</td>';
                //controlla se la canzone è esplicita
                echo '<td>';
                if ($canzone->getEsplicita() == 1) {
                    echo "✔";
                } else {
                    echo "✘";
                }
                echo '</td>';
                echo '<td>' . htmlspecialchars($canzone->getAscolti() ?? '') . '</td>';
                //crea la cella contenente i link per modifica/eliminazione
                echo '<td>';
                echo '<a href="add.php?tipo=canzone&id=' . $canzone->getIdCanzone() . '">Modifica</a>';

                echo '<form method="POST" action="delete.php" onsubmit="return confirm(\'Sicuro?\');">';
                echo '<input type="hidden" name="id" value="' . $canzone->getIdCanzone() . '">';
                echo '<button type="submit" name="eliminaCanzone">Elimina</button>';
                echo '</form>';


                echo '</td>';

                //chiude la riga
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>

            
    <table id="tabellaAlbum">
        <thead>
            <tr>
            <th>Titolo</th>
            <th>Anno d'Uscita</th>
            <th>Genere</th>
            <th>Copertina</th>
            <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($album as $albumPreciso) {
                //crea una nuova riga
                echo '<tr>';
                //inserisce album
                echo '<td>' . htmlspecialchars($albumPreciso->getTitolo() ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($albumPreciso->getAnno() ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($albumPreciso->getGenere() ?? '') . '</td>';
                
                echo '<td>';
                if (!empty($albumPreciso->getCopertina())) {
                    echo '<img src="/images/' . htmlspecialchars($albumPreciso->getCopertina()) .   '">';
                }
                else{
                    echo 'Nessuna foto';
                }
                echo '</td>';

                //crea la cella con i link per modificare ed eliminare
                echo '<td>';
                echo '<a href="add.php?tipo=album&id=' . $albumPreciso->getIdAlbum() . '">Modifica</a>';
                
                echo '<form method="POST" action="delete.php" onsubmit="return confirm(\'Sei sicuro?\');">';
                echo '<input type="hidden" name="id" value="' . $albumPreciso->getIdAlbum() . '">';
                echo '<button type="submit" name="eliminaAlbum">Elimina</button>';
                echo '</form>';
                
                echo '</td>';

                //chiude la riga
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>

</body>
</html>