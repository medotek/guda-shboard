<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220213181721 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE discord_webhook CHANGE webhook_id webhook_id VARCHAR(100) NOT NULL, CHANGE channel_id channel_id VARCHAR(100) NOT NULL, CHANGE guild_id guild_id VARCHAR(100) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE discord_webhook CHANGE webhook_id webhook_id INT NOT NULL, CHANGE channel_id channel_id INT NOT NULL, CHANGE guild_id guild_id INT NOT NULL');
    }
}
