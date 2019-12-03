<?php

include_once("PointDeTrace.class.php");

class Trace 
{
    private $id;
    private $dateHeureDebut;
    private $dateHeureFin; 
    private $terminee; 
    private $idUtilisateur;
    private $lesPointsDeTrace;
    
    public function __construct($unId, $uneDateHeureDebut, $uneDateHeureFin, $terminee, $unIdUtilisateur) {
        $this->id = $unId;
        $this->dateHeureDebut = $uneDateHeureDebut;
        $this->dateHeureFin = $uneDateHeureFin;
        $this->terminee = $terminee;
        $this->idUtilisateur = $unIdUtilisateur;
        $this->lesPointsDeTrace = array();
    }
    public function getId() {return $this->id;}
    public function setId($unId) {$this->id = $unId;}
    
    public function getDateHeureDebut() {return $this->dateHeureDebut;}
    public function setDateHeureDebut($uneDateHeureDebut) {$this->dateHeureDebut = $uneDateHeureDebut;}
    
    public function getDateHeureFin() {return $this->dateHeureFin;}
    public function setDateHeureFin($uneDateHeureFin) {$this->dateHeureFin= $uneDateHeureFin;}
    
    public function getTerminee() {return $this->terminee;}
    public function setTerminee($terminee) {$this->terminee = $terminee;} 
    
    public function getIdUtilisateur() {return $this->idUtilisateur;}
    public function setIdUtilisateur($unIdUtilisateur) {$this->idUtilisateur = $unIdUtilisateur;}
    
    public function getLesPointsDeTrace() {return $this->lesPointsDeTrace;}
    public function setLesPointsDeTrace($lesPointsDeTrace) {$this->lesPointsDeTrace = $lesPointsDeTrace;}
    
    public function toString() {
        $msg = "Id : " . $this->getId() . "<br>";
        $msg .= "Utilisateur : " . $this->getIdUtilisateur() . "<br>";
        if ($this->getDateHeureDebut() != null) {
            $msg .= "Heure de début : " . $this->getDateHeureDebut() . "<br>";
        }
        if ($this->getTerminee()) {
            $msg .= "Terminée : Oui  <br>";
        } 
        else {
            $msg .= "Terminée : Non  <br>";
        }
        $msg .= "Nombre de points : " . $this->getNombrePoints() . "<br>";
        if ($this->getNombrePoints() > 0) {
            if ($this->getDateHeureFin() != null) {
                $msg .= "Heure de fin : " . $this->getDateHeureFin() . "<br>";
            }
            $msg .= "Durée en secondes : " . $this->getDureeEnSecondes() . "<br>";
            $msg .= "Durée totale : " . $this->getDureeTotale() . "<br>";
            $msg .= "Distance totale en Km : " . $this->getDistanceTotale() . "<br>";
            $msg .= "Dénivelé en m : " . $this->getDenivele() . "<br>";
            $msg .= "Dénivelé positif en m : " . $this->getDenivelePositif() . "<br>";
            $msg .= "Dénivelé négatif en m : " . $this->getDeniveleNegatif() . "<br>";
            $msg .= "Vitesse moyenne en Km/h : " . $this->getVitesseMoyenne() . "<br>";
            $msg .= "Centre du parcours : " . "<br>";
            $msg .= "   - Latitude : " . $this->getCentre()->getLatitude() . "<br>";
            $msg .= "   - Longitude : "  . $this->getCentre()->getLongitude() . "<br>";
            $msg .= "   - Altitude : " . $this->getCentre()->getAltitude() . "<br>";
        }
        return $msg;
    }
    
    public function getNombrePoints() {return sizeof($this->lesPointsDeTrace);}
    
    public function getCentre() {
        $latitudeMin = $this->lesPointsDeTrace[0]->getLatitude();
        $latitudeMax = $this->lesPointsDeTrace[0]->getLatitude();
        
        $longitudeMin = $this->lesPointsDeTrace[0]->getLongitude();
        $longitudeMax = $this->lesPointsDeTrace[0]->getLongitude();
        
        foreach($this->lesPointsDeTrace as $unPoint){
            if ($unPoint->getLatitude() < $latitudeMin) {$latitudeMin = $unPoint->getLatitude();}
            if ($unPoint->getLatitude() > $latitudeMax) {$latitudeMax = $unPoint->getLatitude();}
            
            if ($unPoint->getLongitude() < $longitudeMin) {$longitudeMin = $unPoint->getLongitude();}
            if ($unPoint->getLongitude() > $longitudeMax) {$longitudeMax = $unPoint->getLongitude();}
        }
        
        return new Point(($latitudeMax + $latitudeMin) / 2, ($longitudeMax + $longitudeMin) / 2, 0);
    }
    
