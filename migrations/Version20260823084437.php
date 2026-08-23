<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Frontoffice content entities: BlogArticle, Reference, MediaPhoto, SectionImage,
 * plus new fields on Section (slug/ageRange/description/schedule/instructorName)
 * and on Event (type).
 */
final class Version20260823084437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add frontoffice content entities (BlogArticle, Reference, MediaPhoto, SectionImage) and extend Section/Event';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE blog_article (
              id INT AUTO_INCREMENT NOT NULL,
              slug VARCHAR(255) NOT NULL,
              tag VARCHAR(255) DEFAULT NULL,
              title VARCHAR(255) NOT NULL,
              date DATETIME DEFAULT NULL,
              resume TEXT DEFAULT NULL,
              content LONGTEXT DEFAULT NULL,
              image VARCHAR(255) DEFAULT NULL,
              updated_at DATETIME NOT NULL,
              is_published TINYINT NOT NULL,
              UNIQUE INDEX UNIQ_EECCB3E5989D9B62 (slug),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE media_photo (
              id INT AUTO_INCREMENT NOT NULL,
              image VARCHAR(255) DEFAULT NULL,
              caption VARCHAR(255) DEFAULT NULL,
              position INT DEFAULT NULL,
              updated_at DATETIME NOT NULL,
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reference (
              id INT AUTO_INCREMENT NOT NULL,
              icon VARCHAR(10) DEFAULT NULL,
              name VARCHAR(255) NOT NULL,
              city VARCHAR(255) DEFAULT NULL,
              country VARCHAR(255) DEFAULT NULL,
              year INT DEFAULT NULL,
              lat DOUBLE PRECISION DEFAULT NULL,
              lng DOUBLE PRECISION DEFAULT NULL,
              description TEXT DEFAULT NULL,
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE section_image (
              id INT AUTO_INCREMENT NOT NULL,
              image VARCHAR(255) DEFAULT NULL,
              updated_at DATETIME NOT NULL,
              position INT DEFAULT NULL,
              section_id INT DEFAULT NULL,
              INDEX IDX_526A633BD823E37A (section_id),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              section_image
            ADD
              CONSTRAINT FK_526A633BD823E37A FOREIGN KEY (section_id) REFERENCES section (id)
        SQL);
        $this->addSql('ALTER TABLE event ADD type VARCHAR(255) DEFAULT NULL');
        $this->addSql(<<<'SQL'
            ALTER TABLE
              section
            ADD
              slug VARCHAR(255) DEFAULT NULL,
            ADD
              age_range VARCHAR(255) DEFAULT NULL,
            ADD
              description TEXT DEFAULT NULL,
            ADD
              schedule VARCHAR(255) DEFAULT NULL,
            ADD
              instructor_name VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2D737AEF989D9B62 ON section (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE section_image DROP FOREIGN KEY FK_526A633BD823E37A');
        $this->addSql('DROP TABLE blog_article');
        $this->addSql('DROP TABLE media_photo');
        $this->addSql('DROP TABLE reference');
        $this->addSql('DROP TABLE section_image');
        $this->addSql('ALTER TABLE event DROP type');
        $this->addSql('DROP INDEX UNIQ_2D737AEF989D9B62 ON section');
        $this->addSql(<<<'SQL'
            ALTER TABLE
              section
            DROP
              slug,
            DROP
              age_range,
            DROP
              description,
            DROP
              schedule,
            DROP
              instructor_name
        SQL);
    }
}
