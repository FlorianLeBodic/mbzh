<?php

namespace App\Controller;

use App\Classe\Search;
use App\Entity\Product;
use App\Form\SearchType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends AbstractController
{
    private ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    #[Route('/nos-produits', name: 'products')]
    public function index(Request$request): Response
    {

        // Nouvelle instance de formulaire
        $search = new Search();

        // Créer la méthode qui prend en paramètre le formulaire
        $form = $this->createForm(SearchType::class,$search);

        // Ecoute la requête passé au formulaire
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            // pas besoin car on l'a déjà passé avant avec $search lié via le createForm
            // $search= $form->getData();

            $products = $this->managerRegistry->getRepository(Product::class)->findWithSearch($search);
        } else
        {
            $products = $this->managerRegistry->getRepository(Product::class)->findAll();
        }

        return $this->render('product/index.html.twig',[
            'products' => $products,
            'form' => $form->createView()
        ]);
    }

    #[Route('/produit/{slug}', name: 'product')]
    public function show($slug): Response
    {
        $em = $this->managerRegistry->getManager();

        // On remplace findOneBySlug($slug) par findOneBy(['slug' => $slug]);
        $product = $this->managerRegistry->getRepository(Product::class)->findOneBy(['slug' => $slug]);
        $products = $this->managerRegistry->getRepository(Product::class)->findBy(['isBest' => 1]);

        if(!$product){
            return  $this->redirectToRoute('products');
        }

        return $this->render('product/show.html.twig',[
            'product' => $product,
            'products' => $products,
        ]);
    }

}
