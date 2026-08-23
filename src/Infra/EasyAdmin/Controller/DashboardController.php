<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use Infra\Symfony\Persistance\Doctrine\Entity\Barcode;
use Infra\Symfony\Persistance\Doctrine\Entity\BlogArticle;
use Infra\Symfony\Persistance\Doctrine\Entity\ClothesColor;
use Infra\Symfony\Persistance\Doctrine\Entity\ClothesCostume;
use Infra\Symfony\Persistance\Doctrine\Entity\ClothesOpportunity;
use Infra\Symfony\Persistance\Doctrine\Entity\ClothesPiece;
use Infra\Symfony\Persistance\Doctrine\Entity\ClothesPieceStock;
use Infra\Symfony\Persistance\Doctrine\Entity\ClothesSeason;
use Infra\Symfony\Persistance\Doctrine\Entity\ClothesTexture;
use Infra\Symfony\Persistance\Doctrine\Entity\ClothesType;
use Infra\Symfony\Persistance\Doctrine\Entity\ClothesTypeZone;
use Infra\Symfony\Persistance\Doctrine\Entity\Club;
use Infra\Symfony\Persistance\Doctrine\Entity\ClubYear;
use Infra\Symfony\Persistance\Doctrine\Entity\Dance;
use Infra\Symfony\Persistance\Doctrine\Entity\DocumentCategory;
use Infra\Symfony\Persistance\Doctrine\Entity\DocumentFile;
use Infra\Symfony\Persistance\Doctrine\Entity\Event;
use Infra\Symfony\Persistance\Doctrine\Entity\LoginHistory;
use Infra\Symfony\Persistance\Doctrine\Entity\Member;
use Infra\Symfony\Persistance\Doctrine\Entity\MemberFamily;
use Infra\Symfony\Persistance\Doctrine\Entity\MediaPhoto;
use Infra\Symfony\Persistance\Doctrine\Entity\MemberShip;
use Infra\Symfony\Persistance\Doctrine\Entity\Playlist;
use Infra\Symfony\Persistance\Doctrine\Entity\Reference;
use Infra\Symfony\Persistance\Doctrine\Entity\Section;
use Infra\Symfony\Persistance\Doctrine\Entity\SectionImage;
use Infra\Symfony\Persistance\Doctrine\Entity\User;
use Infra\Symfony\Persistance\Doctrine\Entity\Video;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[AdminDashboard(routePath: '/admin', routeName: 'admin_dashboard')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect($adminUrlGenerator->setController(MemberCrudController::class)->generateUrl());
    }

    #[Route('/admin/address_map', name: 'admin_dashboard_addressmap')]
    public function addressMap(): Response
    {
        return $this->render('admin/dashboard/addressesMap.html.twig', [
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Clap\'Sabots');
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            ->setDateFormat('dd/MM/yyyy')
            ->setDateTimeFormat('dd/MM/yyyy HH:mm:ss')
            ->setTimeFormat('HH:mm')
            ->overrideTemplate('label/null', 'easy_admin/label_null.html.twig');
    }

    public function configureActions(): Actions
    {
        $actions = parent::configureActions();

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::SAVE_AND_ADD_ANOTHER)
            ;
    }

    public function configureMenuItems(): iterable
    {
        $submenuAdmin = [
            MenuItem::linkTo(ClubCrudController::class, 'Club', 'fa fa-building'),
            MenuItem::linkTo(ClubYearCrudController::class, 'Années', 'fa fa-calendar-alt'),
            MenuItem::linkTo(SectionCrudController::class, 'Sections', 'fa fa-list'),
            MenuItem::linkTo(SectionImageCrudController::class, 'Photos des sections', 'fa fa-images'),
            MenuItem::linkTo(UserCrudController::class, 'Users', 'fa fa-user-circle-o'),
            MenuItem::linkTo(LoginHistoryCrudController::class, 'Login History', 'fa fa-user-circle-o'),
        ];

        $now = new \DateTimeImmutable();
        $submenuMember = [
            MenuItem::linkTo(MemberCrudController::class, 'Members', 'fa fa-user'),
            MenuItem::linkTo(MemberShipCrudController::class, 'MemberShip', 'fa fa-address-card'),
            MenuItem::linkTo(MemberFamilyCrudController::class, 'Familles', 'fa fa-users'),
        ];

        $submenuDocument = [
            MenuItem::linkTo(DocumentFileCrudController::class, 'Documents', 'fa fa-file'),
            MenuItem::linkTo(DocumentCategoryCrudController::class, 'Categories', 'fa fa-sitemap'),
        ];

        $submenuDance = [
            MenuItem::linkTo(DanceCrudController::class, 'Danses', 'fa fa-file'),
        ];

        $submenuClothe = [
            MenuItem::linkTo(ClothesCostumeCrudController::class, 'Costumes', 'fa fa-user-tie'),
            MenuItem::linkTo(ClothesPieceCrudController::class, 'Pièces', 'fa fa-tshirt'),
            MenuItem::linkTo(ClothesPieceStockCrudController::class, 'Stock', 'fa fa-cubes'),
            MenuItem::linkTo(ClothesTypeCrudController::class, 'Types', 'fa fa-filter'),
            MenuItem::linkTo(ClothesOpportunityCrudController::class, 'Opportunités', 'fa fa-glass-cheers'),
            MenuItem::linkTo(ClothesSeasonCrudController::class, 'Saisons', 'fa fa-cloud-sun'),
            MenuItem::linkTo(ClothesTextureCrudController::class, 'Textures', 'fa fa-feather'),
            MenuItem::linkTo(ClothesTypeZoneCrudController::class, 'Zones', 'fa fa-puzzle-piece'),
            MenuItem::linkTo(ClothesColorCrudController::class,'Couleurs', 'fa fa-paint-brush'),
        ];

        $submenuMedia = [
            MenuItem::linkTo(VideoCrudController::class, 'Vidéos', 'fa fa-film'),
            MenuItem::linkTo(MediaPhotoCrudController::class, 'Photos', 'fa fa-images'),
            MenuItem::linkToDashboard('Music <small>(soon)</small>', 'fa fa-music'),
            MenuItem::linkTo(PlaylistCrudController::class, 'Playlist', 'fa fa-list'),
        ];

        $submenuEvent = [
            MenuItem::linkTo(EventCrudController::class, 'Evenements', 'fa fa-calendar-alt'),
            MenuItem::linkTo(BarcodeCrudController::class, 'Codes à barres', 'fa fa-barcode'),
        ];

        $submenuMarketing = [
            MenuItem::linkToDashboard('Newsletter <small>(soon)</small>', 'fa fa-paper-plane'),
        ];

        $submenuWebsite = [
            MenuItem::linkTo(BlogArticleCrudController::class, 'Articles de blog', 'fa fa-newspaper'),
            MenuItem::linkTo(ReferenceCrudController::class, 'Références', 'fa fa-globe'),
        ];

        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            yield MenuItem::subMenu('Administration', 'fas fa-cogs')->setSubItems($submenuAdmin);
        }

        yield MenuItem::subMenu('Membres', 'fa fa-users')->setSubItems($submenuMember);
        yield MenuItem::subMenu('Documents', 'fa fa-file')->setSubItems($submenuDocument);
        yield MenuItem::subMenu('Dances', 'fas fa-file')->setSubItems($submenuDance);
        yield MenuItem::subMenu('Costumes', 'fas fa-tshirt')->setSubItems($submenuClothe);
        yield MenuItem::subMenu('Medias', 'fas fa-photo-video')->setSubItems($submenuMedia);
        yield MenuItem::subMenu('Event', 'fa fa-calendar-alt')->setSubItems($submenuEvent);
        yield MenuItem::subMenu('Site public', 'fas fa-globe')->setSubItems($submenuWebsite);
        yield MenuItem::subMenu('Marketing', 'fas fa-bullhorn')->setSubItems($submenuMarketing);
    }
}
