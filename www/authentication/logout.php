
<?php
session_start();        //recupera sessione corrente
session_unset();        //libera variabili di sessione
session_destroy();      //distrugge tutti i dati associati alla sessione corretnte
header("Location: /index.php");
exit();
?>
