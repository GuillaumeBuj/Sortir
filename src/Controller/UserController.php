<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user/{id}', name: 'user_profile')]
    public function profile(User $user): Response
    {

        return $this->render('user/profile.html.twig', [
            'user'=>$user
        ]);
    }

    #[Route('/user/edit/{id}', name: 'user_profile_edit')]
    public function profile_edit(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }

        if($this->getUser()!==$user){
            return $this->redirectToRoute('main_home');
        }

        $form = $this->createForm(UserType::class,$user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $user=$form->getData();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Profil mis à jour'
            );

            return $this->redirectToRoute('main_home');
        }

        return $this->render('user/profile_edit.html.twig', [
            'userForm'=>$form->createView(),
        ]);
    }

    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [

        ]);
    }
}
