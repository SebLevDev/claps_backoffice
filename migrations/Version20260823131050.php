<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260823131050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add position field on Section, for custom display ordering';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE section ADD position INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE section DROP position');
    }
}
