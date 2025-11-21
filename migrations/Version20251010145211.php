<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251010145211 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Cette migration ne fait rien car la colonne isbn a déjà été ajoutée dans Version20251010144830
        // Migration laissée vide pour maintenir la cohérence de l'historique
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__livre AS SELECT id, titre, qte, priorite, datepub FROM livre');
        $this->addSql('DROP TABLE livre');
        $this->addSql('CREATE TABLE livre (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, qte INTEGER NOT NULL, priorite VARCHAR(255) NOT NULL, datepub DATE NOT NULL)');
        $this->addSql('INSERT INTO livre (id, titre, qte, priorite, datepub) SELECT id, titre, qte, priorite, datepub FROM __temp__livre');
        $this->addSql('DROP TABLE __temp__livre');
    }
}
