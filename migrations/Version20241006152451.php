<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241006152451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE student_id_seq CASCADE');
        $this->addSql('CREATE TABLE professor_classe (professor_id INT NOT NULL, classe_id INT NOT NULL, PRIMARY KEY(professor_id, classe_id))');
        $this->addSql('CREATE INDEX IDX_A560E3AF7D2D84D5 ON professor_classe (professor_id)');
        $this->addSql('CREATE INDEX IDX_A560E3AF8F5EA509 ON professor_classe (classe_id)');
        $this->addSql('ALTER TABLE professor_classe ADD CONSTRAINT FK_A560E3AF7D2D84D5 FOREIGN KEY (professor_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE professor_classe ADD CONSTRAINT FK_A560E3AF8F5EA509 FOREIGN KEY (classe_id) REFERENCES classe (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD discr VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD school VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ALTER classe_id DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE student_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE professor_classe DROP CONSTRAINT FK_A560E3AF7D2D84D5');
        $this->addSql('ALTER TABLE professor_classe DROP CONSTRAINT FK_A560E3AF8F5EA509');
        $this->addSql('DROP TABLE professor_classe');
        $this->addSql('ALTER TABLE "user" DROP discr');
        $this->addSql('ALTER TABLE "user" DROP school');
        $this->addSql('ALTER TABLE "user" ALTER classe_id SET NOT NULL');
    }
}
