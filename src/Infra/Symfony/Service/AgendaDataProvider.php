<?php

declare(strict_types=1);

namespace Infra\Symfony\Service;

use Domain\Event\Enum\EventTypeEnum;
use Infra\Symfony\Persistance\Doctrine\Entity\Event;
use Infra\Symfony\Persistance\Doctrine\Repository\EventRepository;

class AgendaDataProvider
{
    public function __construct(private readonly EventRepository $eventRepository) {}

    /** @return array<int, array<string, mixed>> */
    public function getEvents(): array
    {
        return array_map($this->toArray(...), $this->eventRepository->findUpcoming());
    }

    /** @return array<int, string> */
    public function getTypes(): array
    {
        return array_map(static fn (EventTypeEnum $type) => $type->value, EventTypeEnum::cases());
    }

    /** @return array<string, mixed> */
    private function toArray(Event $event): array
    {
        return [
            'date'    => $event->getDate(),
            'endDate' => $event->getEndDate(),
            'title'   => $event->getName(),
            'lieu'    => $event->getVenue(),
            'type'    => $event->getType()?->value,
        ];
    }
}
