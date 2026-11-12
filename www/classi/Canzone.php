
<?php
class Canzone
{
    private $IdCanzone;
    private $Titolo;
    private $Anno;
    private $Durata;
    private $Esplicita;
    private $Ascolti;
    private $IdAlbum;

    public function __construct($IdCanzone, $Titolo, $Anno, $Durata, $Esplicita, $Ascolti, $IdAlbum)
    {
        $this->setIdCanzone($IdCanzone);
        $this->setTitolo($Titolo);
        $this->setAnno($Anno);
        $this->setDurata($Durata);
        $this->setEsplicita($Esplicita);
        $this->setAscolti($Ascolti);
        $this->setIdAlbum($IdAlbum);
    }

    public function setIdCanzone($IdCanzone)
    {
        $this->IdCanzone = $IdCanzone;
    }

    public function setTitolo($Titolo)
    {
        $this->Titolo = $Titolo;
    }

    public function setAnno($Anno)
    {
        $this->Anno = $Anno;
    }

    public function setDurata($Durata)
    {
        $this->Durata = $Durata;
    }

    public function setEsplicita($Esplicita)
    {
        $this->Esplicita = $Esplicita;
    }

    public function setAscolti($Ascolti)
    {
        $this->Ascolti = $Ascolti;
    }

    public function setIdAlbum($IdAlbum)
    {
        $this->IdAlbum = $IdAlbum;
    }

    public function getIdCanzone()
    {
        return $this->IdCanzone;
    }

    public function getTitolo()
    {
        return $this->Titolo;
    }

    public function getAnno()
    {
        return $this->Anno;
    }

    public function getDurata()
    {
        return $this->Durata;
    }

    public function getEsplicita()
    {
        return $this->Esplicita;
    }

    public function getAscolti()
    {
        return $this->Ascolti;
    }

    public function getIdAlbum()
    {
        return $this->IdAlbum;
    }
}
?>
