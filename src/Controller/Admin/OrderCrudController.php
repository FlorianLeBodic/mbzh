<?php

namespace App\Controller\Admin;

use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use MongoDB\Driver\Manager;
use function Sodium\add;

class OrderCrudController extends AbstractCrudController
{
    private ManagerRegistry $em;

    // Remplace le $crudUrlGenerator
    private AdminUrlGenerator $crudUrlGenerator;

    public function __construct(ManagerRegistry $em, AdminUrlGenerator $crudUrlGenerator){
        $this->em = $em;
        $this->crudUrlGenerator = $crudUrlGenerator;
    }


    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $updatePreparation = Action::new('updatePreparation','Préparation en cours', 'fas fa-box-open' )->linkToCrudAction('updatePreparation');
        $updateDelivery = Action::new('updateDelivery','Livraison en cours', 'fas fa-truck')->linkToCrudAction('updateDelivery');
        // On peut y accéder avec des contantes genre Crud:: si on modifie les pages
        return $actions
            ->add('detail', $updatePreparation)
            ->add('detail', $updateDelivery)
            ->add('index','detail');

    }

    // Permet d'afficher le bouton préparation en cours et de set la commande à préparation en cours
    public function updatePreparation(AdminContext $context){
        $order = $context->getEntity()->getInstance();
        $order->setState(2);
        $this->em->getManager()->flush();

        $this->addFlash('notice', "<span style='color:green;'><strong>La commande ".$order->getReference()." est bien <u>en cours de préparation</u></strong></span>");

        // Plus besoin du $this->crudUrlGenerator->build->setController avec le AdminUrlGenerator
        $url = $this->crudUrlGenerator->setController(OrderCrudController::class)
            ->setAction('index')
            ->generateUrl();

        //TODO rajouter la notif par email avec $order->getUser() + ajouter détail
        $mail = new Mail();
        $content = "Bonjour ". $order->getUser()->getFirstname() ."<br> Votre commande" .$order->getReference(). " est en cours de préparation<br>";
        $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Commande' .$order->getReference().'en préparation' , $content);

        return $this->redirect($url);
    }

    // Permet d'afficher le bouton livraison en cours et de set la commande à livraison en cours
    public function updateDelivery(AdminContext $context){
        $order = $context->getEntity()->getInstance();
        $order->setState(3);
        $this->em->getManager()->flush();

        $this->addFlash('notice', "<span style='color:blue;'><strong>La commande ".$order->getReference()." est bien <u>en cours de livraison</u></strong></span>");

        // Plus besoin du $this->crudUrlGenerator->build->setController avec le AdminUrlGenerator
        $url = $this->crudUrlGenerator->setController(OrderCrudController::class)
            ->setAction('index')
            ->generateUrl();

        //TODO rajouter la notif par email avec $order->getUser() + ajouter détail

        $mail = new Mail();
        $content = "Bonjour ". $order->getUser()->getFirstname() ."<br> Votre commande" .$order->getReference(). " est en cours de livraison<br>";
        $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Commande' .$order->getReference().'en cours de livraison' , $content);
        return $this->redirect($url);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id'=> 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            DateTimeField::new('createdAt', 'Passé le '),
            TextField::new('user.getFullName', 'Utilisateur'),
            // Permet d'interpréter le HTML et ->onlyOnDetails()
            TextEditorField::new('delivery', 'Adresse de livraison')->onlyOnDetail(),
            MoneyField::new('total', 'Total produit')->setCurrency('EUR'),
            TextField::new('carrierName', 'Transporteur'),
            MoneyField::new('carrierPrice', 'Frais de port')->setCurrency('EUR'),
            ChoiceField::new('state', 'Statut')->setChoices([
                'Non payé' => 0,
                'Payé' => 1,
                'Préparation en cours' => 2,
                'Livraison en cours' => 3,
            ]),
            //BooleanField::new('isPaid', 'Payée'),
            ArrayField::new('orderDetails', 'Produits achetés')->hideOnIndex(),
            ];

    }

}