    public function getDenivele() {
        $altitudeMin = $this->lesPointsDeTrace[0]->getAltitude();
        $altitudeMax = $this->lesPointsDeTrace[0]->getAltitude();
        
        foreach($this->lesPointsDeTrace as $unPoint) {
            if ($unPoint->getAltitude() < $altitudeMin) {$altitudeMin = $unPoint->getAltitude();}
            if ($unPoint->getAltitude() > $altitudeMax) {$altitudeMax = $unPoint->getAltitude();}
        }
        
        return $altitudeMax - $altitudeMin;
    }
    
    public function getDureeEnSecondes() {
        if (sizeof($this->lesPointsDeTrace) == 0) {return 0;}
        
        return $this->lesPointsDeTrace[sizeof($this->lesPointsDeTrace) - 1]->getTempsCumule();
    }
    
    public function getDureeTotale() {
        $secondes = $this->getDureeEnSecondes();
        $heures = intdiv($secondes, 3600);
        $secondes -= $heures * 3600;
        $minutes = intdiv($secondes, 60);
        $secondes -= $minutes * 60;
        
        return sprintf("%02d",$heures) . ":" . sprintf("%02d",$minutes) . ":" . sprintf("%02d",$secondes);
    }
    
    public function getDistanceTotale() {
        if (sizeof($this->lesPointsDeTrace) == 0) {return 0;}
        
        return $this->lesPointsDeTrace[sizeof($this->lesPointsDeTrace) - 1]->getDistanceCumulee();
    }
    
    public function getDenivelePositif() {
        $denPosTot = 0;
        $i = 1;
        foreach($this->lesPointsDeTrace as $unPoint) {
            if ($i == sizeof($this->lesPointsDeTrace)) {break;}
            
            if ($unPoint->getAltitude() < $this->lesPointsDeTrace[$i]->getAltitude()) {
                $denPosTot += $this->lesPointsDeTrace[$i]->getAltitude() - $unPoint->getAltitude();
            }
            
            $i++;
        }
        return $denPosTot;
    }
    
    public function getDeniveleNegatif() {
        $denNegTot = 0;
        $i = 1;
        foreach($this->lesPointsDeTrace as $unPoint) {
            if ($i == sizeof($this->lesPointsDeTrace)) {break;}
            
            if ($unPoint->getAltitude() > $this->lesPointsDeTrace[$i]->getAltitude()) {
                $denNegTot += $unPoint->getAltitude() - $this->lesPointsDeTrace[$i]->getAltitude();
            }
            
            $i++;
        }
        return $denNegTot;
    }
    
    public function getVitesseMoyenne() {
        if (sizeof($this->lesPointsDeTrace) == 0) {return 0;}
        
        $km = $this->getDistanceTotale();
        $secondes = $this->getDureeEnSecondes();
        
        return $km / ($secondes / 3600);
    }
    
    public function ajouterPoint(PointDeTrace $unPoint){
        if (sizeof($this->lesPointsDeTrace) == 0) {
            $unPoint->setTempsCumule(0);
            $unPoint->setDistanceCumulee(0);
            $unPoint->setVitesse(0);
        }
        else {
            $dernierPoint = $this->lesPointsDeTrace[sizeof($this->lesPointsDeTrace) - 1];
            
            $duree = strtotime($unPoint->getDateHeure()) - strtotime($dernierPoint->getDateHeure());
            $unPoint->setTempsCumule($dernierPoint->getTempsCumule() + $duree);
            
            $distance = Point::getDistance($dernierPoint, $unPoint);
            $unPoint->setDistanceCumulee($dernierPoint->getDistanceCumulee() + $distance);
            
            if ($duree > 0) {
                $vitesse = $distance / $duree * 3600;
            }
            else {
                $vitesse = 0;
            }
            $unPoint->setVitesse($vitesse);
        }
        $this->lesPointsDeTrace[] = $unPoint;
    }
    
    public function viderListePoints() {
        $this->lesPointsDeTrace = array();
    }
}