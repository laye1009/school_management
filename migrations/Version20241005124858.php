<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241005124858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE classe_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE matiere_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE student_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE classe (id INT NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE classe_matiere (classe_id INT NOT NULL, matiere_id INT NOT NULL, PRIMARY KEY(classe_id, matiere_id))');
        $this->addSql('CREATE INDEX IDX_EB8D372B8F5EA509 ON classe_matiere (classe_id)');
        $this->addSql('CREATE INDEX IDX_EB8D372BF46CD258 ON classe_matiere (matiere_id)');
        $this->addSql('CREATE TABLE matiere (id INT NOT NULL, libelle VARCHAR(255) NOT NULL, facultative BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE student (id INT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, classe VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE classe_matiere ADD CONSTRAINT FK_EB8D372B8F5EA509 FOREIGN KEY (classe_id) REFERENCES classe (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE classe_matiere ADD CONSTRAINT FK_EB8D372BF46CD258 FOREIGN KEY (matiere_id) REFERENCES matiere (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE classe_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE matiere_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE student_id_seq CASCADE');
        $this->addSql('ALTER TABLE classe_matiere DROP CONSTRAINT FK_EB8D372B8F5EA509');
        $this->addSql('ALTER TABLE classe_matiere DROP CONSTRAINT FK_EB8D372BF46CD258');
        $this->addSql('DROP TABLE classe');
        $this->addSql('DROP TABLE classe_matiere');
        $this->addSql('DROP TABLE matiere');
        $this->addSql('DROP TABLE student');
    }
}
