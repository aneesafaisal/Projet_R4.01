<?php

// Déclaration du namespace
namespace rencontre;

// Enumération représentant le sens du résultat d'une rencontre, soit gagné, égalité ou perdu
enum SensDuResultat {
    case GAGNE;
    case EGALITE;
    case PERDU;

    // Méthode pour récupérer le nom du sens du résultat
    public function getName(): string {
        return $this->name;
    }

    // Méthode statique pour déterminer le sens du résultat à partir d'un objet Resultat, en comparant les scores de l'équipe et des adversaires
    public static function fromResultat(Resultat $aPartirDuquelCalculerLeSens): SensDuResultat {
        $scoreDeLequipe = $aPartirDuquelCalculerLeSens->getScoreDeLequipe();
        $scoreDesAdversaires = $aPartirDuquelCalculerLeSens->getScoreDesAdversaires();

        if ($scoreDeLequipe > $scoreDesAdversaires) {
            return SensDuResultat::GAGNE;
        } else if ($scoreDeLequipe < $scoreDesAdversaires) {
            return SensDuResultat::PERDU;
        } else {
            return SensDuResultat::EGALITE;
        }
    }
}
