<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260909075528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Event.endDate (multi-day events for the public agenda)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event ADD end_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event DROP end_date');
    }
}
