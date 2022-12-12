<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    private ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry){

        $this->managerRegistry = $managerRegistry;
    }



    // L'utilisateur doit voir son panier
    #[Route('/mon-panier', name: 'cart')]
    public function index(Cart $cart): Response
    {


        /** Fonction pas propre pas rapport aux autres méthodes
         *
         * $cartComplete = [];
         *
        if ($cart->get()) {
            foreach ($cart->get() as $id => $quantity){
                $cartComplete[] = [
                'product' => $this->managerRegistry->getRepository(Product::class)->findOneBy(['id' => $id]),
                'quantity' => $quantity
                ];
            }
        }
        */

        return $this->render('cart/index.html.twig', [
            'cart' => $cart->getFull()
        ]);
    }

    // Injection de dépendance
    #[Route('/cart/add/{id}', name: 'add_to_cart')]
    public function add(Cart $cart, $id): Response
    {
        $cart->add($id);

        // on appelle la route vers laquelle on veut rediriger
        return $this->redirectToRoute('cart');
    }

    // Injection de dépendance
    #[Route('/cart/remove', name: 'remove_my_cart')]
    public function remove(Cart $cart): Response
    {
       $cart->remove();

        // on appelle la route vers laquelle on veut rediriger
        return $this->redirectToRoute('products');
    }

    // Injection de dépendance
    #[Route('/cart/delete/{id}', name: 'delete_to_cart')]
    public function delete(Cart $cart, $id): Response
    {
        $cart->delete($id);

        // on appelle la route vers laquelle on veut rediriger
        return $this->redirectToRoute('cart');
    }

    // Injection de dépendance
    #[Route('/cart/decrease/{id}', name: 'decrease_to_cart')]
    public function decrease(Cart $cart, $id): Response
    {
        $cart->decrease($id);

        // on appelle la route vers laquelle on veut rediriger
        return $this->redirectToRoute('cart');
    }
}
