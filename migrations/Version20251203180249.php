<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251203180249 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add image_name column to livre table';
    }

    public function up(Schema $schema): void
    {
        // Ajout de la colonne image_name à la table livre
        // Vérification pour éviter les doublons
        $connection = $this->connection;
        $sm = $connection->createSchemaManager();
        $columns = $sm->listTableColumns('livre');
        $hasImageName = false;
        foreach ($columns as $column) {
            if ($column->getName() === 'image_name') {
                $hasImageName = true;
                break;
            }
        }

        if (!$hasImageName) {
            $this->addSql('ALTER TABLE livre ADD COLUMN image_name VARCHAR(255) DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__livre AS SELECT id, bibliotheque_id, auteur_id, categorie_id, editeur_id, titre, qte, priorite, isbn, datepub FROM livre');
        $this->addSql('DROP TABLE livre');
        $this->addSql('CREATE TABLE livre (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, bibliotheque_id INTEGER DEFAULT NULL, auteur_id INTEGER DEFAULT NULL, categorie_id INTEGER DEFAULT NULL, editeur_id INTEGER DEFAULT NULL, titre VARCHAR(255) NOT NULL, qte INTEGER NOT NULL, priorite VARCHAR(255) NOT NULL, isbn VARCHAR(255) DEFAULT NULL, datepub DATE NOT NULL, CONSTRAINT FK_AC634F994419DE7D FOREIGN KEY (bibliotheque_id) REFERENCES bibliotheque (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AC634F9960BB6FE6 FOREIGN KEY (auteur_id) REFERENCES auteur (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AC634F99BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AC634F993375BD21 FOREIGN KEY (editeur_id) REFERENCES editeur (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO livre (id, bibliotheque_id, auteur_id, categorie_id, editeur_id, titre, qte, priorite, isbn, datepub) SELECT id, bibliotheque_id, auteur_id, categorie_id, editeur_id, titre, qte, priorite, isbn, datepub FROM __temp__livre');
        $this->addSql('DROP TABLE __temp__livre');
        $this->addSql('CREATE INDEX IDX_AC634F994419DE7D ON livre (bibliotheque_id)');
        $this->addSql('CREATE INDEX IDX_AC634F9960BB6FE6 ON livre (auteur_id)');
        $this->addSql('CREATE INDEX IDX_AC634F99BCF5E72D ON livre (categorie_id)');
        $this->addSql('CREATE INDEX IDX_AC634F993375BD21 ON livre (editeur_id)');
    }
}
