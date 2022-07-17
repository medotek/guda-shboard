<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class AdminDashboardController extends AbstractDashboardController
{
    private AdminUrlGenerator $adminUrlGenerator;

    private Security $security;

    public function __construct(
        AdminUrlGenerator $adminUrlGenerator,
        Security $security)
    {
        $this->security = $security;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    /**
     * @Route("/gudadmin", name="admin")
     */
    public function index(): Response
    {
        $user = $this->security->getUser();

        if (!$user) {
            return $this->redirectToRoute('gudadmin_login');
        }

        return $this->redirect($this->adminUrlGenerator->setController(UserCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Guda-shboard');
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::section('Community'),
            MenuItem::linkToCrud('Users', 'fas fa-users', User::class),
        ];
    }
}
