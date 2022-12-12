<?php

namespace App\Classe;

use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Cart
{

    private SessionInterface $session;
    private ManagerRegistry $managerRegistry;

    public function __construct(SessionInterface $session, ManagerRegistry $managerRegistry)
    {
        $this->session = $session;
        $this->managerRegistry = $managerRegistry;
    }

    public function add($id)
    {

        // Récupérer un panier
        $cart = $this->session->get('cart', []);

        // Si tu as déjà un produit dans ton panier et donc qu'il est pas vide
        if (!empty($cart[$id])) {
            // Tu ajoutes une quantité, un deuxième de quantité
            $cart[$id]++;
        } else {
            // Sinon tu l'initialise à 1
            $cart[$id] = 1;
        }

        // On va ensuite set le panier, c'est-à-dire le créer
        $this->session->set('cart', $cart);

    }

    public function get()
    {
        return $this->session->get('cart');
    }

    public function remove()
    {
        return $this->session->remove('cart');
    }

    public function delete($id)
    {
        // Récupérer un panier vide
        $cart = $this->session->get('cart', []);

        // On unset l'élément de la session
        unset($cart[$id]);

        // il faut donc ensuite retourner le nouveau panier sans l'élement unset sinon cela ne va supprimer qu'en apparence
        // il faut donc set à nouveau le panier
        return $this->session->set('cart', $cart);
    }

    public function decrease($id)
    {

        // Récupérer un panier
        $cart = $this->session->get('cart', []);

        // vérifier si la quantité de notre produit pas égal à 1 sinon c'est supprimer le produit
        //retirer une quantité (-1)
        if($cart[$id] > 1){
            $cart[$id]--;
        } else {
            unset($cart[$id]);
        }
        return $this->session->set('cart', $cart);
    }

    public function getFull()
    {
        $cartComplete = [];

        // Au lieu de $cart->get() on récupère la fonction qui est déjà là
        if ($this->get()) {
            foreach ($this->get() as $id => $quantity){

                $product_object = $this->managerRegistry->getRepository(Product::class)->findOneBy(['id' => $id]);

                // Si le produit n'existe pas en tapant cart/add/77777779999000000 on le supprimme
                if(!$product_object){
                    $this->delete($id);
                    //ensuite on sort de la boulce si le produit existe pas
                    continue;
                }

                $cartComplete[] = [
                    'product' => $product_object,
                    'quantity' => $quantity,
                ];
            }
        }
        return $cartComplete;
    }
}