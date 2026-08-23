<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use Domain\MemberShip\Service\PdfInsurance;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FieldFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Infra\Symfony\Persistance\Doctrine\Entity\ClubYear;
use Infra\Symfony\Persistance\Doctrine\Entity\MemberShip;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Infra\Symfony\Service\CsvService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

#[AdminRoute(path: '/memberships', name: 'membership')]
class MemberShipCrudController extends AbstractCrudController
{
    public function __construct(private readonly CsvService $csvService, private readonly EntityManagerInterface $entityManager)
    {
    }

    public static function getEntityFqcn(): string
    {
        return MemberShip::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('MemberShip')
            ->setEntityLabelInPlural('MemberShip')
            ->setSearchFields(['id', 'subscriptionAmount'])
            ->setPaginatorPageSize(100)
            ->overrideTemplate('label/null', 'easy_admin/label_null.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        $export = Action::new('export', 'action.export')
            ->setIcon('fa fa-download')
            ->linkToCrudAction('export')
            ->setCssClass('btn')
            ->createAsGlobalAction();

        $renderInsurancePdf = Action::new('generateInsuranceDocument', 'Insurance')
            ->setIcon('fa fa-file-invoice')
            ->linkToCrudAction('generateInsuranceDocument')
            ->setCssClass('btn')
            ->displayIf(fn (MemberShip $entity) => $entity->isPaid() && $entity->getMember()->getInsurer() !== null);

        return $actions
            ->add(Crud::PAGE_INDEX, $export)
            ->add(Crud::PAGE_EDIT, $renderInsurancePdf)
            ->add(Crud::PAGE_DETAIL, $renderInsurancePdf);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('sections'));
    }

    public function createEntity(string $entityFqcn): object
    {
        $clubYear = $this->entityManager->getRepository(ClubYear::class)->findCurrentYear();

        $memberShip = new MemberShip();
        $memberShip->setClubYear($clubYear);

        return $memberShip;
    }

    public function configureFields(string $pageName): iterable
    {
        $clubYear = AssociationField::new('clubYear');
        $member = AssociationField::new('member');
        $sections = AssociationField::new('sections')
            ->setTemplatePath('admin/field/property_sections.html.twig');
        $startDate = DateField::new('startDate');
        $endDate = DateField::new('endDate');
        $subscriptionAmount = MoneyField::new('subscriptionAmount')
            ->setCurrency('EUR')
            ->setStoredAsCents(false);
        $subscripptionType = TextField::new('subscripptionType');
        $subscriptionPaidAt = DateField::new('subscriptionPaidAt');

        $age = NumberField::new('member.age');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$member, $age, $sections, $subscripptionType, $subscriptionAmount, $subscriptionPaidAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $startDate, $endDate, $subscriptionAmount, $subscriptionPaidAt, $clubYear, $member, $sections];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$clubYear, $member, $sections, $startDate, $endDate, $subscriptionAmount, $subscriptionPaidAt];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$clubYear, $member, $sections, $startDate, $endDate, $subscriptionAmount, $subscriptionPaidAt];
        }
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters): \Doctrine\ORM\QueryBuilder
    {
        $clubYear = $this->entityManager->getRepository(ClubYear::class)->findCurrentYear();

        return $this->container
            ->get(EntityRepository::class)
            ->createQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->leftJoin('entity.member', 'member')
            ->andWhere('entity.clubYear = :clubYear')
            ->setParameter('clubYear', $clubYear)
            ->addOrderBy('member.birthdate', 'DESC');
    }

    #[AdminRoute(path: '/export', name: 'export')]
    public function export(Request $request, FieldFactory $fieldFactory)
    {
        $context = $request->attributes->get(EA::CONTEXT_REQUEST_ATTRIBUTE);
        $fieldFactory = $this->container->get(FieldFactory::class);
        $configuredFields = $this->configureFields(Crud::PAGE_INDEX);
        $processedFields = $fieldFactory->processFields($context->getEntity(), $configuredFields);
        $fields = new FieldCollection($processedFields);
        $filters = $this->container->get(FilterFactory::class)->create(
            $context->getCrud()->getFiltersConfig(),
            $fields,
            $context->getEntity()
        );
        $members = $this->createIndexQueryBuilder($context->getSearch(), $context->getEntity(), $fields, $filters)
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($members as $member) {
            /** @var MemberShip $member */
            $data[] = $member->getExportData();
        }

        return $this->csvService->export($data, 'export_members_'.date_create()->format('d-m-y').'.csv');
    }

    #[AdminRoute(path: '/download-insurance', name: 'generateInsuranceDocument')]
    public function generateInsuranceDocument(AdminContext $context): BinaryFileResponse
    {
        $memberShip = $context->getEntity()->getInstance();
        $filename = "public/insurances/" . $memberShip->getPdfFileName("Insurance");
        $pdf = PdfInsurance::createDocument($memberShip);

        $test = __DIR__ . '/../../../../'.$filename;
        $pdf->Output(__DIR__ . '/../../../../'.$filename, "F");
        $pdf->freeResources();
        $fullName = realpath($test);

        $fileResponse = new BinaryFileResponse($fullName);
        $fileResponse->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $memberShip->getPdfFileName("Insurance"));

        return $fileResponse;
    }
}
