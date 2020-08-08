<?php

namespace App\Controller\Admin;

use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Blog Site - Administration');
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Blog');
        yield MenuItem::linkToCrud('Posts', 'fa fa-file-text', BlogPost::class);
        yield MenuItem::linkToCrud('Images', 'fa fa-images', Image::class);

        yield MenuItem::section('Users');
        yield MenuItem::linkToCrud('Comments', 'fa fa-comments', Comment::class);
        yield MenuItem::linkToCrud('Users', 'fa fa-users', User::class);
    }
}
