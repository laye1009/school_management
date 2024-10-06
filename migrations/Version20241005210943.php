<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241005210943 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_cfbdfa14cb944f1a');
        $this->addSql('ALTER TABLE note ALTER student_id SET NOT NULL');
        $this->addSql('CREATE INDEX IDX_CFBDFA14CB944F1A ON note (student_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX IDX_CFBDFA14CB944F1A');
        $this->addSql('ALTER TABLE note ALTER student_id DROP NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_cfbdfa14cb944f1a ON note (student_id)');
    }
}
