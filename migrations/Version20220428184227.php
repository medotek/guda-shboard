<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220428184227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hoyolab_stats DROP FOREIGN KEY FK_72432A8F21B6FB0A');
        $this->addSql('DROP TABLE hoyolab_stat_type');
        $this->addSql('DROP INDEX IDX_72432A8F21B6FB0A ON hoyolab_stats');
        $this->addSql('ALTER TABLE hoyolab_stats ADD view INT NOT NULL, ADD reply INT NOT NULL, ADD likes INT NOT NULL, ADD bookmark INT NOT NULL, ADD share INT NOT NULL, DROP stat_type_id, DROP number');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE hoyolab_stat_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, taxonomy VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE hoyolab_stats ADD stat_type_id INT NOT NULL, ADD number INT NOT NULL, DROP view, DROP reply, DROP likes, DROP bookmark, DROP share');
        $this->addSql('ALTER TABLE hoyolab_stats ADD CONSTRAINT FK_72432A8F21B6FB0A FOREIGN KEY (stat_type_id) REFERENCES hoyolab_stat_type (id)');
        $this->addSql('CREATE INDEX IDX_72432A8F21B6FB0A ON hoyolab_stats (stat_type_id)');
    }
}
