<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251010150726 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__editeur AS SELECT id, nom, pays, acteur, telephone FROM editeur');
        $this->addSql('DROP TABLE editeur');
        $this->addSql('CREATE TABLE editeur (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, pays VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO editeur (id, nom, pays, adresse, telephone) SELECT id, nom, pays, acteur, telephone FROM __temp__editeur');
        $this->addSql('DROP TABLE __temp__editeur');
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
