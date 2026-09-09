<?php

declare(strict_types=1);

namespace Domain\BlogArticle\Enum;

enum BlogArticleTagEnum: string
{
    case Actualite = 'Actualité';
    case Spectacle = 'Spectacle';
    case Animation = 'Animation';
    case Rencontre = 'Rencontre';
}
