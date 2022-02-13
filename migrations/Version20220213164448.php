<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220213164448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE discord_webhook (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, avatar_id VARCHAR(500) DEFAULT NULL, token VARCHAR(500) NOT NULL, webhook_id INT NOT NULL, channel_id INT NOT NULL, guild_id INT NOT NULL, INDEX IDX_CEE9330D7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE discord_webhook_user (discord_webhook_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_39F2244343246941 (discord_webhook_id), INDEX IDX_39F22443A76ED395 (user_id), PRIMARY KEY(discord_webhook_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE discord_webhook ADD CONSTRAINT FK_CEE9330D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE discord_webhook_user ADD CONSTRAINT FK_39F2244343246941 FOREIGN KEY (discord_webhook_id) REFERENCES discord_webhook (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE discord_webhook_user ADD CONSTRAINT FK_39F22443A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE discord_webhook_user DROP FOREIGN KEY FK_39F2244343246941');
        $this->addSql('DROP TABLE discord_webhook');
        $this->addSql('DROP TABLE discord_webhook_user');
    }
}
