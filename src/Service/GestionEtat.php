<?php

namespace App\Service;

use App\Entity\EtatSortie;
use App\Entity\Sortie;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManager;

class GestionEtat
{


    public function mettreAJour(Sortie $sortie, EntityManager $entityManager)
    {
        $now = new \DateTime();

        $repoEtat = $entityManager->getRepository(EtatSortie::class);

        if($sortie->getDateHeureFin() < new \DateTime("-1 month")){
            $sortie->setEtat($repoEtat->findOneBy(array('libelle' => 'Archivée')));
        }

        else if ($sortie->getEtat() != $repoEtat->findOneBy(array('libelle' => 'Créée'))
            && $sortie->getEtat() != $repoEtat->findOneBy(array('libelle' => 'Annulée'))
            && $sortie->getEtat() != $repoEtat->findOneBy(array('libelle' => 'Passée')))
        {
            if ($sortie->getDateHeureFin() < $now)
                {$sortie->setEtat($repoEtat->findOneBy(array('libelle' => 'Passée'))); }

            else if($sortie->getDateHeureDebut() < $now && $sortie->getDateHeureFin() > $now)
                {$sortie->setEtat($repoEtat->findOneBy(array('libelle' => 'Activité en cours')));}

            else if ($sortie->getDateLimiteInscription() < $now
                || $sortie->getParticipants()->count()>=$sortie->getNbInscriptionsMax())
                {$sortie->setEtat($repoEtat->findOneBy(array('libelle' => 'Cloturée')));}
        }

        $entityManager->persist($sortie);
        $entityManager->flush();
    }
}
