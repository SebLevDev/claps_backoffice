<?php

declare(strict_types=1);

namespace Domain\Reference\Enum;

enum ReferenceTypeEnum: string
{
    case Festivite = 'Festivité';
    case Festival = 'Festival';
    case Rencontre = 'Rencontre';
    case Spectacle = 'Spectacle';
    case Recompense = 'Récompense';

    public function getIcon(): string
    {
        return match ($this) {
            self::Festivite => '🎉',
            self::Festival => '🌍',
            self::Rencontre => '🤝',
            self::Spectacle => '🎭',
            self::Recompense => '🏆',
        };
    }

    public function isFestival(): bool
    {
        return $this === self::Festival;
    }
}
