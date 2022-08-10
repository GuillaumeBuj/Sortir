<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VilleController extends AbstractController
{
    #[Route('/ville/create', name: 'ville_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ville = new Ville();
        $villeForm = $this->createForm(VilleType::class, $ville);

        $villeForm->handleRequest($request);

        if($villeForm->isSubmitted() && $villeForm->isValid()){
            $entityManager->persist($ville);
            $entityManager->flush();

            $this->addFlash('success','Ville créée!');
            return $this->redirectToRoute('main_home');
        }

        return $this->render('ville/create.html.twig', [
            'villeForm' => $villeForm->createView()
        ]);
    }
}
