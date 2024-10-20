<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241006185000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE professor_classe DROP CONSTRAINT fk_a560e3af7d2d84d5');
        $this->addSql('ALTER TABLE professor_classe DROP CONSTRAINT fk_a560e3af8f5ea509');
        $this->addSql('DROP TABLE professor_classe');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE TABLE professor_classe (professor_id INT NOT NULL, classe_id INT NOT NULL, PRIMARY KEY(professor_id, classe_id))');
        $this->addSql('CREATE INDEX idx_a560e3af8f5ea509 ON professor_classe (classe_id)');
        $this->addSql('CREATE INDEX idx_a560e3af7d2d84d5 ON professor_classe (professor_id)');
        $this->addSql('ALTER TABLE professor_classe ADD CONSTRAINT fk_a560e3af7d2d84d5 FOREIGN KEY (professor_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE professor_classe ADD CONSTRAINT fk_a560e3af8f5ea509 FOREIGN KEY (classe_id) REFERENCES classe (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
