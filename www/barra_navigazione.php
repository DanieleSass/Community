<?php
session_start();    //recupera eventuale sessione se esistente
?>


<nav class="barra_navigazione">  <!-- nav indica tutti i menu e le barre di navigazione-->
    <div class="logo">Community musica </div>

    <ul class="link">
        <li><a href="index.php">Home</a></li>
        <li><a href="add.php">Aggiungi</a></li>

        <?php
        if (isset($_SESSION['username'])) {
            echo '<li><a href="authentication/logout.php">Logout</a></li>';
        } else {
            session_unset();
            session_destroy();

            echo '<li><a href="authentication/register.php">Registrati</a></li>';
            echo '<li><a href="authentication/login.php">Login</a></li>';
        }
        ?>
    </ul>
</nav>