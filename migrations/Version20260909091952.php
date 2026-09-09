<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260909091952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Reference.type: simplify ReferenceTypeEnum down to Festivité/Festival/Rencontre/Spectacle/Récompense';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE reference SET type = 'Festival' WHERE type = 'Festival international'");
        $this->addSql("UPDATE reference SET type = 'Récompense' WHERE type = 'Festival récompense'");
        $this->addSql("UPDATE reference SET type = 'Festivité' WHERE type = 'Fête institutionnelle'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE reference SET type = 'Festival international' WHERE type = 'Festival' AND id IN (91, 93)");
        $this->addSql("UPDATE reference SET type = 'Festival récompense' WHERE type = 'Récompense' AND id = 92");
        $this->addSql("UPDATE reference SET type = 'Fête institutionnelle' WHERE type = 'Festivité' AND id = 94");
    }
}
