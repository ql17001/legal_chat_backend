<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231106200806 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE asesoria (id INT AUTO_INCREMENT NOT NULL, id_cliente_id INT NOT NULL, nombre VARCHAR(100) NOT NULL, estado VARCHAR(1) NOT NULL, INDEX IDX_F8126B477BF9CE86 (id_cliente_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chat (id INT AUTO_INCREMENT NOT NULL, id_asesoria_id INT NOT NULL, fecha_creacion DATETIME NOT NULL, UNIQUE INDEX UNIQ_659DF2AA7E36505E (id_asesoria_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mensaje (id INT AUTO_INCREMENT NOT NULL, id_chat_id INT NOT NULL, fecha_envio DATETIME NOT NULL, contenido VARCHAR(255) NOT NULL, INDEX IDX_9B631D01C407D855 (id_chat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, nombre VARCHAR(100) NOT NULL, apellido VARCHAR(100) NOT NULL, dui VARCHAR(8) NOT NULL, activo TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_2265B05DE7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE asesoria ADD CONSTRAINT FK_F8126B477BF9CE86 FOREIGN KEY (id_cliente_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE chat ADD CONSTRAINT FK_659DF2AA7E36505E FOREIGN KEY (id_asesoria_id) REFERENCES asesoria (id)');
        $this->addSql('ALTER TABLE mensaje ADD CONSTRAINT FK_9B631D01C407D855 FOREIGN KEY (id_chat_id) REFERENCES chat (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE asesoria DROP FOREIGN KEY FK_F8126B477BF9CE86');
        $this->addSql('ALTER TABLE chat DROP FOREIGN KEY FK_659DF2AA7E36505E');
        $this->addSql('ALTER TABLE mensaje DROP FOREIGN KEY FK_9B631D01C407D855');
        $this->addSql('DROP TABLE asesoria');
        $this->addSql('DROP TABLE chat');
        $this->addSql('DROP TABLE mensaje');
        $this->addSql('DROP TABLE usuario');
    }
}
