<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260909090754 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Reference: replace icon (emoji) column with type (ReferenceTypeEnum), icon is now derived from type';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reference ADD type VARCHAR(30) DEFAULT NULL');

        // Only 6 rows currently have an icon set; migrated by id (safer than matching on the
        // emoji value itself — MySQL's collation on this column does not reliably distinguish
        // between some emoji in a WHERE clause).
        $this->addSql("UPDATE reference SET type = 'Festival international' WHERE id IN (91, 93)");
        $this->addSql("UPDATE reference SET type = 'Festival récompense' WHERE id IN (92)");
        $this->addSql("UPDATE reference SET type = 'Fête institutionnelle' WHERE id IN (94)");
        $this->addSql("UPDATE reference SET type = 'Spectacle' WHERE id IN (95, 96)");

        $this->addSql('ALTER TABLE reference DROP icon');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reference ADD icon VARCHAR(10) DEFAULT NULL');

        $this->addSql("UPDATE reference SET icon = '🌍' WHERE id IN (91, 93)");
        $this->addSql("UPDATE reference SET icon = '🏆' WHERE id IN (92)");
        $this->addSql("UPDATE reference SET icon = '🏛️' WHERE id IN (94)");
        $this->addSql("UPDATE reference SET icon = '🎭' WHERE id IN (95, 96)");

        $this->addSql('ALTER TABLE reference DROP type');
    }
}
