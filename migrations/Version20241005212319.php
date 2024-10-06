<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241005212319 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_cfbdfa14f46cd258');
        $this->addSql('ALTER TABLE note ALTER matiere_id SET NOT NULL');
        $this->addSql('CREATE INDEX IDX_CFBDFA14F46CD258 ON note (matiere_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX IDX_CFBDFA14F46CD258');
        $this->addSql('ALTER TABLE note ALTER matiere_id DROP NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_cfbdfa14f46cd258 ON note (matiere_id)');
    }
}
