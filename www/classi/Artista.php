
<?php
class Artista
{
    private $IdArtista;
    private $Nome;
    private $Cognome;
    private $NomeArte;
    private $Eta;
    private $Nazionalita;
    private $Foto;

    public function __construct($IdArtista, $Nome, $Cognome, $NomeArte, $Eta, $Nazionalita, $Foto)
    {
        $this->setIdArtista($IdArtista);
        $this->setNome($Nome);
        $this->setCognome($Cognome);
        $this->setNomeArte($NomeArte);
        $this->setEta($Eta);
        $this->setNazionalita($Nazionalita);
        $this->setFoto($Foto);
    }

    public function setIdArtista($IdArtista)
    {
        $this->IdArtista = $IdArtista;
    }

    public function setNome($Nome)
    {
        $this->Nome = $Nome;
    }

    public function setCognome($Cognome)
    {
        $this->Cognome = $Cognome;
    }

    public function setNomeArte($NomeArte)
    {
        $this->NomeArte = $NomeArte;
    }

    public function setEta($Eta)
    {
        $this->Eta = $Eta;
    }

    public function setNazionalita($Nazionalita)
    {
        $this->Nazionalita = $Nazionalita;
    }

    public function setFoto($Foto)
    {
        $this->Foto = $Foto;
    }

    public function getIdArtista()
    {
        return $this->IdArtista;
    }

    public function getNome()
    {
        return $this->Nome;
    }

    public function getCognome()
    {
        return $this->Cognome;
    }

    public function getNomeArte()
    {
        return $this->NomeArte;
    }

    public function getEta()
    {
        return $this->Eta;
    }

    public function getNazionalita()
    {
        return $this->Nazionalita;
    }

    public function getFoto()
    {
        return $this->Foto;
    }
}
?>
