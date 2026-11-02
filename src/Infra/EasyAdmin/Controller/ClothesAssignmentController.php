<?php

declare(strict_types=1);

namespace Infra\EasyAdmin\Controller;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Infra\Symfony\Persistance\Doctrine\Entity\ClothesColor;
use Infra\Symfony\Persistance\Doctrine\Entity\ClothesCostume;
use Infra\Symfony\Persistance\Doctrine\Entity\ClothesCostumePiece;
use Infra\Symfony\Persistance\Doctrine\Entity\ClothesPiece;
use Infra\Symfony\Persistance\Doctrine\Entity\ClothesPieceStock;
use Infra\Symfony\Persistance\Doctrine\Entity\Member;
use Infra\Symfony\Persistance\Doctrine\Entity\Section;
use Infra\Symfony\Persistance\Doctrine\Repository\ClothesPieceStockRepository;
use Infra\Symfony\Persistance\Doctrine\Repository\ClubYearRepository;
use Infra\Symfony\Persistance\Doctrine\Repository\MemberShipRepository;
use Infra\Symfony\Service\ClothesAssignmentDataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Écran d'attribution des pièces de costume : matrice danseurs × pièces, modifiée en direct
 * (chaque action est enregistrée immédiatement, sans passer par les formulaires CRUD).
 */
