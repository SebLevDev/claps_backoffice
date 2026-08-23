<?php

declare(strict_types=1);

namespace Domain\Reference\Enum;

enum ReferenceIconEnum: string
{
    case Tournee = '✈️';
    case FestivalInternational = '🌍';
    case Rencontre = '🤝';
    case FestivalRecompense = '🏆';
    case Spectacle = '🎭';
    case FeteInstitutionnelle = '🏛️';
    case FestivalRegional = '🎪';
    case FeteLocale = '🏘️';
    case Fete = '🎉';
    case Foire = '🏫';
    case FestivalMusical = '🎵';
}
