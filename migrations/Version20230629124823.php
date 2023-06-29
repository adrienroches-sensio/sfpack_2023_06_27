<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230629124823 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[Movie] Add rated column.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movie ADD COLUMN rated VARCHAR(10) DEFAULT \'G\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__movie AS SELECT id, slug, title, released_at, plot, poster FROM movie');
        $this->addSql('DROP TABLE movie');
        $this->addSql('CREATE TABLE movie (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, slug VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, released_at DATE NOT NULL --(DC2Type:date_immutable)
        , plot CLOB NOT NULL, poster VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO movie (id, slug, title, released_at, plot, poster) SELECT id, slug, title, released_at, plot, poster FROM __temp__movie');
        $this->addSql('DROP TABLE __temp__movie');
        $this->addSql('CREATE UNIQUE INDEX idx_movie_unique_slug ON movie (slug)');
    }
}
