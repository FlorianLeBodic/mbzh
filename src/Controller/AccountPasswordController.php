<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AccountPasswordController extends AbstractController
{

    private ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    #[Route('/compte/modifier-mon-mot-de-passe', name: 'account_password')]
    public function index(Request $request, UserPasswordHasherInterface $hasher): Response
    {

        $notification = null;
        // Annotation à mettre au dessus de l'IDE afin qu'il est l'autocomplétion
        /**
         * @var ?User
         */
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->managerRegistry->getManager();

            $old_pwd = $form->get('old_password')->getData();

            if($hasher->isPasswordValid($user, $old_pwd)){
               $new_pwd = $form->get('new_password')->getData();
               $password = $hasher->hashPassword($user, $new_pwd);

                $user->setPassword($password);

                /** La méthode persist est vraiment là pour dire je sauvegarde cette entité en base quand elle est créé
                sinon le flush suffit */

                //$em->persist($user);
                $em->flush();
                $notification = "Votre mot de passe a bien été mis à jour";

            } else {
                $notification = "Votre mot de passe actuel n'est pas le bon";
            }

        }


        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification,
        ]);
    }
}
