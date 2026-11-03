<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261103090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'ClothesPiece: plusieurs photos par pièce (clothes_piece_image), reprise de l\'image existante en première photo';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE clothes_piece_image (id INT AUTO_INCREMENT NOT NULL, image VARCHAR(255) DEFAULT NULL, position INT NOT NULL, updated_at DATETIME NOT NULL, piece_id INT NOT NULL, INDEX IDX_27C1762AC40FCFA8 (piece_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE clothes_piece_image ADD CONSTRAINT FK_27C1762AC40FCFA8 FOREIGN KEY (piece_id) REFERENCES clothes_piece (id) ON DELETE CASCADE');
        $this->addSql("INSERT INTO clothes_piece_image (piece_id, image, position, updated_at) SELECT id, image, 0, COALESCE(updated_at, NOW()) FROM clothes_piece WHERE image IS NOT NULL AND image <> ''");
        $this->addSql('ALTER TABLE clothes_piece DROP image');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE clothes_piece ADD image VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE clothes_piece p JOIN clothes_piece_image i ON i.id = (SELECT MIN(i2.id) FROM clothes_piece_image i2 WHERE i2.piece_id = p.id AND i2.position = (SELECT MIN(i3.position) FROM clothes_piece_image i3 WHERE i3.piece_id = p.id)) SET p.image = i.image');
        $this->addSql('DROP TABLE clothes_piece_image');
    }
}
