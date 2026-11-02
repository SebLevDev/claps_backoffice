<?php

declare(strict_types=1);

namespace Infra\Symfony\Service;

use Infra\Symfony\Persistance\Doctrine\Entity\ClothesColor;
use Infra\Symfony\Persistance\Doctrine\Entity\ClothesCostume;
use Infra\Symfony\Persistance\Doctrine\Entity\ClothesCostumePiece;
use Infra\Symfony\Persistance\Doctrine\Entity\ClothesPieceStock;
use Infra\Symfony\Persistance\Doctrine\Entity\Member;
use Infra\Symfony\Persistance\Doctrine\Entity\Section;
use Infra\Symfony\Persistance\Doctrine\Repository\ClothesPieceStockRepository;
use Infra\Symfony\Persistance\Doctrine\Repository\ClubYearRepository;
use Infra\Symfony\Persistance\Doctrine\Repository\MemberShipRepository;

/**
 * Construit l'état de la matrice "danseurs × pièces" d'un costume pour l'écran d'attribution.
 */
class ClothesAssignmentDataProvider
{
    public function __construct(
        private readonly MemberShipRepository $memberShipRepository,
        private readonly ClothesPieceStockRepository $stockRepository,
        private readonly ClubYearRepository $clubYearRepository,
    ) {}

    /**
     * @param string|null $gender 'M', 'F' ou null pour tous les sexes
     *
     * @return array<string, mixed>
     */
    public function getState(ClothesCostume $costume, ?Section $section, ?string $gender): array
    {
        $clubYear = $this->clubYearRepository->findCurrentYear();
        $sections = null !== $section ? [$section] : $costume->getSections()->toArray();
        $genders = null !== $gender ? [$gender] : null;

        $memberShips = [] === $sections
            ? []
            : $this->memberShipRepository->findForCostumeAssignation($clubYear, $sections, $genders);

        $costumePieces = $costume->getClothesCostumePieces()->toArray();
        usort($costumePieces, static fn (ClothesCostumePiece $a, ClothesCostumePiece $b) => [!$a->getIsDefault(), $a->getPiece()->getName()] <=> [!$b->getIsDefault(), $b->getPiece()->getName()]);

        $pieceIds = array_map(static fn (ClothesCostumePiece $cp) => $cp->getPiece()->getId(), $costumePieces);
        $stocks = [] === $pieceIds ? [] : $this->stockRepository->findForPieces($pieceIds);

        $dancers = [];
        foreach ($memberShips as $memberShip) {
            $member = $memberShip->getMember();
            $dancers[$member->getId()] ??= $this->memberToArray($member) + ['outside' => false];
        }

        // Les porteurs qui ne font pas partie de la liste (autre section, ancien membre...) sont
        // ajoutés en fin de tableau : c'est souvent chez eux qu'il faut aller récupérer les pièces.
        $currentMemberIds = array_flip($this->memberShipRepository->findMemberIdsForYear($clubYear));
        $outsiders = [];
        foreach ($stocks as $stock) {
            $keeper = $stock->getDressKeeper();
            if (null !== $keeper && !isset($dancers[$keeper->getId()])) {
                $outsiders[$keeper->getId()] ??= $this->memberToArray($keeper) + [
                    'outside' => true,
                    'former' => !isset($currentMemberIds[$keeper->getId()]),
                ];
            }
        }
        uasort($outsiders, static fn (array $a, array $b) => $a['name'] <=> $b['name']);

        return [
            'costume' => [
                'id' => $costume->getId(),
                'name' => (string) $costume,
                'sections' => array_map(static fn (Section $s) => $s->getName(), $costume->getSections()->toArray()),
                'genders' => $costume->getGender() ?? [],
            ],
            'pieces' => array_map(fn (ClothesCostumePiece $cp) => [
                'costumePieceId' => $cp->getId(),
                'id' => $cp->getPiece()->getId(),
                'name' => $cp->getPiece()->getName(),
                'code' => $cp->getPiece()->getCode(),
                'required' => (bool) $cp->getIsDefault(),
                'personal' => (bool) $cp->getPiece()->getPersonal(),
            ], $costumePieces),
            'stocks' => array_map($this->stockToArray(...), $stocks),
            'dancers' => array_values($dancers),
            'outsiders' => array_values($outsiders),
        ];
    }

    /**
     * Vue "par danseur" : tout ce qu'un membre porte, et pour chaque costume concerné
     * la liste complète des pièces (pour voir ce qui lui manque).
     *
     * @return array<string, mixed>
     */
    public function getMemberState(Member $member): array
    {
        $stocks = $this->stockRepository->findKeptBy($member);

        $costumes = [];
        foreach ($stocks as $stock) {
            foreach ($stock->getClothesPiece()->getClothesCostumePieces() as $costumePiece) {
                $costume = $costumePiece->getCostume();
                $costumes[$costume->getId()] ??= [
                    'id' => $costume->getId(),
                    'name' => $costume->getName(),
                    'code' => $costume->getCode(),
                    'country' => $costume->getCountry(),
                    'pieces' => array_values(array_map(static fn (ClothesCostumePiece $cp) => [
                        'id' => $cp->getPiece()->getId(),
                        'name' => $cp->getPiece()->getName(),
                        'code' => $cp->getPiece()->getCode(),
                        'required' => (bool) $cp->getIsDefault(),
                    ], $costume->getClothesCostumePieces()->toArray())),
                ];
            }
        }
        uasort($costumes, static fn (array $a, array $b) => [$a['country'], $a['name']] <=> [$b['country'], $b['name']]);

        $clubYear = $this->clubYearRepository->findCurrentYear();

        return [
            'member' => $this->memberToArray($member) + [
                'current' => \in_array($member->getId(), $this->memberShipRepository->findMemberIdsForYear($clubYear)),
            ],
            'stocks' => array_map(fn (ClothesPieceStock $stock) => $this->stockToArray($stock) + [
                'piece' => [
                    'id' => $stock->getClothesPiece()->getId(),
                    'name' => $stock->getClothesPiece()->getName(),
                    'code' => $stock->getClothesPiece()->getCode(),
                    'country' => $stock->getClothesPiece()->getCountry(),
                    'costumes' => array_map(static fn (ClothesCostumePiece $cp) => $cp->getCostume()->getId(), $stock->getClothesPiece()->getClothesCostumePieces()->toArray()),
                ],
            ], $stocks),
            'costumes' => array_values($costumes),
        ];
    }

    /** @return array<string, mixed> */
    private function memberToArray(Member $member): array
    {
        return [
            'id' => $member->getId(),
            'name' => trim((string) $member),
            'sex' => $member->getSex(),
        ];
    }

    /** @return array<string, mixed> */
    private function stockToArray(ClothesPieceStock $stock): array
    {
        return [
            'id' => $stock->getId(),
            'pieceId' => $stock->getClothesPiece()->getId(),
            'status' => $stock->getStatus(),
            'personal' => $stock->getPersonal(),
            'colors' => array_map(static fn (ClothesColor $c) => ['name' => $c->getName(), 'code' => $c->getCode()], $stock->getColors()->toArray()),
            'maker' => null !== $stock->getDressMaker() ? $this->memberToArray($stock->getDressMaker()) : null,
            'keeper' => null !== $stock->getDressKeeper() ? $this->memberToArray($stock->getDressKeeper()) : null,
        ];
    }
}
