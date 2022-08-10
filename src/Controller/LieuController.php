<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LieuController extends AbstractController
{
    #[Route('/lieu/create', name: 'lieu_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class,$lieu);

        $lieuForm->handleRequest($request);

        if($lieuForm->isSubmitted() && $lieuForm->isValid()){
            $entityManager->persist($lieu);
            $entityManager->flush();

            $this->addFlash('success','Lieu créé!');
            return $this->redirectToRoute('main_home');
        }


        return $this->render('lieu/create.html.twig', [
            'lieuForm' => $lieuForm->createView(),
        ]);
    }



    #[Route('/lieu', name: 'app_lieu')]
    public function index(): Response
    {
        return $this->render('lieu/index.html.twig', [
            'controller_name' => 'LieuController',
        ]);
    }
}
