<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Infra\Symfony\Persistance\Doctrine\Entity\Address;
use Infra\Symfony\Persistance\Doctrine\Entity\BodyMeasurement;
use Infra\Symfony\Persistance\Doctrine\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Infra\Symfony\Service\CsvService;
use Symfony\Component\HttpFoundation\Request;

#[AdminRoute(path: '/members', name: 'member')]
class MemberCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly CsvService $csvService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Member::class;
    }

    public function createEntity(string $entityFqcn): object
    {
        $address = new Address();
        $address->setCountry('BE');

        $bodyMeasurement = new BodyMeasurement();

        $member = new Member();
        $member->setAddress($address);
        $member->setBodyMeasurement($bodyMeasurement);

        return $member;
    }

    public function updateEntity(
        EntityManagerInterface $entityManager,
        $entityInstance
    ): void {
        if ($entityInstance instanceof Member) {
            if (!$entityInstance->getAddress()) {
                $address = new Address();
                $address->setCountry('BE');
                $entityInstance->setAddress($address);
            }

            if (!$entityInstance->getBodyMeasurement()) {
                $bodyMeasurement = new BodyMeasurement();
                $entityInstance->setBodyMeasurement($bodyMeasurement);
            }
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function configureActions(Actions $actions): Actions
    {
        $export = Action::new('export', 'action.export')
            ->setIcon('fa fa-download')
            ->linkToCrudAction('export')
            ->setCssClass('btn')
            ->createAsGlobalAction();

        return $actions->add(Crud::PAGE_INDEX, $export);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('member.crud.title.singular')
            ->setEntityLabelInPlural('member.crud.title.plural')
            ->setSearchFields(['id', 'firstname', 'lastname', 'email', 'phone', 'mobilePhone', 'sex', 'niss', 'insurer'])
            ->setPaginatorPageSize(100)
            ->setDefaultSort(['lastname' => 'ASC'])
            ->overrideTemplate('label/null', 'easy_admin/label_null.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('member.crud.form.personal'),

            FormField::addFieldset('member.crud.form.personal'),
            IdField::new('id')->onlyOnDetail(),
            TextField::new('firstName', 'member.properties.firstname'),
            TextField::new('lastName', 'member.properties.lastname'),
            DateField::new('birthdate', 'member.properties.birthdate'),
            ChoiceField::new('sex',  'member.properties.sex')
                ->hideOnIndex()
                ->setChoices([ 'Male' => 'M', 'Female' => 'F']),
            TextField::new('niss')->hideOnIndex(),
            ChoiceField::new('insurer', 'member.properties.insurer')
                ->hideOnIndex()
                ->setChoices([
                    'PartenaMut' => 'PARTENA',
                    'Mutualité Solidaris' => 'SOLIDARIS',
                    'Mutualité Chrétienne' => 'MC',
                    'Mutualité Neutre' => 'MN'
                ]),

            FormField::addFieldset('member.crud.form.contact')->collapsible(),
            EmailField::new('email', 'member.properties.email'),
            TelephoneField::new('phone', 'member.properties.phone'),
            TelephoneField::new('mobilePhone', 'member.properties.mobilePhone'),

            FormField::addFieldset('member.crud.form.families')->collapsible(),
            AssociationField::new('families', 'member.properties.families')
                ->setTemplatePath('admin/field/property_family.html.twig'),

            FormField::addFieldset('member.crud.form.address')->collapsible(),
            TextField::new('address.street', 'address.properties.street')->hideOnIndex(),
            TextField::new('address.streetNumber', 'address.properties.streetNumber')->hideOnIndex(),
            TextField::new('address.streetBox', 'address.properties.streetBox')->hideOnIndex(),
            TextField::new('address.zipCode', 'address.properties.zipCode')->hideOnIndex(),
            TextField::new('address.city', 'address.properties.city')->hideOnIndex(),
            CountryField::new('address.country', 'address.properties.country')->hideOnIndex(),

            FormField::addTab('Affiliations'),
            AssociationField::new('memberShips', 'Affiliations')
                ->setTemplatePath('admin/field/property_membership.html.twig')
                ->onlyOnDetail(),

            FormField::addTab('member.crud.form.bodyMeasurement'),
            FormField::addFieldset('member.crud.form.bodyMeasurement'),
            IntegerField::new('bodyMeasurement.neck', 'body_measurement.properties.neck')->hideOnIndex()->setHelp("cm"),
            IntegerField::new('bodyMeasurement.bust', 'body_measurement.properties.bust')->hideOnIndex()->setHelp("cm"),
            IntegerField::new('bodyMeasurement.underBust', 'body_measurement.properties.underBust')->hideOnIndex()->setHelp("cm"),
            IntegerField::new('bodyMeasurement.waist', 'body_measurement.properties.waist')->hideOnIndex()->setHelp("cm"),
            IntegerField::new('bodyMeasurement.hips', 'body_measurement.properties.hips')->hideOnIndex()->setHelp("cm"),
            IntegerField::new('bodyMeasurement.thigh', 'body_measurement.properties.thigh')->hideOnIndex()->setHelp("cm"),
            IntegerField::new('bodyMeasurement.calf', 'body_measurement.properties.calf')->hideOnIndex()->setHelp("cm"),
            IntegerField::new('bodyMeasurement.biceps', 'body_measurement.properties.biceps')->hideOnIndex()->setHelp("cm"),
            IntegerField::new('bodyMeasurement.wrist', 'body_measurement.properties.wrist')->hideOnIndex()->setHelp("cm"),
            IntegerField::new('bodyMeasurement.shoulder', 'body_measurement.properties.shoulder')->hideOnIndex()->setHelp("cm"),
            IntegerField::new('bodyMeasurement.armLength', 'body_measurement.properties.armLength')->hideOnIndex()->setHelp("cm"),
            IntegerField::new('bodyMeasurement.bustHeight', 'body_measurement.properties.bustHeight')->hideOnIndex()->setHelp("cm"),
            IntegerField::new('bodyMeasurement.shoulderToWaistFront', 'body_measurement.properties.shoulderToWaistFront')->hideOnIndex()->setHelp("cm"),
            IntegerField::new('bodyMeasurement.shoulderToWaistBack', 'body_measurement.properties.shoulderToWaistBack')->hideOnIndex()->setHelp("cm"),
            IntegerField::new('bodyMeasurement.backWidth', 'body_measurement.properties.backWidth')->hideOnIndex()->setHelp("cm"),
            IntegerField::new('bodyMeasurement.hipHeight', 'body_measurement.properties.hipHeight')->hideOnIndex()->setHelp("cm"),
            IntegerField::new('bodyMeasurement.legLength', 'body_measurement.properties.legLength')->hideOnIndex()->setHelp("cm"),
            IntegerField::new('bodyMeasurement.waistToFloor', 'body_measurement.properties.waistToFloor')->hideOnIndex()->setHelp("cm"),
            IntegerField::new('bodyMeasurement.neckToFloor', 'body_measurement.properties.neckToFloor')->hideOnIndex()->setHelp("cm"),
            IntegerField::new('bodyMeasurement.totalHeight', 'body_measurement.properties.totalHeight')->hideOnIndex()->setHelp("cm"),
            IntegerField::new('bodyMeasurement.shoeSize', 'body_measurement.properties.shoeSize')->hideOnIndex()->setHelp("cm"),

        ];
    }

    #[AdminRoute(path: '/export', name: 'export')]
    public function export(Request $request)
    {
        $context = $request->attributes->get(EA::CONTEXT_REQUEST_ATTRIBUTE);
        $fields = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));
        $filters = $this->container->get(FilterFactory::class)->create($context->getCrud()->getFiltersConfig(), $fields, $context->getEntity());
        $members = $this->createIndexQueryBuilder($context->getSearch(), $context->getEntity(), $fields, $filters)
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($members as $member) {
            $data[] = $member->getExportData();
        }

        return $this->csvService->export($data, 'export_members_'.date_create()->format('d-m-y').'.csv');
    }
}
