<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231106230018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE asesoria ADD id_asesor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE asesoria ADD CONSTRAINT FK_F8126B4718972399 FOREIGN KEY (id_asesor_id) REFERENCES usuario (id)');
        $this->addSql('CREATE INDEX IDX_F8126B4718972399 ON asesoria (id_asesor_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE asesoria DROP FOREIGN KEY FK_F8126B4718972399');
        $this->addSql('DROP INDEX IDX_F8126B4718972399 ON asesoria');
        $this->addSql('ALTER TABLE asesoria DROP id_asesor_id');
    }
}
