<?php

declare(strict_types=1);

namespace Domain\Event\Enum;

enum EventTypeEnum: string
{
    case Cours = 'cours';
    case Stage = 'stage';
    case Spectacle = 'spectacle';
    case Voyage = 'voyage';
}
