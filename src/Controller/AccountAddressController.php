<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Address;
use App\Form\AddressType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountAddressController extends AbstractController
{

    private ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    #[Route('/compte/adresses', name: 'account_address')]
    public function index(): Response
    {
        return $this->render('account/address.html.twig');
    }

    // On peut chercher les adresses avec le Repository mais pour le User on a le getUser pour récupérer l'utilisateur connecté mais
    // Encore mieux on va directement le faire dans Twig avec app.user

    #[Route('/compte/ajouter-une-adresse', name: 'account_address_add')]
    public function add(Request $request, Cart $cart): Response
    {

        // On veut le form AdressType dans le createForm dépendante du controleur et en 2ème paramètre une instance de la classe en question ici Adress

        $address = new Address();

        $form = $this->createForm(AddressType::class, $address);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->managerRegistry->getManager();

            $address->setUser($this->getUser());
            $em->persist($address);
            $em->flush();
            if ($cart->get()){
                return $this->redirectToRoute('order');
            } else {
                return $this->redirectToRoute('account_address');
            }
        }

        return $this->render('account/address_form.html.twig', [
            'form' => $form->createView()
        ]);
    }


    #[Route('/compte/modifier-une-adresse/{id}', name: 'account_address_edit')]
    public function edit(Request $request, $id): Response
    {

        $address = $this->managerRegistry->getRepository(Address::class)->findOneBy(['id' => $id]);

        // Est-ce que mon adresse existe et est-ce que l'adresse que je viens de récupérer appartient à mon utilisateur
        if(!$address || $address->getUser() != $this->getUser()){
            return $this->redirectToRoute('account_adress');
        }

        $form = $this->createForm(AddressType::class, $address);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->managerRegistry->getManager();

        // Plus besoin car ça seulement pour le new
        // $address->setUser($this->getUser());
        // $em->persist($address);

            $em->flush();
            return $this->redirectToRoute('account_address');

        }

        return $this->render('account/address_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/compte/supprimer-une-adresse/{id}', name: 'account_address_delete')]
    public function delete(Request $request, $id): Response
    {

        $address = $this->managerRegistry->getRepository(Address::class)->findOneBy(['id' => $id]);

        // Je veux que l'adresse existe et que je sois l'utilisateur concerné qui l'a supprimme
        if($address && $address->getUser() == $this->getUser()){
            $em = $this->managerRegistry->getManager();

            $em->remove($address);
            $em->flush();

        }


            return $this->redirectToRoute('account_address');


    }
}