#[AdminRoute(path: '/clothes/assignment', name: 'clothesAssignment')]
class ClothesAssignmentController extends AbstractController
{
    public const CSRF_TOKEN_ID = 'clothes_assignment';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ClothesAssignmentDataProvider $dataProvider,
    ) {}

    #[AdminRoute(path: '/', name: 'index')]
    public function index(Request $request, ClubYearRepository $clubYearRepository, MemberShipRepository $memberShipRepository): Response
    {
        $currentMemberIds = array_flip($memberShipRepository->findMemberIdsForYear($clubYearRepository->findCurrentYear()));
        $members = array_map(static fn (Member $m) => [
            'id' => $m->getId(),
            'name' => trim((string) $m),
            'current' => isset($currentMemberIds[$m->getId()]),
            'sex' => $m->getSex(),
        ], $this->entityManager->getRepository(Member::class)->findBy([], ['firstname' => 'ASC', 'lastname' => 'ASC']));

        return $this->render('admin/clothes_assignment/index.html.twig', [
            'costumes' => $this->entityManager->getRepository(ClothesCostume::class)->findBy([], ['name' => 'ASC', 'code' => 'ASC']),
            'sections' => $this->entityManager->getRepository(Section::class)->findBy([], ['position' => 'ASC', 'id' => 'ASC']),
            'colors' => $this->entityManager->getRepository(ClothesColor::class)->findBy([], ['name' => 'ASC']),
            'members' => $members,
            'selectedCostume' => $request->query->getInt('costume') ?: null,
            'selectedSection' => $request->query->getInt('section') ?: null,
            'selectedView' => 'member' === $request->query->getString('view') ? 'member' : 'costume',
            'selectedMember' => $request->query->getInt('member') ?: null,
            'selectedGroup' => 'country' === $request->query->getString('group') ? 'country' : 'costume',
            'selectedCountry' => $request->query->getString('country') ?: null,
            'selectedMemberGender' => $request->query->getString('sex'),
            'selectedGender' => $request->query->has('gender') ? $request->query->getString('gender') : null,
            'csrfTokenId' => self::CSRF_TOKEN_ID,
        ]);
    }

    #[AdminRoute(path: '/state', name: 'state')]
    public function state(Request $request): JsonResponse
    {
        $costume = $this->find(ClothesCostume::class, $request->query->getInt('costume'));
        $sectionId = $request->query->getInt('section');
        $section = $sectionId ? $this->find(Section::class, $sectionId) : null;

        $gender = $request->query->getString('gender');
        $gender = \array_key_exists($gender, ClothesCostume::getGenders()) ? $gender : null;

        return $this->json($this->dataProvider->getState($costume, $section, $gender));
    }

    #[AdminRoute(path: '/member', name: 'member')]
    public function member(Request $request): JsonResponse
    {
        return $this->json($this->dataProvider->getMemberState($this->find(Member::class, $request->query->getInt('member'))));
    }

    /** Change le porteur d'une pièce unique (null = retour en stock). */
    #[AdminRoute(path: '/keeper', name: 'keeper', options: ['methods' => ['POST']])]
    public function keeper(Request $request): JsonResponse
    {
        $data = $this->payload($request);
        $stock = $this->find(ClothesPieceStock::class, $data['stock'] ?? null);
        $this->assignKeeper($stock, $this->findMember($data['member'] ?? null));
        $this->entityManager->flush();

        return $this->json(['ok' => true]);
    }

    #[AdminRoute(path: '/maker', name: 'maker', options: ['methods' => ['POST']])]
    public function maker(Request $request): JsonResponse
    {
        $data = $this->payload($request);
        $stock = $this->find(ClothesPieceStock::class, $data['stock'] ?? null);
        $stock->setDressMaker($this->findMember($data['member'] ?? null));
        $this->entityManager->flush();

        return $this->json(['ok' => true]);
    }

    /** "Julie reprend tout le costume de Marine" : transfère toutes les pièces du costume d'un porteur à un autre. */
    #[AdminRoute(path: '/transfer', name: 'transfer', options: ['methods' => ['POST']])]
    public function transfer(Request $request, ClothesPieceStockRepository $stockRepository): JsonResponse
    {
        $data = $this->payload($request);
        $costume = $this->find(ClothesCostume::class, $data['costume'] ?? null);
        $from = $this->findMember($data['from'] ?? null) ?? throw new BadRequestHttpException('Porteur d\'origine manquant.');
        $to = $this->findMember($data['to'] ?? null);

        $pieceIds = $costume->getClothesCostumePieces()->map(static fn (ClothesCostumePiece $cp) => $cp->getPiece()->getId())->toArray();
        $stocks = [] === $pieceIds ? [] : $stockRepository->findForPiecesKeptBy($pieceIds, $from);
        foreach ($stocks as $stock) {
            $this->assignKeeper($stock, $to);
        }
        $this->entityManager->flush();

        return $this->json(['ok' => true, 'count' => \count($stocks)]);
    }

    #[AdminRoute(path: '/required', name: 'required', options: ['methods' => ['POST']])]
    public function required(Request $request): JsonResponse
    {
        $data = $this->payload($request);
        $costumePiece = $this->find(ClothesCostumePiece::class, $data['costumePiece'] ?? null);
        $costumePiece->setIsDefault((bool) ($data['required'] ?? false));
        $this->entityManager->flush();

        return $this->json(['ok' => true]);
    }

    #[AdminRoute(path: '/stock', name: 'stock', options: ['methods' => ['POST']])]
    public function createStock(Request $request): JsonResponse
    {
        $data = $this->payload($request);
        $piece = $this->find(ClothesPiece::class, $data['piece'] ?? null);
        $quantity = max(1, min(50, (int) ($data['quantity'] ?? 1)));
        $colors = array_map(fn ($id) => $this->find(ClothesColor::class, $id), (array) ($data['colors'] ?? []));
        $maker = $this->findMember($data['maker'] ?? null);
        // Création depuis une case de la matrice : la pièce est directement attribuée au danseur
        $keeper = $this->findMember($data['keeper'] ?? null);

        $stocks = [];
        for ($i = 0; $i < $quantity; ++$i) {
            $stocks[] = $stock = (new ClothesPieceStock())
                ->setClothesPiece($piece)
                ->setDressMaker($maker)
                ->setPersonal((bool) $piece->getPersonal());
            foreach ($colors as $color) {
                $stock->addColor($color);
            }
            $this->assignKeeper($stock, $keeper);
            $this->entityManager->persist($stock);
        }
        $this->entityManager->flush();

        return $this->json([
            'ok' => true,
            'count' => $quantity,
            'ids' => array_map(static fn (ClothesPieceStock $s) => $s->getId(), $stocks),
        ]);
    }

    private function assignKeeper(ClothesPieceStock $stock, ?Member $keeper): void
    {
        $stock
            ->setDressKeeper($keeper)
            ->setStatus(null === $keeper ? 'STOCK' : 'USED');
    }

    /** @return array<string, mixed> */
    private function payload(Request $request): array
    {
        if (!$this->isCsrfTokenValid(self::CSRF_TOKEN_ID, $request->headers->get('X-CSRF-Token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        return $request->toArray();
    }

    private function findMember(mixed $id): ?Member
    {
        return null === $id || '' === $id ? null : $this->find(Member::class, $id);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    private function find(string $class, mixed $id): object
    {
        return (is_numeric($id) ? $this->entityManager->find($class, (int) $id) : null)
            ?? throw new NotFoundHttpException(sprintf('%s #%s introuvable.', (new \ReflectionClass($class))->getShortName(), $id));
    }
}
