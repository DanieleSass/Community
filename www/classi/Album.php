
<?php
class Album
{
    private $IdAlbum;
    private $Titolo;
    private $Anno;
    private $Genere;
    private $Copertina;

    public function __construct($IdAlbum, $Titolo, $Anno, $Genere, $Copertina)
    {
        $this->setIdAlbum($IdAlbum);
        $this->setTitolo($Titolo);
        $this->setAnno($Anno);
        $this->setGenere($Genere);
        $this->setCopertina($Copertina);
    }

    public function setIdAlbum($IdAlbum)
    {
        $this->IdAlbum = $IdAlbum;
    }

    public function setTitolo($Titolo)
    {
        $this->Titolo = $Titolo;
    }

    public function setAnno($Anno)
    {
        $this->Anno = $Anno;
    }

    public function setGenere($Genere)
    {
        $this->Genere = $Genere;
    }

    public function setCopertina($Copertina)
    {
        $this->Copertina = $Copertina;
    }

    public function getIdAlbum()
    {
        return $this->IdAlbum;
    }

    public function getTitolo()
    {
        return $this->Titolo;
    }

    public function getAnno()
    {
        return $this->Anno;
    }

    public function getGenere()
    {
        return $this->Genere;
    }

    public function getCopertina()
    {
        return $this->Copertina;
    }
}
?>
