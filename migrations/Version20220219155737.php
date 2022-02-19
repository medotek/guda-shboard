<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220219155737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hoyolab_post DROP FOREIGN KEY FK_3115E93FA925B3C1');
        $this->addSql('DROP INDEX IDX_3115E93FA925B3C1 ON hoyolab_post');
        $this->addSql('ALTER TABLE hoyolab_post DROP hoyo_user_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hoyolab_post ADD hoyo_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE hoyolab_post ADD CONSTRAINT FK_3115E93FA925B3C1 FOREIGN KEY (hoyo_user_id) REFERENCES hoyolab_post_user (id)');
        $this->addSql('CREATE INDEX IDX_3115E93FA925B3C1 ON hoyolab_post (hoyo_user_id)');
    }
}
