<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220219144238 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hoyolab_post_user ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE hoyolab_post_user ADD CONSTRAINT FK_8400E19EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8400E19EA76ED395 ON hoyolab_post_user (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hoyolab_post_user DROP FOREIGN KEY FK_8400E19EA76ED395');
        $this->addSql('DROP INDEX IDX_8400E19EA76ED395 ON hoyolab_post_user');
        $this->addSql('ALTER TABLE hoyolab_post_user DROP user_id');
    }
}
