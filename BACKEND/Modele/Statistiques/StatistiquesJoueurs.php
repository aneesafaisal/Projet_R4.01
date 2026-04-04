<?php

// Déclaration du namespace
namespace R301\Modele\Statistiques;

// Importation des classes nécessaires
use R301\Modele\Joueur\Joueur;
use R301\Modele\Participation\Poste;

// Classe représentant les statistiques d'un joueur, avec des méthodes pour calculer différentes statistiques basées sur les participations du joueur dans les rencontres
class StatistiquesJoueurs implements \JsonSerializable
{
    public readonly array $participations;
    public readonly array $rencontresJouees;

    // Constructeur de la classe StatistiquesJoueurs, prenant en paramètre un tableau de participations et un tableau de rencontres, triant les rencontres par date et filtrant celles qui ont été jouées
    public function __construct(
        array $participations,
        array $rencontres,
    ) {
        $this->participations = $participations;
        usort($rencontres, function ($a, $b) {
            return $a->getDateEtHeure() <=> $b->getDateEtHeure(); });
        $this->rencontresJouees = array_filter($rencontres, function ($rencontre) {
            return $rencontre->joue(); });
    }

    // Méthode privée pour filtrer les participations d'un joueur spécifique, en vérifiant que la rencontre associée à chaque participation a été jouée et que le participant correspond au joueur donné
    private function participationsDunJoueur(Joueur $joueur): array
    {
        return array_filter($this->participations, function ($participation) use ($joueur) {
            return $participation->getRencontre()->joue()
                && $participation->getParticipant()->getJoueurId() === $joueur->getJoueurId();
        });
    }

    // Méthode privée pour filtrer les participations d'un joueur spécifique à un poste donné, en vérifiant que la rencontre associée à chaque participation a été jouée, que le participant correspond au joueur donné et que le poste correspond au poste donné
    private function participationsDunJoueurAuPoste(Joueur $joueur, Poste $poste): array
    {
        return array_filter($this->participations, function ($participation) use ($joueur, $poste) {
            return $participation->getRencontre()->joue()
                && $participation->getPoste() === $poste
                && $participation->getParticipant()->getJoueurId() === $joueur->getJoueurId();
        });
    }

    // Méthode privée pour vérifier si un joueur a participé à une rencontre spécifique, en parcourant les participations du joueur et en vérifiant si l'identifiant de la rencontre correspond à celui de la rencontre donnée
    private function leJoueurAParticipeALaRencontre(Joueur $joueur, mixed $rencontre): bool
    {
        foreach ($this->participationsDunJoueur($joueur) as $participations) {
            if ($participations->getRencontre()->getRencontreId() === $rencontre->getRencontreId()) {
                return true;
            }
        }
        return false;
    }

    // Méthode publique pour déterminer le poste le plus performant d'un joueur, en calculant la moyenne des évaluations pour chaque poste et en retournant le poste avec la moyenne la plus élevée, ou null si le joueur n'a pas de participations
    public function posteLePlusPerformant(Joueur $joueur): ?Poste
    {
        $participations = $this->participationsDunJoueur($joueur);
        if (count($participations) === 0) {
            return null;
        } else {
            $moyenneParPoste = [];
            foreach (Poste::cases() as $poste) {
                $moyenneParPoste[$poste->name] = $this->moyenneDesEvaluationsPourLePoste($joueur, $poste);
            }

            arsort($moyenneParPoste);
            return Poste::fromName(array_key_first($moyenneParPoste));
        }
    }

    // Méthode publique pour calculer le nombre de rencontres consécutives auxquelles un joueur a participé, en parcourant les rencontres jouées dans l'ordre chronologique et en vérifiant si le joueur a participé à chaque rencontre jusqu'à ce qu'il trouve une rencontre à laquelle le joueur n'a pas participé
    public function nbRencontresConsecutivesADate(Joueur $joueur): int
    {
        $nbRencontresConsecutivesADate = 0;

        foreach ($this->rencontresJouees as $rencontre) {
            if ($this->leJoueurAParticipeALaRencontre($joueur, $rencontre)) {
                $nbRencontresConsecutivesADate++;
            } else {
                break;
            }
        }

        return $nbRencontresConsecutivesADate;
    }

    // Méthode publique pour calculer le nombre de titularisations d'un joueur, en filtrant les participations du joueur pour celles où il était titulaire et en comptant le nombre de participations restantes
    public function nbTitularisations(Joueur $joueur): int
    {
        return count(array_filter($this->participationsDunJoueur($joueur), function ($participation) {
            return $participation->estTitulaire();
        }));
    }

