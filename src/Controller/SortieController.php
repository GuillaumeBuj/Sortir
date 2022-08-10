<?php

namespace App\Controller;

use App\Entity\EtatSortie;
use App\Entity\Sortie;
use App\Form\AnnulationType;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use App\Service\GestionEtat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    #[Route('/sortie/create', name: 'sortie_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sortie = new Sortie();
        $currentUser = $this->getUser();
        $sortie->setOrganisateur($currentUser);
        $sortie->setSiteOrganisateur($currentUser->getCampus());
        $repoEtat = $entityManager->getRepository(EtatSortie::class);
        $sortie->setEtat($repoEtat->findOneBy(array('libelle' => 'Créée')));


        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        if($sortieForm->isSubmitted() && $sortieForm->isValid()){
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success','Sortie créée!');
            return $this->redirectToRoute('main_home');
        }

        return $this->render('sortie/create.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }

    /*#[Route('/sortie/list', name: 'sortie_list')]
    public function list(SortieRepository $sortieRepository): Response
    {
        $sorties=$sortieRepository->findAll();

        return $this->render('sortie/list.html.twig', [
            "sorties"=>$sorties
        ]);
    }*/

   #[Route('/sortie/list', name: 'sortie_list')]
    public function listePubliees(SortieRepository $sortieRepository, GestionEtat $gestionEtat, EntityManagerInterface $entityManager): Response
    {
          $sorties=$sortieRepository->listeSortiesPubliees();

        //!!!!Mise à jour du statut!!!!
        foreach ($sorties as $sortie)
            {$gestionEtat->mettreAJour($sortie,$entityManager);}

        return $this->render('sortie/list.html.twig', [
            "sorties"=>$sorties
        ]);
    }

    #[Route('/sortie/mes_sorties', name: 'sortie_mes_sorties')]
    public function listeMesSorties(SortieRepository $sortieRepository, GestionEtat $gestionEtat, EntityManagerInterface $entityManager): Response
    {

        $user=$this->getUser()->getId();
        $sorties=$sortieRepository->listeSortiesParOrganisateur($user);

        //!!!!Mise à jour du statut!!!!
        foreach ($sorties as $sortie)
            {$gestionEtat->mettreAJour($sortie,$entityManager);}

        return $this->render('sortie/mes_sorties.html.twig', [
            "sorties"=>$sorties
        ]);
    }

    #[Route('/sortie/mes_inscriptions', name: 'sortie_mes_inscriptions')]
    public function listeMesInscriptions(/*SortieRepository $sortieRepository, */GestionEtat $gestionEtat, EntityManagerInterface $entityManager): Response
    {

        $user=$this->getUser();
        $sorties=$user->getParticipationsSorties();

        //!!!!Mise à jour du statut!!!!
        foreach ($sorties as $sortie)
        {$gestionEtat->mettreAJour($sortie,$entityManager);}

        return $this->render('sortie/mes_inscriptions.html.twig', [
            "sorties"=>$sorties
        ]);
    }

    #[Route('/sortie/participer/{id}', name:'sortie_participer')]
    public function participer(int $id, SortieRepository $sortieRepository, EntityManagerInterface $entityManager)
    {
        $sortie=$sortieRepository->find($id);
        $participants=$sortie->getParticipants();
        $currentUser=$this->getUser();

        //condition: état 'ouverte' et date limite d'inscription non dépassée
        if($sortie->getEtat()->getLibelle()=='Ouverte' && $sortie->getDateLimiteInscription()>new \DateTime('now'))
            $currentUser->participerA($sortie);

        $entityManager->flush();

        $this->addFlash('success','Inscrit!');
        return $this->redirectToRoute('sortie_list',[]);
    }

    #[Route('/sortie/se_desister/{id}', name:'sortie_se_desister')]
    public function seDesister(int $id, SortieRepository $sortieRepository, EntityManagerInterface $entityManager)
    {
        $sortie=$sortieRepository->find($id);
        $currentUser=$this->getUser();

        $currentUser->seDesister($sortie);

        $entityManager->flush();

        $this->addFlash('success','Désinscrit!');
        return $this->redirectToRoute('sortie_mes_inscriptions',[]);
    }

    #[Route('/sortie/annuler/{id}', name:'sortie_annuler')]
    public function annuler(int $id, Request $request,SortieRepository $sortieRepository, EntityManagerInterface $entityManager)
    {
        $sortie=$sortieRepository->find($id);

        $repoEtat = $entityManager->getRepository(EtatSortie::class);
        $sortie->setEtat($repoEtat->findOneBy(array('libelle' => 'Annulée')));

        $annulationForm = $this->createForm(AnnulationType::class, $sortie);
        $annulationForm->handleRequest($request);

        if($annulationForm->isSubmitted() && $annulationForm->isValid()){
            $entityManager->persist($sortie);
            $entityManager->flush();
        return $this->redirectToRoute('sortie_mes_sorties');
        }

        return $this->render('sortie/annuler.html.twig',[
            'annulationForm' => $annulationForm->createView(),
            'sortie'=>$sortie
        ]);
    }

    #[Route('/sortie/publier/{id}', name:'sortie_publier')]
    public function publier(int $id, Request $request,SortieRepository $sortieRepository, EntityManagerInterface $entityManager)
    {
        $sortie=$sortieRepository->find($id);

        $repoEtat = $entityManager->getRepository(EtatSortie::class);
        $sortie->setEtat($repoEtat->findOneBy(array('libelle' => 'Ouverte')));

        $entityManager->persist($sortie);
        $entityManager->flush();

        return $this->redirectToRoute('sortie_mes_sorties');
    }

    #[Route('/sortie/détails/{id}', name:'details')]
    public function details(Sortie $sortie)
    {
        return $this->render('sortie/details.html.twig',[
            'sortie'=>$sortie
        ]);
    }
}
