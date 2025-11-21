<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251010150906 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Vérifier si la colonne acteur existe avant de la renommer en adresse
        $connection = $this->connection;
        $sm = $connection->createSchemaManager();
        
        if ($sm->tablesExist(['editeur'])) {
            $columns = $sm->listTableColumns('editeur');
            $hasActeur = false;
            $hasAdresse = false;
            
            foreach ($columns as $column) {
                if ($column->getName() === 'acteur') {
                    $hasActeur = true;
                }
                if ($column->getName() === 'adresse') {
                    $hasAdresse = true;
                }
            }
            
            // Si la colonne acteur existe et adresse n'existe pas, on fait la migration
            if ($hasActeur && !$hasAdresse) {
                $this->addSql('CREATE TEMPORARY TABLE __temp__editeur AS SELECT id, nom, pays, acteur, telephone FROM editeur');
                $this->addSql('DROP TABLE editeur');
                $this->addSql('CREATE TABLE editeur (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, pays VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL)');
                $this->addSql('INSERT INTO editeur (id, nom, pays, adresse, telephone) SELECT id, nom, pays, acteur, telephone FROM __temp__editeur');
                $this->addSql('DROP TABLE __temp__editeur');
            } elseif (!$hasAdresse) {
                // Si ni acteur ni adresse n'existent, on ajoute juste la colonne adresse
                $this->addSql('ALTER TABLE editeur ADD COLUMN adresse VARCHAR(255) DEFAULT NULL');
            }
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__editeur AS SELECT id, nom, pays, adresse, telephone FROM editeur');
        $this->addSql('DROP TABLE editeur');
        $this->addSql('CREATE TABLE editeur (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, pays VARCHAR(255) NOT NULL, acteur VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO editeur (id, nom, pays, acteur, telephone) SELECT id, nom, pays, adresse, telephone FROM __temp__editeur');
        $this->addSql('DROP TABLE __temp__editeur');
    }
}
