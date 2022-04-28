<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220424144527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE hoyolab_stat_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, taxonomy VARCHAR(30) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hoyolab_stats (id INT AUTO_INCREMENT NOT NULL, stat_type_id INT NOT NULL, hoyolab_post_id INT DEFAULT NULL, number INT NOT NULL, date DATETIME NOT NULL, INDEX IDX_72432A8F21B6FB0A (stat_type_id), INDEX IDX_72432A8FAB17CBE2 (hoyolab_post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hoyolab_stats ADD CONSTRAINT FK_72432A8F21B6FB0A FOREIGN KEY (stat_type_id) REFERENCES hoyolab_stat_type (id)');
        $this->addSql('ALTER TABLE hoyolab_stats ADD CONSTRAINT FK_72432A8FAB17CBE2 FOREIGN KEY (hoyolab_post_id) REFERENCES hoyolab_post (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hoyolab_stats DROP FOREIGN KEY FK_72432A8F21B6FB0A');
        $this->addSql('DROP TABLE hoyolab_stat_type');
        $this->addSql('DROP TABLE hoyolab_stats');
    }
}