    // Méthode publique pour calculer le nombre de fois où un joueur était remplaçant, en filtrant les participations du joueur pour celles où il était remplaçant et en comptant le nombre de participations restantes
    public function nbRemplacant(Joueur $joueur): int
    {
        return count(array_filter($this->participationsDunJoueur($joueur), function ($participation) {
            return $participation->estRemplacant();
        }));
    }

    // Méthode privée pour calculer le nombre de matchs évalués pour un joueur, en filtrant les participations du joueur pour celles où la performance a été évaluée (c'est-à-dire que la note de performance n'est pas nulle) et en comptant le nombre de participations restantes
    private function nbMatchsEvalues(Joueur $joueur): int
    {
        return count(
            array_filter($this->participationsDunJoueur($joueur), function ($participation) {
                return $participation->getPerformance() !== null;
            })
        );
    }

    // Méthode privée pour calculer le nombre de matchs joués pour un joueur, en filtrant les participations du joueur pour celles où la rencontre associée a été jouée et en comptant le nombre de participations restantes
    private function nbMatchsJoues(Joueur $joueur): int
    {
        return count(
            array_filter($this->participationsDunJoueur($joueur), function ($participation) {
                return $participation->getRencontre()->getResultat() !== null;
            })
        );
    }

    // Méthode privée pour calculer le nombre de matchs gagnés pour un joueur, en filtrant les participations du joueur pour celles où la rencontre associée a été gagnée et en comptant le nombre de participations restantes
    private function nbMatchsGagnes(Joueur $joueur): int
    {
        return count(
            array_filter($this->participationsDunJoueur($joueur), function ($participation) {
                return $participation->getRencontre()->gagne();
            })
        );
    }

    // Méthode publique pour calculer la moyenne des évaluations d'un joueur, en filtrant les participations du joueur pour celles où la performance a été évaluée, en sommant les notes de performance de ces participations et en divisant par le nombre de participations évaluées, ou en retournant null si le joueur n'a pas de participations évaluées
    public function moyenneDesEvaluations(Joueur $joueur): ?float
    {
        $participations = $this->participationsDunJoueur($joueur);

        if ($this->nbMatchsEvalues($joueur) > 0) {
            return array_sum(array_map(function ($participation) {
                return $participation->notePerformance(); }, $participations)) / $this->nbMatchsEvalues($joueur);
        } else {
            return null;
        }
    }

    // Méthode privée pour calculer la moyenne des évaluations d'un joueur pour un poste spécifique, en filtrant les participations du joueur pour celles où le poste correspond au poste donné, en sommant les notes de performance de ces participations et en divisant par le nombre de participations évaluées pour ce poste, ou en retournant null si le joueur n'a pas de participations évaluées pour ce poste
    private function moyenneDesEvaluationsPourLePoste(Joueur $joueur, Poste $poste)
    {
        $participations = $this->participationsDunJoueurAuPoste($joueur, $poste);

        if ($this->nbMatchsEvalues($joueur) > 0) {
            return array_sum(array_map(function ($participation) {
                return $participation->notePerformance(); }, $participations)) / $this->nbMatchsEvalues($joueur);
        } else {
            return null;
        }
    }

    // Méthode publique pour calculer le pourcentage de matchs gagnés pour un joueur, en vérifiant que le nombre de matchs joués est supérieur à zéro, en divisant le nombre de matchs gagnés par le nombre de matchs joués et en multipliant par 100, ou en retournant null si le joueur n'a pas de matchs joués
    public function pourcentageDeMatchsGagnes(Joueur $joueur): ?int
    {
        if ($this->nbMatchsJoues($joueur) > 0) {
            return $this->nbMatchsGagnes($joueur) / $this->nbMatchsJoues($joueur) * 100;
        } else {
            return null;
        }
    }

    // Méthode pour sérialiser les statistiques des joueurs en un tableau associatif, incluant les statistiques calculées pour chaque joueur, utilisée pour la conversion en JSON
    public function jsonSerialize(): array
    {
        $joueurs = array_unique(
            array_map(
                function ($participation) {
                    return $participation->getParticipant(); },
                $this->participations
            ),
            SORT_REGULAR
        );

        $statsParJoueur = [];
        foreach ($joueurs as $joueur) {
            $statsParJoueur[] = [
                'joueur' => $joueur,
                'nbTitularisations' => $this->nbTitularisations($joueur),
                'nbRemplacant' => $this->nbRemplacant($joueur),
                'moyenneDesEvaluations' => $this->moyenneDesEvaluations($joueur),
                'pourcentageDeMatchsGagnes' => $this->pourcentageDeMatchsGagnes($joueur),
                'posteLePlusPerformant' => $this->posteLePlusPerformant($joueur)?->name,
                'nbRencontresConsecutivesADate' => $this->nbRencontresConsecutivesADate($joueur),
            ];
        }

        return $statsParJoueur;
    }

}


