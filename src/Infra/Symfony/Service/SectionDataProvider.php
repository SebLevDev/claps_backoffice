<?php

declare(strict_types=1);

namespace Infra\Symfony\Service;

use Infra\Symfony\Persistance\Doctrine\Entity\Section;
use Infra\Symfony\Persistance\Doctrine\Entity\SectionImage;
use Infra\Symfony\Persistance\Doctrine\Repository\SectionRepository;

class SectionDataProvider
{
    public function __construct(private readonly SectionRepository $sectionRepository) {}

    /** @return array<int, array<string, mixed>> */
    public function getSections(): array
    {
        return array_map(
            $this->toArray(...),
            $this->sectionRepository->findBy([], ['position' => 'ASC', 'id' => 'ASC']),
        );
    }

    /** @return array<string, mixed>|null */
    public function getSectionBySlug(string $slug): ?array
    {
        $section = $this->sectionRepository->findOneBy(['slug' => $slug]);

        return $section === null ? null : $this->toArray($section);
    }

    /** @return array<string, mixed> */
    private function toArray(Section $section): array
    {
        $images = $section->getImages()->toArray();
        usort($images, static fn (SectionImage $a, SectionImage $b) => ($a->getPosition() ?? 0) <=> ($b->getPosition() ?? 0));

        return [
            'slug'    => $section->getSlug(),
            'title'   => $section->getName(),
            'age'     => $section->getAgeRange(),
            'desc'    => $section->getDescription(),
            'horaire' => $section->getSchedule(),
            'encad'   => $section->getInstructorName(),
            'images'  => array_map(
                static fn (SectionImage $image) => '/uploads/section_images/' . $image->getImage(),
                $images,
            ),
        ];
    }
}
