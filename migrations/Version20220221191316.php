<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220221191316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE hoyolab_post_discord_notification (id INT AUTO_INCREMENT NOT NULL, hoyolab_post_id INT DEFAULT NULL, process_stats_id INT DEFAULT NULL, message_id VARCHAR(15) DEFAULT NULL, process_date DATETIME DEFAULT NULL, channel_id VARCHAR(15) DEFAULT NULL, guild_id VARCHAR(15) DEFAULT NULL, UNIQUE INDEX UNIQ_9ED79E48AB17CBE2 (hoyolab_post_id), UNIQUE INDEX UNIQ_9ED79E48865B6A03 (process_stats_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hoyolab_post_discord_notification ADD CONSTRAINT FK_9ED79E48AB17CBE2 FOREIGN KEY (hoyolab_post_id) REFERENCES hoyolab_post (id)');
        $this->addSql('ALTER TABLE hoyolab_post_discord_notification ADD CONSTRAINT FK_9ED79E48865B6A03 FOREIGN KEY (process_stats_id) REFERENCES hoyolab_post_stats (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE hoyolab_post_discord_notification');
    }
}
