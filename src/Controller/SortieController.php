<?php

namespace App\Controller;

use App\Entity\EtatSortie;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\SortieRepository;
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
    }

        return $this->render('sortie/create.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }

    #[Route('/sortie/list', name: 'sortie_list')]
    public function list(SortieRepository $sortieRepository): Response
    {
        $sorties=$sortieRepository->findAll();

        return $this->render('sortie/list.html.twig', [
            "sorties"=>$sorties
        ]);
    }

    #[Route('/sortie/participer/{id}', name:'participer')]
    public function participer(int $id, SortieRepository $sortieRepository, EntityManagerInterface $entityManager)
    {
        $sortie=$sortieRepository->find($id);
        $participants=$sortie->getParticipants();
        $currentUser=$this->getUser();

        $currentUser->participerA($sortie);

        $entityManager->flush();

        return $this->render('sortie/details.html.twig',[
            'sortie'=>$sortie,
            'currentUser'=>$currentUser,
            'participants'=>$participants
        ]);
    }

    #[Route('/sortie/participer/{id}', name:'participer')]
    public function seDesister(int $id, SortieRepository $sortieRepository, EntityManagerInterface $entityManager)
    {
        $sortie=$sortieRepository->find($id);
        $currentUser=$this->getUser();

        $currentUser->participerA($sortie);

        $entityManager->flush();

        return $this->render('sortie/details.html.twig',[
            'sortie'=>$sortie,
            'currentUser'=>$currentUser,
            'participants'=>$participants
        ]);
    }

}
